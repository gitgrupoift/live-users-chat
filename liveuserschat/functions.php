<?php
/*
 * Functions.php
 * By Tomasz Swiadek - Polcode
 * Chat from
 */


class liveUsersChat extends WP_Widget {


    function liveUsersChat() {
        parent::WP_widget(false, $name = __('Live Users Chat - Tomasz Swiadek', 'liveUsersChat'));
    }

    function form($instance) {

        if ($instance) {

               $title = 'Feel free to change the title :) ';

        } else {
            $title = 'Feel free to change the title :) ';

        }
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'liveUsersChat'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>


        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        // Fields
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function show_chat_box() {

        global $bp;


        global $wpdb;
        $db_table_name = $wpdb->prefix . 'live_users_chat_messages';
        $_SESSION['tableName'] = $db_table_name;


        global $bp;

        $userName = bp_core_get_username($bp->loggedin_user->id);
        $_SESSION['username'] = $userName;
        $id_of_Sender = $bp->loggedin_user->id;
        
        function chatHeartbeat() {

            $sql = "select * from " . $_SESSION['tableName'] . " where (" . $_SESSION['tableName'] . ".to = '" . mysql_real_escape_string($_SESSION['username']) . "' AND recd = 0) AND " . $_SESSION['tableName'] . ".whosPriv='" . $_SESSION['username'] . "' order by id ASC";
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

            $sql = "insert into " . $_SESSION['tableName'] . " (" . $_SESSION['tableName'] . ".from, " . $_SESSION['tableName'] . ".whosPriv," . $_SESSION['tableName'] . ".to, " . $_SESSION['tableName'] . ".message," . $_SESSION['tableName'] . ".sent) values ('" . mysql_real_escape_string($from) . "', '" . mysql_real_escape_string($from) . "','" . mysql_real_escape_string($to) . "','" . mysql_real_escape_string($message) . "',NOW())";

            $query = mysql_query($sql) or die("Error: Cannot insert! " . mysql_errno() . ": " . mysql_error() . " \n");

            $sql2 = "insert into " . $_SESSION['tableName'] . " (" . $_SESSION['tableName'] . ".from, " . $_SESSION['tableName'] . ".whosPriv," . $_SESSION['tableName'] . ".to, " . $_SESSION['tableName'] . ".message," . $_SESSION['tableName'] . ".sent) values ('" . mysql_real_escape_string($from) . "', '" . mysql_real_escape_string($to) . "','" . mysql_real_escape_string($to) . "','" . mysql_real_escape_string($message) . "',NOW())";

            $query2 = mysql_query($sql2) or die("Error: Cannot insert! " . mysql_errno() . ": " . mysql_error() . " \n");

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
    }

    function widget($args, $instance) {
        extract($args);

        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        echo $before_title
        . $title
        . $after_title;

        $members_args = array(
            'user_id' => bp_loggedin_user_id(),
            'type' => 'online',
            'per_page' => $instance['max_members'],
            'max' => $instance['max_members'],
            'populate_extras' => true,
            'search_terms' => false,
        );
        ?>
        <?php
        if (!is_user_logged_in()) {
            echo"Register to see who is online to chat with !";
        } else {
            ?>
            <?php if (bp_has_members($members_args)) : ?>
                <div class="avatar-block">
                    <?php while (bp_members()) : bp_the_member(); ?>
                        <div class="item-avatar">
                            <?php $helpMembId = bp_get_member_user_id();  ?>
                            <a href="javascript:void(0)" onclick="javascript:chatWith('<?php echo bp_core_get_username($helpMembId) ?>')" title="<?php bp_member_name() ?>"><?php bp_member_avatar() ?></a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>

                <div class="widget-error">
                    <?php _e('There are no users currently online', 'buddypress') ?>
                </div>

            <?php
            endif;
        }
        ?> 

        <?php echo $after_widget; ?>
        <?php
    }

}

add_action('widgets_init', create_function('', 'return register_widget("liveUsersChat");'));
