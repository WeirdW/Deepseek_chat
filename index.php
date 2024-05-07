<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeepSeek Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            height: 80vh;
            background-color: #f0f0f0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-top: 0px;
            margin-bottom: 10px;
        }
        #chat-container {
            max-height: 60vh;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            scroll-behavior: smooth; 
        }
        #chat-container div {
            margin-bottom: 5px;
        }
        #chat-form {
            display: flex;
            align-items: center;
        }
        #user-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 10px;
            margin-right: 8px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>DeepSeek Chat</h1>
        <div id="chat-container">
            <?php
            require_once 'Parsedown.php';
            $parsedown = new Parsedown();

            session_start(); 

            $history_file = 'history_' . session_id() . '.txt';
            if (file_exists($history_file)) {
                $history = file_get_contents($history_file);
                echo $history;
            }
            ?>
              <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user-input'])) {
            $user_input = $_POST['user-input'];
            $response = getDeepSeekResponse($user_input);
            echo '<div><strong>You:</strong> ' . htmlspecialchars($user_input) . '</div>';
            echo '<div><strong>DeepSeek:</strong> ' . $parsedown->text(htmlspecialchars($response)) . '</div>';
            $history_file = 'history_' . session_id() . '.txt'; 
            file_put_contents($history_file, '<div><strong>You:</strong> ' . htmlspecialchars($user_input) . '</div>' . PHP_EOL
                . '<div><strong>DeepSeek:</strong> ' . htmlspecialchars($response) . '</div>' . PHP_EOL, FILE_APPEND);
        }

        function getDeepSeekResponse($input) {
            $api_key = getenv('DEEPSEEK_API_KEY'); 
            $url = 'https://api.deepseek.com/chat/completions';
            
            $history_file = 'history_' . session_id() . '.txt';
            $history = file_exists($history_file) ? file_get_contents($history_file) : '';
            
            $data = array(
                'model' => 'deepseek-chat',
                'messages' => array(
                    array('role' => 'system', 'content' => $history),
                    array('role' => 'user', 'content' => $input)
                )
            );

            $options = array(
                'http' => array(
                    'header' => "Content-type: application/json\r\n" .
                                "Authorization: Bearer $api_key\r\n",
                    'method' => 'POST',
                    'content' => json_encode($data)
                )
            );

            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === FALSE) {
                return "Sorry, something went wrong.";
            } else {
                $response_json = json_decode($response, true);
                return $response_json['choices'][0]['message']['content'];
            }
        }
        ?>
        </div>
      
        <form id="chat-form" action="" method="post">
            <input type="text" id="user-input" name="user-input" placeholder="输入您的消息" autocomplete="off">
            <button type="submit" id="submit-button">发送</button>
        </form>
        <script>
            var chatContainer = document.getElementById('chat-container');
            chatContainer.scrollTop = chatContainer.scrollHeight;
            
        document.getElementById('chat-form').addEventListener('submit', function(event) {
            var button = document.getElementById('submit-button');
            button.textContent = '思考中'; 
            button.disabled = true; 
        });
    </script>
    </div>
</body>
</html>
