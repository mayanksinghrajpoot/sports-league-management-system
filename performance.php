<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_config.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$msg = "";

if ($role == 'player') {
    $performance = null;
    $team_details = null;

    // Check if the player has an approved join record
    $stmt = $conn->prepare("SELECT id, team_id FROM players WHERE user_id = ? AND join_status = 'approved'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $hasJoinRecord = ($stmt->num_rows > 0);
    $player_id = null;
    $team_id = null;
    if ($hasJoinRecord) {
        $stmt->bind_result($player_id, $team_id);
        $stmt->fetch();
    }
    $stmt->close();

    // If the player is in a team, fetch team details
    if ($team_id) {
        $stmt = $conn->prepare("SELECT team_name, sport, min_players, max_players FROM teams WHERE id = ?");
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $team_details = $result->fetch_assoc();
        $stmt->close();
    }

    // Fetch performance data for the player using their player id
    if ($player_id) {
        $stmt = $conn->prepare("SELECT matches_played, goals, assists, rating FROM performance WHERE player_id = ?");
        $stmt->bind_param("i", $player_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $performance = $result->fetch_assoc();
        $stmt->close();
    }

} else {
    $players = [];
    $team_id = null; // only used if role is team
    if ($role == 'team') {
        // Get the team id for this team user
        $stmt = $conn->prepare("SELECT id FROM teams WHERE owner_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($team_id);
        if (!$stmt->fetch()) {
            $stmt->close();
            $conn->close();
            die("Team not found for the current user.");
        }
        $stmt->close();

        // Fetch approved players for this team, ordered by rating descending.
        $query = "SELECT p.id AS player_id, u.username, 
                         IFNULL(per.matches_played, 0) AS matches_played, 
                         IFNULL(per.goals, 0) AS goals, 
                         IFNULL(per.assists, 0) AS assists, 
                         IFNULL(per.rating, 0) AS rating
                  FROM players p 
                  JOIN users u ON p.user_id = u.id
                  LEFT JOIN performance per ON p.id = per.player_id
                  WHERE p.team_id = ? AND p.join_status = 'approved'
                  ORDER BY rating DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $players[] = $row;
        }
        $stmt->close();
    } else { // admin
        // Fetch all approved players with team details and order by team name and rating descending
        $query = "SELECT p.id AS player_id, u.username, 
                         IFNULL(per.matches_played, 0) AS matches_played, 
                         IFNULL(per.goals, 0) AS goals, 
                         IFNULL(per.assists, 0) AS assists, 
                         IFNULL(per.rating, 0) AS rating,
                         t.team_name
                  FROM players p 
                  JOIN users u ON p.user_id = u.id
                  JOIN teams t ON p.team_id = t.id
                  LEFT JOIN performance per ON p.id = per.player_id
                  WHERE p.join_status = 'approved'
                  ORDER BY t.team_name, rating DESC";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $groupedPlayers[$row['team_name']][] = $row;
        }
    }

    // Process performance update form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_performance'])) {
        // Expected arrays: player_id[], matches_played[], goals[], assists[], rating[]
        foreach ($_POST['player_id'] as $index => $player_id) {
            $matches_played = intval($_POST['matches_played'][$index]);
            $goals = intval($_POST['goals'][$index]);
            $assists = intval($_POST['assists'][$index]);
            $rating = floatval($_POST['rating'][$index]);

            // Check if performance record exists for this player
            $stmt = $conn->prepare("SELECT id FROM performance WHERE player_id = ?");
            $stmt->bind_param("i", $player_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->close();
                // Update existing record
                $stmt = $conn->prepare("UPDATE performance SET matches_played = ?, goals = ?, assists = ?, rating = ? WHERE player_id = ?");
                $stmt->bind_param("iiidi", $matches_played, $goals, $assists, $rating, $player_id);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt->close();
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO performance (player_id, matches_played, goals, assists, rating) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiid", $player_id, $matches_played, $goals, $assists, $rating);
                $stmt->execute();
                $stmt->close();
            }
        }
        $msg = "Performance data updated successfully.";
        
        // Refresh players list after update
        if ($role == 'team') {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $team_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $players = [];
            while ($row = $result->fetch_assoc()) {
                $players[] = $row;
            }
            $stmt->close();
        } else {
            $result = $conn->query($query);
            $groupedPlayers = [];
            while ($row = $result->fetch_assoc()) {
                $groupedPlayers[$row['team_name']][] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Performance Dashboard - Sports League</title>
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
    .performance-table th,
    .performance-table td {
      padding: 1rem;
    }
    .rating-input {
      position: relative;
    }
    .rating-input::before {
      content: '⭐';
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #EAB308;
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
        <a href="<?php echo $role; ?>_dashboard.php" class="hover:text-blue-400 transition-colors">
          <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
        </a>
        <a href="logout.php" class="hover:text-blue-400 transition-colors">
          <i class="fas fa-sign-out-alt mr-1"></i> Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="container mx-auto p-6">
    <?php if ($role == 'player'): ?>
      <!-- Player View -->
      <div class="glass-effect p-8 rounded-xl shadow-xl">
        <h1 class="text-3xl font-bold mb-8 text-center">
          <i class="fas fa-chart-line text-blue-600 mr-2"></i>
          Performance Dashboard
        </h1>
        
        <?php if ($msg) echo "<div class='mb-6 bg-green-100 text-green-700 p-4 rounded-lg'><i class='fas fa-check-circle mr-2'></i>" . htmlspecialchars($msg) . "</div>"; ?>
        
        <?php if ($team_details): ?>
          <!-- Team Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-blue-500 text-white p-6 rounded-xl">
              <div class="text-4xl mb-2"><i class="fas fa-users"></i></div>
              <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($team_details['team_name']); ?></h3>
              <p class="text-sm opacity-80">Team Name</p>
            </div>
            
            <div class="stat-card bg-green-500 text-white p-6 rounded-xl">
              <div class="text-4xl mb-2"><i class="fas fa-running"></i></div>
              <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($team_details['sport']); ?></h3>
              <p class="text-sm opacity-80">Sport</p>
            </div>
            
            <div class="stat-card bg-purple-500 text-white p-6 rounded-xl">
              <div class="text-4xl mb-2"><i class="fas fa-user-friends"></i></div>
              <h3 class="text-lg font-semibold"><?php echo $team_details['min_players']; ?> - <?php echo $team_details['max_players']; ?></h3>
              <p class="text-sm opacity-80">Team Size Range</p>
            </div>
            
            <div class="stat-card bg-yellow-500 text-white p-6 rounded-xl">
              <div class="text-4xl mb-2"><i class="fas fa-star"></i></div>
              <h3 class="text-lg font-semibold"><?php echo $performance ? number_format($performance['rating'], 1) : 'N/A'; ?></h3>
              <p class="text-sm opacity-80">Player Rating</p>
            </div>
          </div>

          <?php if ($performance): ?>
            <!-- Performance Metrics -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
              <h2 class="text-2xl font-semibold mb-6">
                <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                Performance Metrics
              </h2>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 rounded-lg bg-gray-50">
                  <i class="fas fa-gamepad text-4xl text-blue-500 mb-2"></i>
                  <h3 class="text-2xl font-bold"><?php echo htmlspecialchars($performance['matches_played']); ?></h3>
                  <p class="text-gray-600">Matches Played</p>
                </div>
                
                <div class="text-center p-4 rounded-lg bg-gray-50">
                  <i class="fas fa-futbol text-4xl text-green-500 mb-2"></i>
                  <h3 class="text-2xl font-bold"><?php echo htmlspecialchars($performance['goals']); ?></h3>
                  <p class="text-gray-600">Goals Scored</p>
                </div>
                
                <div class="text-center p-4 rounded-lg bg-gray-50">
                  <i class="fas fa-hands-helping text-4xl text-purple-500 mb-2"></i>
                  <h3 class="text-2xl font-bold"><?php echo htmlspecialchars($performance['assists']); ?></h3>
                  <p class="text-gray-600">Assists</p>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center py-8 bg-yellow-50 rounded-lg">
              <i class="fas fa-exclamation-circle text-6xl text-yellow-500 mb-4"></i>
              <p class="text-xl text-gray-700">No performance data available yet.</p>
              <p class="text-gray-600">Check back later or contact your team manager for an update.</p>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="text-center py-12 bg-red-50 rounded-lg">
            <i class="fas fa-user-slash text-6xl text-red-500 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-800 mb-2">Not Part of a Team</h2>
            <p class="text-gray-600 mb-4">You need to join a team to view performance statistics.</p>
            <a href="join_team.php" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
              <i class="fas fa-users mr-2"></i>
              Join a Team
            </a>
          </div>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <!-- Admin / Team View -->
      <div class="glass-effect p-8 rounded-xl shadow-xl">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-3xl font-bold">
            <i class="fas fa-chart-line text-blue-600 mr-2"></i>
            Performance Management
          </h1>
          <div class="text-gray-600">
            <i class="fas fa-user-shield mr-2"></i>
            <?php echo ucfirst($role); ?> Dashboard
          </div>
        </div>

        <?php if ($msg) echo "<div class='mb-6 bg-green-100 text-green-700 p-4 rounded-lg'><i class='fas fa-check-circle mr-2'></i>" . htmlspecialchars($msg) . "</div>"; ?>

        <form method="POST" action="">
          <?php if ($role == 'admin'): ?>
            <?php foreach ($groupedPlayers as $teamName => $playersGroup): ?>
              <div class="mb-8">
                <h2 class="text-2xl font-semibold mb-4">
                  <i class="fas fa-users text-blue-600 mr-2"></i>
                  <?php echo htmlspecialchars($teamName); ?>
                </h2>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                  <table class="min-w-full performance-table">
                    <thead class="bg-gray-800 text-white">
                      <tr>
                        <th class="text-left">Player</th>
                        <th class="text-center">Matches</th>
                        <th class="text-center">Goals</th>
                        <th class="text-center">Assists</th>
                        <th class="text-center">Rating</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <?php foreach ($playersGroup as $player): ?>
                        <tr class="hover:bg-gray-50">
                          <td class="flex items-center">
                            <i class="fas fa-user-circle text-gray-400 mr-2"></i>
                            <?php echo htmlspecialchars($player['username']); ?>
                            <input type="hidden" name="player_id[]" value="<?php echo $player['player_id']; ?>">
                          </td>
                          <td>
                            <input type="number" name="matches_played[]" value="<?php echo htmlspecialchars($player['matches_played']); ?>" 
                              class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-center">
                          </td>
                          <td>
                            <input type="number" name="goals[]" value="<?php echo htmlspecialchars($player['goals']); ?>" 
                              class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-center">
                          </td>
                          <td>
                            <input type="number" name="assists[]" value="<?php echo htmlspecialchars($player['assists']); ?>" 
                              class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-center">
                          </td>
                          <td>
                            <div class="rating-input">
                              <input type="number" step="0.1" min="0" max="10" name="rating[]" value="<?php echo htmlspecialchars($player['rating']); ?>" 
                                class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-center">
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="overflow-x-auto bg-white rounded-lg shadow">
              <table class="min-w-full performance-table">
                <thead class="bg-gray-800 text-white">
                  <tr>
                    <th class="text-left">Player</th>
                    <th class="text-center">Matches</th>
                    <th class="text-center">Goals</th>
                    <th class="text-center">Assists</th>
                    <th class="text-center">Rating</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <?php foreach ($players as $player): ?>
                    <tr class="hover:bg-gray-50">
                      <td class="flex items-center">
                        <i class="fas fa-user-circle text-gray-400 mr-2"></i>
                        <?php echo htmlspecialchars($player['username']); ?>
                        <input type="hidden" name="player_id[]" value="<?php echo $player['player_id']; ?>">
                      </td>
                      <td>
                        <input type="number" name="matches_played[]" value="<?php echo htmlspecialchars($player['matches_played']); ?>" 
                          class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-center">
                      </td>
                      <td>
                        <input type="number" name="goals[]" value="<?php echo htmlspecialchars($player['goals']); ?>" 
                          class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-center">
                      </td>
                      <td>
                        <input type="number" name="assists[]" value="<?php echo htmlspecialchars($player['assists']); ?>" 
                          class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-center">
                      </td>
                      <td>
                        <div class="rating-input">
                          <input type="number" step="0.1" min="0" max="10" name="rating[]" value="<?php echo htmlspecialchars($player['rating']); ?>" 
                            class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all text-center">
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

          <div class="mt-8 flex justify-center">
            <button type="submit" name="update_performance" 
              class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center">
              <i class="fas fa-save mr-2"></i>
              Update Performance
            </button>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
