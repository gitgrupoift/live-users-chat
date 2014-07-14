<?php


/** Loads the WordPress Environment and Template */

$scriptPath = dirname(__FILE__);
 $path = realpath($scriptPath . '/./');
 $filepath = split("wp-content",$path);
// print_r($filepath);
define('WP_USE_THEMES', false);
require(''.$filepath[0].'/wp-blog-header.php'); 




//session_start();
//global $bp;
//global $wpdb;


$userName = $bp->loggedin_user->fullname;
$_SESSION['username'] = $userName;


//require('functions.php');
//require('installer.php');
//register_activation_hook(__FILE__, 'liveUsersChat_activation');

//add_action('wp_head', 'add_header_links');
//add_action('admin_head', 'add_header_links');
//add_action('get_footer', 'show_chat_box');

//<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

show_chat_box();