$(document).ready(function() {
    // Scroll chat box to the bottom
    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);

    // Handle message sending
    $('#chat-form').submit(function(e) {
        e.preventDefault();
        const message = $('#message').val();
        const receiver_id = $('input[name="receiver_id"]').val();

        $.post('send_message.php', {message, receiver_id}, function(response) {
            $('#message').val('');  // Clear input
            $('#chat-box').append(response);  // Append new message
            $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);  // Scroll to bottom
        });
    });
});
