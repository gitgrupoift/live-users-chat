<?php

/*
  Plugin Name: BuddyPress live users chat - chat with friends!
  Plugin URI: -
  Description: Simple in use chat using MySql, AJAX, WP, PHP for BuddyPress. It allows you to chat with friends on your buddypress site. Thanks Anant Garg for the js file. Non-commercial License!
  Version: 0.01
  Author: Tomasz Świadek - Polcode
  Author URI: -
  License: Non-commercial
 */


session_start();
global $bp;
global $wpdb;


$userName = $bp->loggedin_user->fullname;
$_SESSION['username'] = $userName;


require('functions.php');
require('installer.php');
register_activation_hook(__FILE__, 'liveUsersChat_activation');

add_action('wp_head', 'add_header_links');
add_action('admin_head', 'add_header_links');


