<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reverb Chat Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Pusher (required for Echo compatibility with Reverb) -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <!-- Laravel Echo -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            display: flex;
            justify-content: center;
            padding-top: 40px;
        }

        .chat-box {
            width: 420px;
            background: #4ceed8;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1);
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 15px;
            font-weight: bold;
            border-bottom: 1px solid #eee;
        }

        .messages {
            padding: 15px;
            height: 350px;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 10px;
        }

        .message.me {
            text-align: right;
        }

        .message span {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 15px;
            background: #a2f85c;
        }

        .message.me span {
            background: #4f46e5;
            color: white;
        }

        .chat-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #eee;
        }

        .chat-input input {
            flex: 1;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #9eff92;
        }

        .chat-input button {
            margin-left: 10px;
            padding: 10px 15px;
            border: none;
            background: #4f46e5;
            color: rgb(133, 243, 139);
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="chat-box shadow rounded ">
        <div class="chat-header">
            Reverb Chat (Blade Test)
        </div>

        <div class="messages" id="messages"></div>

        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Type a message…">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        /**
         * =============================
         * BASIC SETUP
         * =============================
         */

        const userId = {{ auth()->id() ?? 1 }};
        const receiverId = 2; // 👈 change this user ID for testing

        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] =
            document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        /**
         * =============================
         * ECHO + REVERB CONFIG
         * =============================
         */
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: 'reverb',
            wsHost: window.location.hostname,
            wsPort: 8080,
            wssPort: 8080,
            forceTLS: false,
            encrypted: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
        });

        /**
         * =============================
         * PRIVATE CHANNEL SUBSCRIBE
         * =============================
         */
        Echo.private(`user.${userId}`)
            .listen('.notification', (e) => {
                addMessage(e.payload.message, false);
            });

        /**
         * =============================
         * SEND MESSAGE (API)
         * =============================
         */
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            if (!message) return;

            alert('Sending: ' + message);

            axios.post('/api/chat/send', {
                sender_id: userId,
                receiver_id: receiverId,
                message: message
            }).then((response) => {
                alert('Response: ' + JSON.stringify(response.data));
                toastr.success('Message sent successfully!');
                addMessage(message, true);
                input.value = '';
            }).catch((error) => {
                console.log('Full error:', error);
                console.log('Validation errors:', error.response?.data?.errors);
                alert('Error: ' + JSON.stringify(error.response?.data?.errors || error.message));
                toastr.error(error.response?.data?.message || 'Failed to send message');
            });
        }

        /**
         * =============================
         * UI HELPER
         * =============================
         */
        function addMessage(text, isMe) {
            const div = document.createElement('div');
            div.className = 'message ' + (isMe ? 'me' : '');
            div.innerHTML = `<span>${text}</span>`;
            document.getElementById('messages').appendChild(div);
            document.getElementById('messages').scrollTop = 9999;
        }
    </script>

</body>

</html>
