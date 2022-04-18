<?php

class TimerLock
{
    public static function handleLockUserWhenEnterPass($isValid, $password, $postId)
    {
        $ip = getClientIp();
        if (!$isValid) {
            if (!empty($ip)) {
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
                        ],
                        [
                            'id' => $ipInfoLock['id']
                        ]
                    );
                }
            }
        } else {
            if (!empty($ip)) {
                TimerDb::delete(
                    TIMER_TABLE_LOCK,
                    [
                        'ip' => $ip
                    ]
                );
                // show clock
//                apply_filters( 'the_content', function ($content) {
//                    dd("zzzzz");
//                }, 999);
            }

        }
    }

    public static function handleUnLockUserWhenShowPage($content)
    {
        global $post, $passwords;
        $ip = getClientIp();
        if (!empty($ip)) {
            $ipInfoLock = TimerDb::get(TIMER_TABLE_LOCK, "ip = '$ip' AND page_id = $post->ID", "ARRAY_A");
            if (!empty($ipInfoLock)) {
                if (round(abs(strtotime(current_time('mysql')) - strtotime($ipInfoLock['updated_at'])) / 60,2) > TIMER_TIME_ROMOVE_LOCK_IP) {
                    TimerDb::delete(
                        TIMER_TABLE_LOCK,
                        [
                            'ip' => $ip
                        ]
                    );
                    return $content;
                }
                if ($ipInfoLock['attempt'] >= TIMER_ALLOWED_NUMBER_OF_ATTEMPTS) {
                    return "<p style='color: red'>you have been blocked for entering the wrong password many times</p>";
                }
            }

            if (!empty($passwords)) {
                return '<div class="ymese-timer">
                            <p id="countdown-timer"></p>
                            <p id="countdown-timer-pause">Pause</p>
                            <p id="countdown-timer-resume">Resume</p>
                        </div>' . $content;
            }
        }
        return $content;
    }
}