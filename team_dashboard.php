<?php
// team_dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'team') {
    header("Location: login.php");
    exit();
}
include 'db_config.php';
$user_id = $_SESSION['user_id'];
$msg = "";

// Check if team already exists for this team user
$stmt = $conn->prepare("SELECT * FROM teams WHERE owner_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_team = $stmt->get_result();
$team = $result_team->fetch_assoc();
$stmt->close();

if (!$team) {
    // If no team exists, show team creation form
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_team'])) {
        $team_name = $_POST['team_name'];
        $sport = $_POST['sport'];
        $min_players = $_POST['min_players'];
        $max_players = $_POST['max_players'];
        
        $stmt = $conn->prepare("INSERT INTO teams (owner_id, team_name, sport, min_players, max_players) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issii", $user_id, $team_name, $sport, $min_players, $max_players);
        if ($stmt->execute()) {
            $msg = "Team created successfully.";
        } else {
            $msg = "Error: " . $stmt->error;
        }
        $stmt->close();
        // Refresh the team details after creation
        $stmt = $conn->prepare("SELECT * FROM teams WHERE owner_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result_team = $stmt->get_result();
        $team = $result_team->fetch_assoc();
        $stmt->close();
    }
}
  
// If team exists, fetch current approved players count for this team
if ($team) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM players WHERE team_id = ? AND join_status = 'approved'");
    $stmt->bind_param("i", $team['id']);
    $stmt->execute();
    $stmt->bind_result($approvedCount);
    $stmt->fetch();
    $stmt->close();
  
    $minPlayers = $team['min_players'];
    if ($approvedCount >= $minPlayers) {
        $statusMessage = "<span class='text-green-600'><i class='fas fa-check-circle mr-2'></i>Team meets the minimum player requirement.</span>";
    } else {
        $statusMessage = "<span class='text-red-600'><i class='fas fa-exclamation-circle mr-2'></i>Team does NOT meet the minimum requirement. (Approved: $approvedCount / Minimum: $minPlayers)</span>";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Team Dashboard</title>
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
    .stat-card {
      transition: all 0.3s ease;
    }
    .stat-card:hover {
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
      <a href="logout.php" class="hover:text-blue-400 transition-colors">
        <i class="fas fa-sign-out-alt mr-1"></i> Logout
      </a>
    </div>
  </nav>

  <div class="container mx-auto p-6">
    <div class="glass-effect p-8 rounded-xl shadow-xl">
      <h1 class="text-3xl font-bold mb-6 text-center">Team Dashboard</h1>
      <?php if($msg) echo "<p class='mb-6 text-green-500 text-center font-medium'><i class='fas fa-check-circle mr-2'></i>" . htmlspecialchars($msg) . "</p>"; ?>

      <?php if (!$team): ?>
        <!-- Team Creation Form -->
        <div class="max-w-2xl mx-auto">
          <h2 class="text-2xl font-semibold mb-6 text-center">Create Your Team</h2>
          <form method="POST" action="" class="space-y-6">
            <div>
              <label class="block mb-2 font-medium">
                <i class="fas fa-users mr-2"></i>Team Name
              </label>
              <input type="text" name="team_name" required 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
                placeholder="Enter team name">
            </div>
            
            <div>
              <label class="block mb-2 font-medium">
                <i class="fas fa-basketball-ball mr-2"></i>Sport
              </label>
              <input type="text" name="sport" required 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
                placeholder="Enter sport type">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block mb-2 font-medium">
                  <i class="fas fa-users-cog mr-2"></i>Minimum Players
                </label>
                <input type="number" name="min_players" required 
                  class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
                  placeholder="Min players">
              </div>
              
              <div>
                <label class="block mb-2 font-medium">
                  <i class="fas fa-user-plus mr-2"></i>Maximum Players
                </label>
                <input type="number" name="max_players" required 
                  class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
                  placeholder="Max players">
              </div>
            </div>
            
            <button type="submit" name="create_team" 
              class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 flex items-center justify-center">
              <i class="fas fa-plus-circle mr-2"></i>Create Team
            </button>
          </form>
        </div>
      <?php else: ?>
        <!-- Team Details Display -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="stat-card bg-blue-500 text-white p-6 rounded-xl">
            <div class="text-4xl mb-2"><i class="fas fa-users"></i></div>
            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($team['team_name']); ?></h3>
            <p class="text-sm opacity-80">Team Name</p>
          </div>
          
          <div class="stat-card bg-green-500 text-white p-6 rounded-xl">
            <div class="text-4xl mb-2"><i class="fas fa-basketball-ball"></i></div>
            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($team['sport']); ?></h3>
            <p class="text-sm opacity-80">Sport</p>
          </div>
          
          <div class="stat-card bg-purple-500 text-white p-6 rounded-xl">
            <div class="text-4xl mb-2"><i class="fas fa-user-friends"></i></div>
            <h3 class="text-lg font-semibold"><?php echo $approvedCount; ?> / <?php echo $team['max_players']; ?></h3>
            <p class="text-sm opacity-80">Players</p>
          </div>
          
          <div class="stat-card bg-yellow-500 text-white p-6 rounded-xl">
            <div class="text-4xl mb-2"><i class="fas fa-chart-line"></i></div>
            <h3 class="text-lg font-semibold"><?php echo $team['min_players']; ?></h3>
            <p class="text-sm opacity-80">Minimum Required</p>
          </div>
        </div>

        <div class="mb-8 p-4 rounded-lg bg-gray-50">
          <?php echo $statusMessage; ?>
        </div>
        
        <!-- Navigation Options -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <a href="team_requests.php" 
            class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg flex items-center justify-center transition-all duration-200">
            <i class="fas fa-user-plus mr-2"></i>View Join Requests
          </a>
          <a href="team_players.php" 
            class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg flex items-center justify-center transition-all duration-200">
            <i class="fas fa-users mr-2"></i>Current Team Members
          </a>
          <a href="performance.php" 
            class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg flex items-center justify-center transition-all duration-200">
            <i class="fas fa-chart-line mr-2"></i>Performance
          </a>
          <a href="match_schedule.php" 
            class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg flex items-center justify-center transition-all duration-200">
            <i class="fas fa-calendar-alt mr-2"></i>Scheduled Matches
          </a>
          <a href="messaging.php" 
            class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg flex items-center justify-center transition-all duration-200">
            <i class="fas fa-comments mr-2"></i>Messaging
          </a>
          <a href="logout.php" 
            class="bg-red-600 hover:bg-red-700 text-white p-4 rounded-lg flex items-center justify-center transition-all duration-200">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
