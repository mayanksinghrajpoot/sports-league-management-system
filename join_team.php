<?php
// join_team.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'player') {
    header("Location: login.php");
    exit();
}
include 'db_config.php';

$msg = "";
// allow player to search for teams 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_sport'])) {
    $sport = $_POST['sport'];
    $sql = "SELECT t.*, 
            (SELECT COUNT(*) FROM players WHERE team_id = t.id AND join_status = 'approved') as current_players 
            FROM teams t 
            WHERE sport = '" . $conn->real_escape_string($sport) . "'";
    $result_teams = $conn->query($sql);
} elseif (isset($_GET['sport'])) {
    $sport = $_GET['sport'];
    $sql = "SELECT t.*, 
            (SELECT COUNT(*) FROM players WHERE team_id = t.id AND join_status = 'approved') as current_players 
            FROM teams t 
            WHERE sport = '" . $conn->real_escape_string($sport) . "'";
    $result_teams = $conn->query($sql);
}

// Get list of available sports
$sql = "SELECT DISTINCT sport FROM teams ORDER BY sport";
$sports_result = $conn->query($sql);
$available_sports = [];
if ($sports_result->num_rows > 0) {
    while($row = $sports_result->fetch_assoc()) {
        $available_sports[] = $row['sport'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Join a Team - Sports League</title>
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
    .team-card {
      transition: all 0.3s ease;
    }
    .team-card:hover {
      transform: translateY(-5px);
    }
    .sport-tag {
      transition: all 0.3s ease;
    }
    .sport-tag:hover {
      transform: translateY(-2px);
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
        <a href="player_dashboard.php" class="hover:text-blue-400 transition-colors">
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
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold mb-2">
          <i class="fas fa-users text-blue-600 mr-2"></i>
          Find Your Team
        </h1>
        <p class="text-gray-600">Join a team and start your journey in sports!</p>
      </div>

      <!-- Search Form -->
      <div class="max-w-2xl mx-auto mb-8">
        <form method="POST" action="" class="bg-white p-6 rounded-lg shadow-md">
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">
              <i class="fas fa-search mr-2"></i>Search by Sport
            </label>
            <input type="text" name="sport" required 
              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
              placeholder="Enter sport name (e.g., Football, Basketball)">
          </div>
          <button type="submit" name="search_sport" 
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 flex items-center justify-center">
            <i class="fas fa-search mr-2"></i>
            Search Teams
          </button>
        </form>

        <?php if(!empty($available_sports)): ?>
          <div class="mt-4">
            <p class="text-gray-600 mb-2">Popular Sports:</p>
            <div class="flex flex-wrap gap-2">
              <?php foreach($available_sports as $available_sport): ?>
                <a href="?sport=<?php echo urlencode($available_sport); ?>" 
                  class="sport-tag bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-full text-sm transition-all">
                  <?php echo htmlspecialchars($available_sport); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php if(isset($result_teams)): ?>
        <div class="mb-6">
          <h2 class="text-2xl font-semibold mb-4">
            <i class="fas fa-search text-blue-600 mr-2"></i>
            Teams for '<?php echo htmlspecialchars($sport); ?>'
          </h2>
          
          <?php if($result_teams->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <?php while($team = $result_teams->fetch_assoc()): ?>
                <div class="team-card bg-white rounded-lg shadow-md overflow-hidden">
                  <div class="bg-gray-800 text-white p-4">
                    <h3 class="font-semibold text-lg mb-1"><?php echo htmlspecialchars($team['team_name']); ?></h3>
                    <p class="text-sm text-gray-300">Team #<?php echo $team['id']; ?></p>
                  </div>
                  <div class="p-4">
                    <div class="space-y-2 mb-4">
                      <div class="flex items-center text-gray-600">
                        <i class="fas fa-running mr-2"></i>
                        <span><?php echo htmlspecialchars($team['sport']); ?></span>
                      </div>
                      <div class="flex items-center text-gray-600">
                        <i class="fas fa-users mr-2"></i>
                        <span>Players: <?php echo $team['current_players']; ?>/<?php echo $team['max_players']; ?></span>
                      </div>
                      <div class="flex items-center text-gray-600">
                        <i class="fas fa-user-friends mr-2"></i>
                        <span>Min Required: <?php echo $team['min_players']; ?></span>
                      </div>
                    </div>
                    <a href="process_join.php?team_id=<?php echo $team['id']; ?>" 
                      class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-lg transition-colors duration-200">
                      <i class="fas fa-plus-circle mr-2"></i>Request to Join
                    </a>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-8">
              <div class="text-6xl text-gray-400 mb-4">
                <i class="fas fa-search"></i>
              </div>
              <h3 class="text-xl font-semibold text-gray-600 mb-2">No Teams Found</h3>
              <p class="text-gray-500">No teams are currently available for <?php echo htmlspecialchars($sport); ?>.</p>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
