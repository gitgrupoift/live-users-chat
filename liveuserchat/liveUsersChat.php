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
session_start();
global $bp;
global $wpdb;
$userName = $bp->loggedin_user->fullname;
$_SESSION['username'] = $userName;

function liveUsersChat_activation() {

    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    
    $db_table_name = $wpdb->prefix . 'live_users_chat_messages';
    $_SESSION['tableName'] = $db_table_name;

    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE " . $db_table_name . " (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `from_id` INT NOT NULL,
                        `from` VARCHAR(255) NOT NULL DEFAULT '',
                        `to_id` INT NOT NULL,
                        `to` VARCHAR(255) NOT NULL DEFAULT '',
                        `message` TEXT NOT NULL,
                        `sent` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `recd` INT UNSIGNED NOT NULL DEFAULT 0
		);";
        mysql_query($sql) or die("Error: Cannot create the table! " . mysql_errno() . ": " . mysql_error() . " \n");
    }
}

register_activation_hook(__FILE__, 'liveUsersChat_activation');

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

add_action('wp_head', 'add_header_links');
add_action('admin_head', 'add_header_links');

function show_chat_box() {
    //var_dump($_POST);
   // echo "tutaj -> " . $_SESSION['tableName'] . "<- tutaj table name";
      
    global $bp;
    $userName = $bp->loggedin_user->fullname;
    $_SESSION['username'] = $userName;
 //echo "tutaj -> " . $_SESSION['username'] . "<- tutaj user name";
    if ($_GET['action'] == "chatheartbeat") {
        chatHeartbeat();
    }
    if ($_GET['action'] == "sendchat") {
      
        sendChat();
    }
    if ($_GET['action'] == "closechat") {
        closeChat();
    }
    if ($_GET['action'] == "startchatsession") {
        startChatSession();
    }

    if (!isset($_SESSION['chatHistory'])) {
        $_SESSION['chatHistory'] = array();
    }

    if (!isset($_SESSION['openChatBoxes'])) {
        $_SESSION['openChatBoxes'] = array();
    }

    function chatHeartbeat() {
        $sql = "select * from " . $_SESSION['tableName'] . " where (" . $_SESSION['tableName'] . ".to = '" . mysql_real_escape_string($_SESSION['username']) . "' AND recd = 0) order by id ASC";
        $query = mysql_query($sql) or die("Error: Cannot select! " . mysql_errno() . ": " . mysql_error() . " \n");
        $items = '';

        $chatBoxes = array();

        while ($chat = mysql_fetch_array($query)) {

            if (!isset($_SESSION['openChatBoxes'][$chat['from']]) && isset($_SESSION['chatHistory'][$chat['from']])) {
                $items = $_SESSION['chatHistory'][$chat['from']];
            }

            $chat['message'] = sanitize($chat['message']);

            $items .= <<<EOD
					   {
			"s": "0",
			"f": "{$chat['from']}",
			"m": "{$chat['message']}"
	   },
EOD;

            if (!isset($_SESSION['chatHistory'][$chat['from']])) {
                $_SESSION['chatHistory'][$chat['from']] = '';
            }

            $_SESSION['chatHistory'][$chat['from']] .= <<<EOD
						   {
			"s": "0",
			"f": "{$chat['from']}",
			"m": "{$chat['message']}"
	   },
EOD;

            unset($_SESSION['tsChatBoxes'][$chat['from']]);
            $_SESSION['openChatBoxes'][$chat['from']] = $chat['sent'];
        }

        if (!empty($_SESSION['openChatBoxes'])) {
            foreach ($_SESSION['openChatBoxes'] as $chatbox => $time) {
                if (!isset($_SESSION['tsChatBoxes'][$chatbox])) {
                    $now = time() - strtotime($time);
                    $time = date('g:iA M dS', strtotime($time));

                    $message = "Sent at $time";
                    if ($now > 180) {
                        $items .= <<<EOD
{
"s": "2",
"f": "$chatbox",
"m": "{$message}"
},
EOD;

                        if (!isset($_SESSION['chatHistory'][$chatbox])) {
                            $_SESSION['chatHistory'][$chatbox] = '';
                        }

                        $_SESSION['chatHistory'][$chatbox] .= <<<EOD
		{
"s": "2",
"f": "$chatbox",
"m": "{$message}"
},
EOD;
                        $_SESSION['tsChatBoxes'][$chatbox] = 1;
                    }
                }
            }
        }

        $sql = "update " . $_SESSION['tableName'] . " set recd = 1 where " . $_SESSION['tableName'] . ".to = '" . mysql_real_escape_string($_SESSION['username']) . "' and recd = 0";
        $query = mysql_query($sql) or die("Error: Cannot update! " . mysql_errno() . ": " . mysql_error() . " \n");

        if ($items != '') {
            $items = substr($items, 0, -1);
        }
        header('Content-type: application/json');
        ?>
        {
        "items": [
        <?php echo $items; ?>
        ]
        }

        <?php
        exit(0);
    }

    function chatBoxSession($chatbox) {

        $items = '';

        if (isset($_SESSION['chatHistory'][$chatbox])) {
            $items = $_SESSION['chatHistory'][$chatbox];
        }

        return $items;
    }

    function startChatSession() {
        $items = '';
        if (!empty($_SESSION['openChatBoxes'])) {
            foreach ($_SESSION['openChatBoxes'] as $chatbox => $void) {
                $items .= chatBoxSession($chatbox);
            }
        }


        if ($items != '') {
            $items = substr($items, 0, -1);
        }

        header('Content-type: application/json');
        ?>
        {
        "username": "<?php echo $_SESSION['username']; ?>",
        "items": [
        <?php echo $items; ?>
        ]
        }

        <?php
        exit(0);
    }

    function sendChat() {
  
        $from = $_SESSION['username'];
        $to = $_POST['to'];
        $message = $_POST['message'];

        $_SESSION['openChatBoxes'][$_POST['to']] = date('Y-m-d H:i:s', time());

        $messagesan = sanitize($message);

        if (!isset($_SESSION['chatHistory'][$_POST['to']])) {
            $_SESSION['chatHistory'][$_POST['to']] = '';
        }

        $_SESSION['chatHistory'][$_POST['to']] .= <<<EOD
					   {
			"s": "1",
			"f": "{$to}",
			"m": "{$messagesan}"
	   },
EOD;


        unset($_SESSION['tsChatBoxes'][$_POST['to']]);

        $sql = "insert into " . $_SESSION['tableName'] . " (" . $_SESSION['tableName'] . ".from," . $_SESSION['tableName'] . ".to," . $_SESSION['tableName'] . ".message," . $_SESSION['tableName'] . ".sent) values ('" . mysql_real_escape_string($from) . "', '" . mysql_real_escape_string($to) . "','" . mysql_real_escape_string($message) . "',NOW())";
        $query = mysql_query($sql) or die("Error: Cannot insert! " . mysql_errno() . ": " . mysql_error() . " \n");
        echo "1";
        exit(0);
    }

    function closeChat() {

        unset($_SESSION['openChatBoxes'][$_POST['chatbox']]);

        echo "1";
        exit(0);
    }

    function sanitize($text) {
        $text = htmlspecialchars($text, ENT_QUOTES);
        $text = str_replace("\n\r", "\n", $text);
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\n", "<br>", $text);
        return $text;
    }

}

add_action('get_footer', 'show_chat_box');


