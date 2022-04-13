<?php

/*
Plugin Name: Timer
Plugin URI: http://wordpress-dev.me
Description: Hmmm
Version: 1.0
Author: tinhnd
Author URI: http://wordpress-dev.me
*/

function get_client_ip() {
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

//add_action( 'ppwp_pro_before_set_post_cookie', function($postid, $pw) {
//    dd("zooo222", $postid, $pw);
//},10, 2 );


add_action( 'ppwp_pro_check_valid_password', function($checkPassword, $passwordInput) {
    if (!empty(get_client_ip())) {

    }
     dd(get_client_ip());
},500, 2 );