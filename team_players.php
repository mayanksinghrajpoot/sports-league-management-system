<?php
// team_players.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'team') {
    header("Location: login.php");
    exit();
}
include 'db_config.php';
$user_id = $_SESSION['user_id'];

// Get the team of the current team user
$stmt = $conn->prepare("SELECT id, team_name FROM teams WHERE owner_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$team = $result->fetch_assoc();
$team_id = $team['id'];
$team_name = $team['team_name'];
$stmt->close();

if (!$team_id) {
    die("You do not have a team yet.");
}

// Fetch approved players for this team
$stmt = $conn->prepare("SELECT players.id as player_id, users.username, players.sport 
                        FROM players 
                        JOIN users ON players.user_id = users.id 
                        WHERE players.team_id = ? AND players.join_status = 'approved'");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();
$players = $result->fetch_all(MYSQLI_ASSOC);
$player_count = count($players);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Players - <?php echo htmlspecialchars($team_name); ?></title>
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
    .player-card {
      transition: all 0.3s ease;
    }
    .player-card:hover {
      transform: translateY(-5px);
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
        <a href="team_dashboard.php" class="hover:text-blue-400 transition-colors">
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
      <!-- Team Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold mb-2">
          <i class="fas fa-users text-blue-600 mr-2"></i>
          <?php echo htmlspecialchars($team_name); ?> - Team Players
        </h1>
        <p class="text-gray-600">
          Total Players: <?php echo $player_count; ?>
        </p>
      </div>

      <?php if($player_count > 0): ?>
        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-blue-500 text-white rounded-lg p-6 text-center">
            <i class="fas fa-users text-4xl mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo $player_count; ?></h3>
            <p>Active Players</p>
          </div>
          <div class="bg-green-500 text-white rounded-lg p-6 text-center">
            <i class="fas fa-trophy text-4xl mb-2"></i>
            <h3 class="text-2xl font-bold">Team</h3>
            <p><?php echo htmlspecialchars($team_name); ?></p>
          </div>
          <div class="bg-purple-500 text-white rounded-lg p-6 text-center">
            <i class="fas fa-star text-4xl mb-2"></i>
            <h3 class="text-2xl font-bold">Status</h3>
            <p>Active</p>
          </div>
        </div>

        <!-- Players Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach($players as $player): ?>
            <div class="player-card bg-white rounded-lg shadow-md overflow-hidden">
              <div class="bg-gray-800 text-white p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <i class="fas fa-user-circle text-2xl mr-3"></i>
                    <div>
                      <h3 class="font-semibold"><?php echo htmlspecialchars($player['username']); ?></h3>
                      <p class="text-sm text-gray-300">Player ID: #<?php echo $player['player_id']; ?></p>
                    </div>
                  </div>
                  <span class="bg-green-500 px-2 py-1 rounded-full text-xs">Active</span>
                </div>
              </div>
              <div class="p-4">
                <div class="flex items-center text-gray-600 mb-2">
                  <i class="fas fa-running mr-2"></i>
                  <span>Sport: <?php echo htmlspecialchars($player['sport']); ?></span>
                </div>
                <div class="flex items-center text-gray-600">
                  <i class="fas fa-shield-alt mr-2"></i>
                  <span>Team Member</span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-12">
          <div class="text-6xl text-gray-400 mb-4">
            <i class="fas fa-users-slash"></i>
          </div>
          <h2 class="text-2xl font-semibold text-gray-600 mb-2">No Players Yet</h2>
          <p class="text-gray-500">Your team doesn't have any approved players at the moment.</p>
        </div>
      <?php endif; ?>

      <!-- Back Button -->
      <div class="mt-8 text-center">
        <a href="team_dashboard.php" 
           class="inline-flex items-center px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200">
          <i class="fas fa-arrow-left mr-2"></i>
          Back to Dashboard
        </a>
      </div>
    </div>
  </div>
</body>
</html>
