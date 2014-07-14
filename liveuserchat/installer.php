<?php

/* 
 * Installer file.
 */

function liveUsersChat_activation() {

    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
global $bp;
global $wpdb;
    $db_table_name = $wpdb->prefix . 'live_users_chat_messages';
    $_SESSION['tableName'] = $db_table_name;
  
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE " . $db_table_name . " (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `whosPriv` INT NOT NULL,
                        `from` VARCHAR(255) NOT NULL DEFAULT '',
                        `to` VARCHAR(255) NOT NULL DEFAULT '',
                        `message` TEXT NOT NULL,
                        `sent` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `recd` INT UNSIGNED NOT NULL DEFAULT 0
		);";
        mysql_query($sql) or die("Error: Cannot create the table! " . mysql_errno() . ": " . mysql_error() . " \n");
    }
}

function add_header_links() {
    echo '<script type="text/javascript" src="' . site_url() . '/wp-content/plugins/liveuserschat/js/jquery.js"></script>
         <script type="text/javascript" src="' . site_url() . '/wp-content/plugins/liveuserschat/js/chat.js"></script>
         <link type="text/css" rel="stylesheet" media="all" href="' . site_url() . '/wp-content/plugins/liveuserschat/css/chat.css" />
         <link type="text/css" rel="stylesheet" media="all" href="' . site_url() . '/wp-content/plugins/liveuserschat/css/screen.css" />
         <!--[if lte IE 7]>
         <link type="text/css" rel="stylesheet" media="all" href="' . site_url() . '/wp-content/plugins/liveuserschat/css/screen_ie.css" />
         <![endif]-->';
    echo '<link rel="stylesheet" href="' . site_url() . '/wp-content/plugins/liveuserschat/css/style.css" type="text/css">';
}