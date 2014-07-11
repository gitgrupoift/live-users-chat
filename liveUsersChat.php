<?php
/*
  Plugin Name: BuddyPress live users chat - chat with friends!
  Plugin URI: -
  Description: Simple in use chat using MySql, AJAX, WP, PHP for BuddyPress. It allows you to chat with friends on your buddypress site. Non-commercial License!
  Version: 0.01
  Author: Tomasz Świadek - Polcode
  Author URI: -
  License: Non-commercial
 */

function liveUsersChat_activation() {
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    global $wpdb;
    $db_table_name = $wpdb->prefix . 'live_users_chat_messages';
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE " . $db_table_name . " (
			`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                        from_id INT(11) NOT NULL,
                        `from` VARCHAR(255) NOT NULL DEFAULT '',
                        to_id INT(11) NOT NULL,
                        `to` VARCHAR(255) NOT NULL DEFAULT '',
                        `message` TEXT NOT NULL,
                        `sent` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `recd` INTEGER UNSIGNED NOT NULL DEFAULT 0
		);";
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'live_users_chat_activation');

function show_chat_box() {
    global $bp;
    session_start();
    $userName = $bp->loggedin_user->fullname;
    $_SESSION['username'] = $userName;
    echo '<script type="text/javascript" src="js/jquery.js"></script>
         <script type="text/javascript" src="js/chat.js"></script>
         <link type="text/css" rel="stylesheet" media="all" href="css/chat.css" />
         <link type="text/css" rel="stylesheet" media="all" href="css/screen.css" />
         <!--[if lte IE 7]>
         <link type="text/css" rel="stylesheet" media="all" href="css/screen_ie.css" />
         <![endif]-->';
    echo '<link rel="stylesheet" href="css/style.css" type="text/css">';
    echo '<form class="submitForm" method="post" action="" target="chatRes">';
    echo '<div name="subMsg">' . $userName . ' <input type="text" name="message" />';
    echo '<input type="submit" name="submit" value="send" />';
    echo '</div>';
    echo '</form>';
    echo '<iframe name="chatRes"></iframe>';
 
}
