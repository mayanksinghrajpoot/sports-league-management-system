<?php
// messaging.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db_config.php';
$user_id = $_SESSION['user_id'];
$msg = "";

// Handle sending message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $receiver_username = $_POST['receiver'];
    $message_text = $_POST['message'];
    
    // Get receiver id
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $receiver_username);
    $stmt->execute();
    $stmt->bind_result($receiver_id);
    $stmt->fetch();
    $stmt->close();
    
    if ($receiver_id) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message_text);
        if ($stmt->execute()) {
            $msg = "Message sent successfully!";
        } else {
            $msg = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $msg = "Receiver not found.";
    }
}

// Get current user's username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_username);
$stmt->fetch();
$stmt->close();

// Fetch messages for this user (both sent and received)
$sql = "SELECT m.*, 
        s.username as sender_name,
        r.username as receiver_name
        FROM messages m 
        JOIN users s ON m.sender_id = s.id 
        JOIN users r ON m.receiver_id = r.id
        WHERE m.sender_id = $user_id OR m.receiver_id = $user_id
        ORDER BY m.sent_at DESC";
$result_messages = $conn->query($sql);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Messaging - Sports League</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-image: url('https://images.pexels.com/photos/46798/the-ball-stadion-football-the-pitch-46798.jpeg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
    }
    .glass-effect {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
    }
    .message-bubble {
      max-width: 80%;
      position: relative;
    }
    .message-bubble::after {
      content: '';
      position: absolute;
      bottom: 0;
      width: 0;
      height: 0;
      border: 8px solid transparent;
    }
    .message-sent::after {
      right: -16px;
      border-left-color: #2563eb;
      border-right: 0;
      margin-bottom: 8px;
    }
    .message-received::after {
      left: -16px;
      border-right-color: #e5e7eb;
      border-left: 0;
      margin-bottom: 8px;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-100">
  <!-- Navigation -->
  <nav class="bg-gray-900 bg-opacity-80 text-white py-4">
    <div class="container mx-auto px-6 flex justify-between items-center">
      <a href="index.php" class="text-xl font-bold flex items-center">
        <i class="fas fa-trophy mr-2"></i>
        Sports League
      </a>
      <div class="flex items-center space-x-4">
        <a href="<?php echo $_SESSION['role']; ?>_dashboard.php" class="hover:text-blue-400 transition-colors">
          <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
        </a>
        <a href="logout.php" class="hover:text-blue-400 transition-colors">
          <i class="fas fa-sign-out-alt mr-1"></i> Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="container mx-auto p-6">
    <div class="glass-effect p-8 rounded-xl shadow-xl">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">
          <i class="fas fa-comments text-blue-600 mr-2"></i>
          Messages
        </h1>
        <div class="text-gray-600">
          <i class="fas fa-user-circle mr-2"></i>
          <?php echo htmlspecialchars($current_username); ?>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo strpos($msg, 'Error') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
          <div class="flex items-center">
            <i class="<?php echo strpos($msg, 'Error') !== false ? 'fas fa-exclamation-circle' : 'fas fa-check-circle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($msg); ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Message Form -->
      <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4">
          <i class="fas fa-paper-plane text-blue-600 mr-2"></i>
          Send New Message
        </h2>
        <form method="POST" action="" class="space-y-4">
          <div>
            <label class="block mb-2 font-medium">
              <i class="fas fa-user mr-2"></i>Recipient Username
            </label>
            <input type="text" name="receiver" required 
              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
              placeholder="Enter username">
          </div>
          
          <div>
            <label class="block mb-2 font-medium">
              <i class="fas fa-envelope mr-2"></i>Message
            </label>
            <textarea name="message" required 
              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
              rows="4"
              placeholder="Type your message here..."></textarea>
          </div>
          
          <button type="submit" name="send_message" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center">
            <i class="fas fa-paper-plane mr-2"></i>
            Send Message
          </button>
        </form>
      </div>

      <!-- Messages Display -->
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-6">
          <i class="fas fa-inbox text-blue-600 mr-2"></i>
          Message History
        </h2>
        
        <?php if($result_messages && $result_messages->num_rows > 0): ?>
          <div class="space-y-6">
            <?php while($msg_item = $result_messages->fetch_assoc()): ?>
              <?php $is_sent = $msg_item['sender_id'] == $user_id; ?>
              <div class="flex <?php echo $is_sent ? 'justify-end' : 'justify-start'; ?>">
                <div class="message-bubble <?php echo $is_sent ? 'message-sent bg-blue-600 text-white' : 'message-received bg-gray-100 text-gray-800'; ?> p-4 rounded-lg">
                  <div class="flex items-center mb-2">
                    <i class="fas fa-user-circle mr-2"></i>
                    <span class="font-medium">
                      <?php echo $is_sent ? 'To: ' . htmlspecialchars($msg_item['receiver_name']) : 'From: ' . htmlspecialchars($msg_item['sender_name']); ?>
                    </span>
                  </div>
                  <p class="mb-2"><?php echo htmlspecialchars($msg_item['message']); ?></p>
                  <div class="text-sm <?php echo $is_sent ? 'text-blue-100' : 'text-gray-500'; ?>">
                    <i class="far fa-clock mr-1"></i>
                    <?php echo date('M d, Y h:i A', strtotime($msg_item['sent_at'])); ?>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-8">
            <div class="text-6xl text-gray-400 mb-4">
              <i class="far fa-comment-alt"></i>
            </div>
            <p class="text-xl text-gray-600">No messages yet</p>
            <p class="text-gray-500">Start a conversation by sending a message above!</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
