<?php

class TimerLock
{
    public static function handleWhenUserEnterPass($isValid, $password, $postId)
    {
        $ip = getClientIp();
        if (!empty($ip)) {
            if (!$isValid) {
                $ipInfoLock = TimerDb::get(TIMER_TABLE_LOCK, "ip = '$ip' AND page_id = $postId", "ARRAY_A");
                if (empty($ipInfoLock)) {
                    TimerDb::insert(TIMER_TABLE_LOCK, [
                        'ip' => getClientIp(),
                        'pw_code' => $password,
                        'page_id' => $postId,
                        'attempt' => 1,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ]);
                } else {
                    TimerDb::update(
                        TIMER_TABLE_LOCK,
                        [
                            'attempt' => $ipInfoLock['attempt'] + 1,
                            'pw_code' => $password,
                            'updated_at' => current_time('mysql'),
                            'blocked' => $ipInfoLock['attempt'] + 1 >= TIMER_ALLOWED_NUMBER_OF_ATTEMPTS ? 1 : 0
                        ],
                        ['id' => $ipInfoLock['id']]
                    );
                }
            } else {
                TimerDb::delete(TIMER_TABLE_LOCK, ['ip' => $ip]);
            }
        }
    }

    public static function handleWhenShowPage($content)
    {
        global $post, $passwords;
        $ip = getClientIp();
        if (!empty($ip) && !empty(TIMER_ALLOWED_NUMBER_OF_ATTEMPTS)) {
            $ipInfoLock = TimerDb::get(TIMER_TABLE_LOCK, "ip = '$ip' AND page_id = $post->ID", "ARRAY_A");
            if (!empty($ipInfoLock)) {
                $lastEnterPass = round(abs(strtotime(current_time('mysql')) - strtotime($ipInfoLock['updated_at'])) / 60, 2);
                if ($lastEnterPass > TIMER_TIME_ROMOVE_LOCK_IP) {
                    TimerDb::delete(TIMER_TABLE_LOCK, ['ip' => $ip]);
                    return $content;
                }
                if ($ipInfoLock['attempt'] >= TIMER_ALLOWED_NUMBER_OF_ATTEMPTS) {
                    return "<p style='color: red'>You have been blocked for entering the wrong password many times</p>
                            <p>Please try again after " . ceil(TIMER_TIME_ROMOVE_LOCK_IP - $lastEnterPass) . " minutes</p>";
                }
            }
        }

        if (!empty($passwords)
            && ((TIMER_CHECK_TYPE_EXPIRE_PASSWORD == TIMER_EXPIRE_PASSWORD_BY_DATE && time() < $passwords->expired_date)
                || (TIMER_CHECK_TYPE_EXPIRE_PASSWORD == TIMER_EXPIRE_PASSWORD_BY_COOKIE))
        ) {
            $settingCookieExpired = explode(' ', TIMER_WPP_PASSWORD_COOKIE_EXPIRED);
            $timeExpired = TIMER_CHECK_TYPE_EXPIRE_PASSWORD == TIMER_EXPIRE_PASSWORD_BY_DATE
                ? (int)round(abs($passwords->expired_date - time()) / 60)
                : getMinusFromSettingCookie($settingCookieExpired[0], $settingCookieExpired[1]);

            return '<div class="ymese-countdown-timer" data-time_expired="' . $timeExpired . '">
                            <div id="countdown-timer"></div>
                        </div>'
                . $content;
        }

        return $content;
    }
}