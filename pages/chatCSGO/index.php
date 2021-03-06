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
        $messages[] = array('id' => $next_id, 'time' => time(), 'name' => $_POST['name'], 'content' => $_POST['content']);
    }
    else if($messages[count($messages) - 1]['content'] != $_POST['content']){
        $messages[] = array('id' => $next_id, 'time' => time(), 'name' => $_POST['name'], 'content' => $_POST['content']);
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

    <!--Stylesheets-->
    <link rel="stylesheet" href="/assets/css/baseStyle.css">
    <link rel="stylesheet" href="/assets/css/chatBaseStyle.css">
    <link rel="stylesheet" href="/assets/css/perfect-scrollbar.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">

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
<section id="bodyContainer">
    <!--Chat Message Section start-->
    <div id="chatWrapper">
        <h3><strong>Counter-Strike: Global Offensive</strong></h3>

        <ul id="messages">
            <li>loading…</li>
        </ul>

        <form id="userForm" action="<?= htmlentities($_SERVER['PHP_SELF'], ENT_COMPAT, 'UTF-8'); ?>" method="post">
            <p id="messageBox">
                <input type="text" name="content" id="content" />
            </p>
            <p id="nameBox">
                <label>
                    Name:
                    <input type="text" name="name" id="name"  />
                </label>
                <button class="btn" type="submit">Send</button>
            </p>
        </form>
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
<script>
    var doStuff;

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    if(!doStuff) {
        var username = getCookie("name");

        if(username != "" && username != null){
            $('#name').load('#name', function () {
                $("#name[type='text']").val(username.toString());
            });
        }
        else {
            var number = Math.floor(Math.random() * 999999) + 100000;
            $('#name').load('#name', function () {
                $("#name[type='text']").val("User" + number);
            });
            setCookie("name", "User" + number, 365);
        }

        doStuff = true;
    }

    $("#name").on("input", function() {
        setCookie("name", $("#name").val(), 365)
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
<script type="text/javascript" src="/assets/js/jquery-1.4.2.min.js"></script>
<script src="/assets/js/perfect-scrollbar.js"></script>

<script type="text/javascript">
    // <![CDATA[
    $(document).ready(function(){
        // Remove the "loading…" list entry
        $('ul#messages > li').remove();

        $('form').submit(function(){
            var form = $(this);
            var name =  form.find("input[name='name']").val();
            var content =  form.find("input[name='content']").val();

            // Only send a new message if it's not empty (also it's ok for the server we don't need to send senseless messages)
            if (name == '' || content == '') {
                return false;
            }

            // Append a "pending" message (not yet confirmed from the server) as soon as the POST request is finished. The
            // text() method automatically escapes HTML so no one can harm the client.
            $.post(form.attr('action'), {'name': name, 'content': content}, function(data, status){
                $('<li class="pending" />').text(content).prepend($('<small />').text(name)).appendTo('ul#messages');
                $('ul#messages').scrollTop( $('ul#messages').get(0).scrollHeight );
                form.find("input[name='content']").val('').focus();
            });
            return false;
        });

        // Poll-function that looks for new messages
        var poll_for_new_messages = function(){
            $.ajax({url: 'messages.json', dataType: 'json', ifModified: true, timeout: 2000, success: function(messages, status){
                    // Skip all responses with unmodified data
                    if (!messages)
                        return;

                    // Remove the pending messages from the list (they are replaced by the ones from the server later)
                    $('ul#messages > li.pending').remove();

                    // Get the ID of the last inserted message or start with -1 (so the first message from the server with 0 will
                    // automatically be shown).
                    var last_message_id = $('ul#messages').data('last_message_id');
                    if (last_message_id == null)
                        last_message_id = -1;

                    // Add a list entry for every incomming message, but only if we not already inserted it (hence the check for
                    // the newer ID than the last inserted message).
                    for(var i = 0; i < messages.length; i++)
                    {
                        var msg = messages[i];
                        if (msg.id > last_message_id)
                        {
                            var date = new Date(msg.time * 1000);
                            $('<li/>').text(msg.content).
                            prepend( $('<small />').text(date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds() + ' ' + msg.name) ).
                            appendTo('ul#messages');
                            $('ul#messages').data('last_message_id', msg.id);
                        }
                    }

                    // Remove all but the last 50 messages in the list to prevent browser slowdown with extremely large lists
                    // and finally scroll down to the newes message.
                    $('ul#messages > li').slice(0, -50).remove();
                    $('ul#messages').scrollTop( $('ul#messages').get(0).scrollHeight );
                }});
        };

        // Kick of the poll function and repeat it every 1/10 seconds
        poll_for_new_messages();
        setInterval(poll_for_new_messages, 100);
    });
    // ]]>
</script>
</body>
</html>