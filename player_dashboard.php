<?php
// player_dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'player') {
    header("Location: login.php");
    exit();
}

include 'db_config.php';

$user_id = $_SESSION['user_id'];
$msg = isset($_GET['msg']) ? $_GET['msg'] : "";

// Check if the player has any join record
$stmt = $conn->prepare("SELECT team_id, join_status FROM players WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

$hasRequest = false;
$approvedTeamId = null;
$joinStatus = "";
if ($stmt->num_rows > 0) {
    $stmt->bind_result($team_id, $join_status);
    // For this example, we assume only one join record exists per player.
    $stmt->fetch();
    $hasRequest = true;
    $joinStatus = $join_status;
    if ($join_status == 'approved') {
        $approvedTeamId = $team_id;
    }
}
$stmt->close();

$team_details = null;
if ($approvedTeamId) {
    // Fetch team details for the approved team
    $stmt = $conn->prepare("SELECT team_name, sport, min_players, max_players FROM teams WHERE id = ?");
    $stmt->bind_param("i", $approvedTeamId);
    $stmt->execute();
    $result = $stmt->get_result();
    $team_details = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Player Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
  </style>
</head>
<body class="bg-gray-100 p-6">
  <nav class="bg-gray-900 bg-opacity-80 text-white py-4">
    <div class="container mx-auto px-6 flex justify-between items-center">
      <a href="index.php" class="text-xl font-bold flex items-center">
        <i class="fas fa-trophy mr-2"></i>
        Sports League
      </a>
      <a href="logout.php" class="hover:text-blue-400 transition-colors">
        <i class="fas fa-sign-out-alt mr-1"></i> Logout
      </a>
    </div>
  </nav>

  <div class="max-w-4xl mx-auto glass-effect p-6 rounded-lg shadow-lg mt-6">
    <h1 class="text-3xl font-bold mb-4 text-center">Player Dashboard</h1>
    <?php if($msg) echo "<p class='mb-4 text-green-500 text-center'>" . htmlspecialchars($msg) . "</p>"; ?>
    
    <?php if ($approvedTeamId && $team_details): ?>
      <h2 class="text-xl font-semibold mb-2">Your Team Details</h2>
      <p><strong>Team Name:</strong> <?php echo htmlspecialchars($team_details['team_name']); ?></p>
      <p><strong>Sport:</strong> <?php echo htmlspecialchars($team_details['sport']); ?></p>
    <?php else: ?>
      <?php if ($hasRequest): ?>
        <p class="text-orange-500 mb-4 text-center">Your join request is currently <strong><?php echo ucfirst(htmlspecialchars($joinStatus)); ?></strong>.</p>
      <?php else: ?>
        <a href="join_team.php" class="bg-blue-500 text-white p-2 rounded">Request to Join a Team</a>
      <?php endif; ?>
    <?php endif; ?>
    
    <ul class="space-y-4 mt-4">
      <li>
        <a href="performance.php" class="bg-blue-500 text-white p-2 rounded flex items-center justify-center transition-all duration-200 hover:bg-blue-600">
          <i class="fas fa-chart-line mr-2"></i> View Performance
        </a>
      </li>
      <li>
        <a href="messaging.php" class="bg-blue-500 text-white p-2 rounded flex items-center justify-center transition-all duration-200 hover:bg-blue-600">
          <i class="fas fa-comments mr-2"></i> Messaging
        </a>
      </li>
      <li>
        <a href="match_schedule.php" class="bg-blue-500 text-white p-2 rounded flex items-center justify-center transition-all duration-200 hover:bg-blue-600">
          <i class="fas fa-calendar-alt mr-2"></i> Scheduled Matches
        </a>
      </li>
      <li>
        <a href="logout.php" class="bg-red-500 text-white p-2 rounded flex items-center justify-center transition-all duration-200 hover:bg-red-600">
          <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</body>
</html>
