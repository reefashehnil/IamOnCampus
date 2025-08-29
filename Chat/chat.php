<?php
session_start();
include '../Connection/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../Login/login.php"); exit; }

$my_id = $_SESSION['user_id'];
$other_id = intval($_GET['id'] ?? 0);

// Get other user's info
$user_stmt = $conn->prepare("SELECT F_name, L_name, DP FROM users WHERE User_id = ?");
$user_stmt->bind_param("i", $other_id);
$user_stmt->execute();
$other_user = $user_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #1a1a1a, #2a1a3a); /* Black to dark violet gradient */
            font-family: Arial; 
        }
        .chat-container { 
            max-width: 800px; 
            margin: 20px auto; 
            background: #2c1e3f; /* Dark violet shade */
            border-radius: 8px; 
            display: flex; 
            flex-direction: column; 
            height: 85vh; 
            overflow: hidden; 
            box-shadow: 0 0 10px rgba(0,0,0,0.5); /* Darker shadow for contrast */
        }
        .chat-header { 
            padding: 10px; 
            background: linear-gradient(90deg, #3a2a5a, #4a3066); /* Violet gradient for header */
            color: white; 
            font-weight: bold; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .chat-header img { 
            width: 40px; 
            height: 40px; 
            border-radius: 50%; 
        }
        .chat-messages { 
            flex: 1; 
            padding: 15px; 
            overflow-y: auto; 
            display: flex; 
            flex-direction: column; 
            gap: 10px; 
        }
        .message-wrapper { 
            display: flex; 
            flex-direction: column; 
            max-width: 70%; 
        }
        .me { 
            align-self: flex-end; 
            text-align: right; 
        }
        .me .bubble { 
            background: #0084ff; 
            color: white; 
            border-radius: 18px 18px 0 18px; 
            padding: 10px; 
            display: inline-block; 
        }
        .them { 
            align-self: flex-start; 
            text-align: left; 
        }
        .them .bubble { 
            background: #4a3066; /* Dark violet for received messages */
            color: white; 
            border-radius: 18px 18px 18px 0; 
            padding: 10px; 
            display: inline-block; 
        }
        .sender-name { 
            font-size: 0.8rem; 
            color: #ccc; /* Light gray for contrast */
            margin-bottom: 2px; 
        }
        .seen-status { 
            font-size: 0.8rem; 
            color: #ccc; /* Light gray for contrast */
            margin-top: 3px; 
        }
        .chat-input { 
            display: flex; 
            padding: 10px; 
            border-top: 1px solid #4a3066; /* Violet-tinted border */
        }
        .chat-input input { 
            flex: 1; 
            border-radius: 20px; 
            border: 1px solid #4a3066; /* Violet border */
            padding: 10px; 
            background: #3a2a5a; /* Dark violet input background */
            color: white; 
        }
        .chat-input input::placeholder { 
            color: #ccc; /* Light gray placeholder text */
        }
        .chat-input button { 
            background: #4a3066; /* Violet button */
            color: white; 
            border: none; 
            border-radius: 50%; 
            width: 40px; 
            height: 40px; 
            margin-left: 5px; 
        }
    </style>
</head>
<body>

<a href="chat_list.php" class="btn btn-warning m-3"> Back to Messages</a>

<div class="chat-container">
    <div class="chat-header">
        <img src="../DP_Uploads/<?= htmlspecialchars($other_user['DP'] ?: 'default.jpg') ?>" alt="">
        <?= htmlspecialchars($other_user['F_name'] . ' ' . $other_user['L_name']) ?>
    </div>
    <div id="chat-box" class="chat-messages"></div>
    <div class="chat-input">
        <input type="text" id="message-input" placeholder="Type a message...">
        <button onclick="sendMessage()">➤</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let myId = <?= $my_id ?>;
let otherUserId = <?= $other_id ?>;

function loadMessages() {
    $.get("fetch_messages.php?other_id=" + otherUserId, function(data) {
        let res = JSON.parse(data);
        let messages = res.messages;
        let seenInfo = res.seen_info;
        let box = $("#chat-box");
        box.html("");
        messages.forEach(m => {
            let cls = (m.Sender_id == myId) ? "me" : "them";
            let name = (cls === "them") ? `<div class="sender-name">${m.SenderName}</div>` : "";
            box.append(`<div class="message-wrapper ${cls}">${name}<div class="bubble">${$('<div>').text(m.Message_text).html()}</div></div>`);
        });
        if (seenInfo) {
            box.append(`<div class="seen-status">${seenInfo}</div>`);
        }
        box.scrollTop(box[0].scrollHeight);
    });
}

function sendMessage() {
    let text = $("#message-input").val();
    if (!text.trim()) return;
    $.post("send_message.php", {receiver_id: otherUserId, message: text}, function() {
        $("#message-input").val("");
        loadMessages();
    });
}

setInterval(loadMessages, 2000);
loadMessages();
</script>

</body>
</html>