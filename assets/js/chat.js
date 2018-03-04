////////////////////////////// Ajax Console error Fix
$.ajaxPrefilter(function( options, original_Options, jqXHR ) {
    options.async = true;
});

////////////////////////////// Cookie FUNctions
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
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

////////////////////////////// on Change cookies
$("#name1").on("input", function() {
    console.log("did stuff");
    setCookie("name", $("#name1").val(), 365);
});

$("#name").on("input", function() {
    setCookie("img", $("#name").val(), 365);
});

////////////////////////////// First run
var doStuff;
var sessionId = Math.floor(Math.random() * 999999) + 100000;
if(!doStuff) {
    var username = getCookie("name");
    var img = getCookie("img");

    if(username !== "" && username != null){
        $('#name1').load('#name1', function () {
            $("#name1[type='text']").val(username.toString());
        });
    }
    else {
        var number = Math.floor(Math.random() * 999999) + 100000;
        $("#name1[type='text']").val("User" + number);
        setCookie("name", "User" + number, 365);
    }

    if(img !== "" && img != null){
        $("#name[type='text']").val(img.toString());
    }

    //Loads other chats html
    $.get('http://138.197.5.88/pages/baseChat/otherChats.html')
        .done(function(data) {
            $('#otherChatHolder').html(data);
        });

    $.get('http://teamsearch.tk/pages/baseChat/otherChats.html')
        .done(function(data) {
            $('#otherChatHolder').html(data);
        });

    // const container = document.querySelector('#messages');
    // var ps = new PerfectScrollbar(container, {
    //     suppressScrollX: true
    // });
    // ps.update();

    var images = $("#messages img");
    var loadedImgNum = 0;
    images.on('load', function(){
        loadedImgNum += 1;
        if (loadedImgNum === images.length) {
            // ps.update();
            $('ul#messages').scrollTop( $('ul#messages').get(0).scrollHeight );
        }
    });
    doStuff = true;
}

$(document).ready(function(){
    var msgs = 'testing';
    $(msgs +'> li').remove();

    //Submit Message
    $('form').submit(function(){
        var form = $(this);
        var name =  form.find("input[name='name1']").val();
        var imgUrl = form.find("input[name='name']").val();
        var content = form.find("input[name='content']").val();

        if (imgUrl === '' || imgUrl === '') {
            imgUrl = "https://vignette.wikia.nocookie.net/leagueoflegends/images/9/97/Lizard_profileicon.png/revision/latest?cb=20170517190640";
        }

        // Only send a new message if it's not empty (also it's ok for the server we don't need to send senseless messages)
        if (name === '' || content === '') {
            return false;
        }

        $.post(form.attr('action'), {'name': name, 'content': content, 'imgUrl': imgUrl, "sessionId": sessionId}, function(data, status){
            $('<li class="pending" />').text(content).prepend($('<small />').text(name)).appendTo('ul#messages');
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

                $('ul#messages').empty();
                var recentChatters = [25][3];

                // Get the ID of the last inserted message or start with -1 (so the first message from the server with 0 will
                // automatically be shown).
                var last_message_id = $('ul#messages').data('last_message_id');
                if (last_message_id == null)
                    last_message_id = -1;

                // Add a list entry for every incoming message, but only if we not already inserted it (hence the check for
                // the newer ID than the last inserted message).
                for(var i = 0; i < messages.length; i++)
                {
                    var msg = messages[i];
                    if (msg.id > last_message_id)
                    {
                        var date = new Date(msg.time * 1000);
                        $('<li class=\"row\"/>').
                        prepend($('<span class=\"col-10\"><small>' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds() + ' ' + msg.name +'</small><p>' + msg.content + '</p></span>')).
                        prepend($('<span class=\"col-2\"><img class="avatarImage" src=\"'+msg.imgUrl+'\"></span>')).
                        appendTo('ul#messages');
                        // ps.update();
                        $('ul#messages').scrollTop( $('ul#messages').get(0).scrollHeight );
                    }
                }
            }});
    };

    // Kick of the poll function and repeat it every 1/10 second
    $( document ).ready(function() {
        setInterval(poll_for_new_messages, 100);

    });
});