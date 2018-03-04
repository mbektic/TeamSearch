<?php
// Name of the message buffer file. You have to create it manually with read and write permissions for the webserver.
$messages_buffer_file = 'messages.json';
// Number of most recent messages kept in the buffer
$messages_buffer_size = 25;

if ( isset($_POST['content']) and isset($_POST['name']) )
{
    // Open, lock and read the message buffer file
    $buffer = fopen($messages_buffer_file, 'r+b');
    flock($buffer, LOCK_EX);
    $buffer_data = stream_get_contents($buffer);

    // Append new message to the buffer data or start with a message id of 0 if the buffer is empty
    $messages = $buffer_data ? json_decode($buffer_data, true) : array();
    $next_id = (count($messages) > 0) ? $messages[count($messages) - 1]['id'] + 1 : 0;

    if($messages[count($messages) - 1]['name'] != $_POST['name']){
        $messages[] = array('id' => $next_id, 'time' => time(), 'name' => $_POST['name'], 'content' => $_POST['content'], 'imgUrl' => $_POST['imgUrl'], 'sessionId' => $_POST['sessionId']);
    }
    else if($messages[count($messages) - 1]['content'] != $_POST['content']){
        $messages[] = array('id' => $next_id, 'time' => time(), 'name' => $_POST['name'], 'content' => $_POST['content'], 'imgUrl' => $_POST['imgUrl'], 'sessionId' => $_POST['sessionId']);
    }

    // Remove old messages if necessary to keep the buffer size
    if (count($messages) > $messages_buffer_size)
        $messages = array_slice($messages, count($messages) - $messages_buffer_size);

    // Rewrite and unlock the message file
    ftruncate($buffer, 0);
    rewind($buffer);
    fwrite($buffer, json_encode($messages));
    flock($buffer, LOCK_UN);
    fclose($buffer);

    // Optional: Append message to log file (file appends are atomic)
    //file_put_contents('chatlog.txt', strftime('%F %T') . "\t" . strtr($_POST['name'], "\t", ' ') . "\t" . strtr($_POST['content'], "\t", ' ') . "\n", FILE_APPEND);

    exit();
}
?>
<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">

        <!--Stylesheets-->
        <link rel="stylesheet" href="/assets/css/baseStyle.css">
        <link rel="stylesheet" href="/testing/assets/css/chatBaseStyle.css">
<!--        <link rel="stylesheet" href="/assets/css/chatBaseStyle.css">-->
        <link rel="stylesheet" href="/assets/css/perfect-scrollbar.css">

        <title>Teamsearch.tk: Home</title>
    </head>

    <body>
        <!--Navbar start-->
        <section>
            <nav class="navbar navbar-expand-md navbar-dark">
                <a class="navbar-brand abs" href="#">TeamSearch</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-collapse collapse" id="collapsingNavbar">
                    <ul class="navbar-nav">
                        <li class="nav-item active">
                            <a class="nav-link" href="/index.html">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/about/">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index.html#bodyBottom">Chat List</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/tos/">Terms of Service</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </section><!--Navbar end-->

        <!--Body container start-->
        <section id="bodyContainer" class="container-fluid">
            <!--Other Chats Section Start-->
            <div class="row">
                <div id="otherChatsWrapper" class="col-3">
                    <div class="titleWrapper">
                        <h4>All Chats</h4>
                    </div>
                    <div id="otherChatHolder">

                    </div>
                </div><!--Other Chats Section End-->

                <!--Chat Message Section start-->
                <div id="chatWrapper" class="col-6">
                    <h1><strong>Chat Name</strong></h1>

                    <div id="chatHolder">
                        <ul id="messages" >
                            <li>loading…</li>
                        </ul>
                    </div>

                    <form id="userForm" >
                            <p id="messageBox">
                                <input type="text" name="content" id="content" />
                            </p>

                        <div id="formRow">

                            <p id="nameBox">
                                <label>
                                    Avatar:
                                    <input type="text" name="name" id="name" />
                                </label>
                            </p>

                            <p id="nameBox">
                                <label>
                                    Name:
                                    <input type="text" name="name1" id="name1" />
                                </label>
                            </p>

                            <button class="btn" type="submit">Send</button>
                        </div>
                    </form>
                </div>

                <!--Online Users Section Start-->
                <div id="onlineUsers" class="col-3">
                    <div class="titleWrapper">
                        <h4>Recent Chatters</h4>
                    </div>
                </div><!--Online Users Section End-->
            </div>
        </section><!--bodyContainer end-->

        <!--Footer start-->
        <section>
            <footer class="footer bkg-dark fixed-bottom">
                <div class="container text-center">
                    <div class="social">
                        <a target="_blank" href="https://discord.gg/VgAQTWd"><img class="footerIcon" src="/assets/images/base/footerDiscord.svg" alt="Link to Teamsearch.tk Discord"/></a>
                        <a target="_blank" href="https://github.com/mbektic/TeamSearch"><img class="footerIcon" src="/assets/images/base/footerGithub.svg" alt="Link to Teamsearch Github"/></a>
                    </div>
                </div>
            </footer>
        </section><!--Footer end-->

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
        <script src="/assets/js/perfect-scrollbar.js"></script>
        <script src="/testing/assets/js/chat.js"></script>
    </body>
</html>