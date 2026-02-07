<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Realtime Chat Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Laravel Echo -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>

    <!-- Pusher (required for Echo compatibility, Reverb works with this) -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            display: flex;
            justify-content: center;
            padding-top: 40px;
        }

        .chat-box {
            width: 420px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,.1);
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
            background: #f1f1f1;
        }

        .message.me span {
            background: #4f46e5;
            color: #fff;
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
            border: 1px solid #ddd;
        }

        .chat-input button {
            margin-left: 10px;
            padding: 10px 15px;
            border: none;
            background: #4f46e5;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="chat-box">
    <div class="chat-header">
        Reverb Chat (Test)
    </div>

    <div class="messages" id="messages"></div>

    <div class="chat-input">
        <input type="text" id="messageInput" placeholder="Type a message...">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
    const userId = {{ auth()->id() ?? 1 }}; // temp fallback
    const receiverId = 2; // change for testing

    // Axios setup
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    // Echo + Reverb
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
    });

    // Subscribe to private channel
    Echo.private(`user.${userId}`)
        .listen('.notification', (e) => {
            addMessage(e.message, false);
        });
        

    function sendMessage() {
        const message = document.getElementById('messageInput').value;
        if (!message) return;

        axios.post('/api/chat/send', {
            receiver_id: receiverId,
            message: message
        }).then(() => {
            addMessage(message, true);
            document.getElementById('messageInput').value = '';
        });
    }

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
