<?php

function getClientIp() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];

    return $ipaddress;
}

function getMinusFromSettingCookie($time, $unitsTimes) {
    $minus = 0;
    switch ($unitsTimes) {
        case 'days':
            $minus = $time * 24 * 60;
        break;
        case 'hours':
            $minus = $time * 60;
            break;
        case 'minutes':
            $minus = $time;
            break;
        case 'seconds':
            $minus = ceil($time / 60);
            break;
    }
    return $minus;
}

function getSecondsFromSettingCookie($time, $unitsTimes) {
    switch ($unitsTimes) {
        case 'days':
            return $time * 24 * 60 * 60;
        case 'hours':
            return $time * 60 * 60;
        case 'minutes':
            return $time * 60;
        case 'seconds':
            return $time;
    }
}