<?php

/** Loads the WordPress Environment and Template */
$scriptPath = dirname(__FILE__);
$path = realpath($scriptPath . '/./');
$filepath = split("wp-content", $path);

define('WP_USE_THEMES', false);
require('' . $filepath[0] . '/wp-blog-header.php');


$userName = $bp->loggedin_user->fullname;
$_SESSION['username'] = $userName;


liveUsersChat::show_chat_box();

