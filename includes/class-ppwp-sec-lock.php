<?php

class PPWP_SEC_LOCK
{
    public static function handleWhenUserEnterPass($isValid, $password, $postId)
    {
        $ip = getClientIp();
        $ppwp_sec_setting = get_option('ppwp-sec-setting');
        if (!empty($ip)) {
            if (!$isValid) {
                $ipInfoLock = PPWP_SEC_DB::get(PPWP_SEC_TABLE_LOCK, "ip = '$ip' AND page_id = $postId", "ARRAY_A");
                if (empty($ipInfoLock)) {
                    PPWP_SEC_DB::insert(PPWP_SEC_TABLE_LOCK, [
                        'ip' => getClientIp(),
                        'pw_code' => $password,
                        'page_id' => $postId,
                        'attempt' => 1,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ]);
                } else {
                    PPWP_SEC_DB::update(
                        PPWP_SEC_TABLE_LOCK,
                        [
                            'attempt' => $ipInfoLock['attempt'] + 1,
                            'pw_code' => $password,
                            'updated_at' => current_time('mysql'),
                            'blocked' => $ipInfoLock['attempt'] + 1 >= $ppwp_sec_setting['allowed-number-attempts'] ? 1 : 0
                        ],
                        ['id' => $ipInfoLock['id']]
                    );
                }
            } else {
                PPWP_SEC_DB::delete(PPWP_SEC_TABLE_LOCK, ['ip' => $ip]);
            }
        }
    }

    public static function handleWhenShowPage($content)
    {
        global $post, $ppwp_passwords;
        $ip = getClientIp();
        $ppwp_sec_setting = get_option('ppwp-sec-setting');
        if (!empty($ip) && !empty($ppwp_sec_setting['allowed-number-attempts'])) {
            $ipInfoLock = PPWP_SEC_DB::get(PPWP_SEC_TABLE_LOCK, "ip = '$ip' AND page_id = $post->ID", "ARRAY_A");
            if (!empty($ipInfoLock)) {
                $lastEnterPass = round(abs(strtotime(current_time('mysql')) - strtotime($ipInfoLock['updated_at'])) / 60, 2);
                if ($lastEnterPass > $ppwp_sec_setting['time-remove-lock-ip']) {
                    PPWP_SEC_DB::delete(PPWP_SEC_TABLE_LOCK, ['ip' => $ip]);
                    return $content;
                }
                if ($ipInfoLock['blocked'] || $ipInfoLock['attempt'] >= $ppwp_sec_setting['allowed-number-attempts']) {
                    if (!empty($ppwp_sec_setting['custom-message-block'])) {
                        return '<p>' . esc_html(str_replace('{time}', $ppwp_sec_setting['time-remove-lock-ip'], $ppwp_sec_setting['custom-message-block'])) . '</p>';
                    }
                    return "<p style='color: red'>You have been blocked for entering the wrong password many times</p>
                            <p>Please try again after " . ceil($ppwp_sec_setting['time-remove-lock-ip'] - $lastEnterPass) . " minutes</p>";
                }
            }
        }

        if (!empty($ppwp_passwords)
            && (($ppwp_sec_setting['check-type-expire-password'] == PPWP_SEC_EXPIRE_PASSWORD_BY_DATE && time() < $ppwp_passwords->expired_date)
                || ($ppwp_sec_setting['check-type-expire-password'] == PPWP_SEC_EXPIRE_PASSWORD_BY_COOKIE))
        ) {
            $settingCookieExpired = explode(' ', PPWP_SEC_WPP_PASSWORD_COOKIE_EXPIRED);
            $timeExpired = $ppwp_sec_setting['check-type-expire-password'] == PPWP_SEC_EXPIRE_PASSWORD_BY_DATE
                ? (int)round(abs($ppwp_passwords->expired_date - time()))
                : getSecondsFromSettingCookie($settingCookieExpired[0], $settingCookieExpired[1]);
            return '<div class="ppwp-sec-countdown" data-time_expired="' . $timeExpired . '">
                            <div id="countdown-timer"></div>
                        </div>'
                . $content;
        }

        return $content;
    }
}