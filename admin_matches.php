<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_config.php';

$msg = "";
$error = "";

// Fetch teams for dropdown selection and store in an array
$teamsQuery = "SELECT id, team_name, sport, min_players FROM teams ORDER BY team_name";
$result_teams = $conn->query($teamsQuery);
$teamsArray = array();
if ($result_teams && $result_teams->num_rows > 0) {
    while ($team = $result_teams->fetch_assoc()) {
        $teamsArray[] = $team;
    }
}

// Handle match scheduling
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule_match'])) {
    $team1_id = $_POST['team1_id'];
    $team2_id = $_POST['team2_id'];
    $match_date = $_POST['match_date'];

    if ($team1_id == $team2_id) {
        $error = "Team 1 and Team 2 cannot be the same.";
    } else {
        // Fetch sports of both teams
        $stmt = $conn->prepare("SELECT sport FROM teams WHERE id = ?");
        $stmt->bind_param("i", $team1_id);
        $stmt->execute();
        $stmt->bind_result($team1_sport);
        $stmt->fetch();
        $stmt->close();

        if ($team1_sport !== $team2_sport) {
            $error = "Both teams must belong to the same sport.";
        } else {
            // Check that both teams meet the minimum approved players requirement
            $checkTeamPlayers = function($team_id) use ($conn) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM players WHERE team_id = ? AND join_status = 'approved'");
                $stmt->bind_param("i", $team_id);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();
                return $count;
            };

            $getTeamMin = function($team_id) use ($conn) {
                $stmt = $conn->prepare("SELECT min_players FROM teams WHERE id = ?");
                $stmt->bind_param("i", $team_id);
                $stmt->execute();
                $stmt->bind_result($min);
                $stmt->fetch();
                $stmt->close();
                return $min;
            };

            $team1_players = $checkTeamPlayers($team1_id);
            $team2_players = $checkTeamPlayers($team2_id);
            $team1_min = $getTeamMin($team1_id);
            $team2_min = $getTeamMin($team2_id);

            if ($team1_players < $team1_min || $team2_players < $team2_min) {
                $error = "Both teams must meet their minimum player requirements. ";
                $error .= "Team 1 has $team1_players/$team1_min approved players, and Team 2 has $team2_players/$team2_min approved players.";
            } else {
                // Time validation
                $match_datetime = strtotime($match_date);
                $current_datetime = time();

                if ($match_datetime <= $current_datetime) {
                    $error = "Match time must be in the future. Please select a later time today or a future date.";
                } else {
                    // Check time conflict with existing matches
                    $stmt = $conn->prepare("SELECT match_date FROM matches WHERE ABS(TIMESTAMPDIFF(MINUTE, match_date, ?)) < 60");
                    $stmt->bind_param("s", $match_date);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $error = "Time conflict: another match is already scheduled within 1 hour of the selected time.";
                    } else {
                        // Insert match record
                        $stmt->close();
                        $stmt = $conn->prepare("INSERT INTO matches (team1_id, team2_id, match_date) VALUES (?, ?, ?)");
                        $stmt->bind_param("iis", $team1_id, $team2_id, $match_date);
                        if ($stmt->execute()) {
                            $msg = "Match scheduled successfully.";
                        } else {
                            $error = "Error: " . $stmt->error;
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Handle result update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_result'])) {
    $match_id = $_POST['match_id'];
    $result_text = $_POST['result'];

    $stmt = $conn->prepare("UPDATE matches SET result = ? WHERE id = ?");
    $stmt->bind_param("si", $result_text, $match_id);
    if ($stmt->execute()) {
        $msg = "Match result updated successfully.";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch matches with team details (joining teams table twice)
$matchesQuery = "SELECT m.id, m.match_date, m.result,
                        t1.team_name AS team1_name, t1.min_players AS team1_min,
                        t2.team_name AS team2_name, t2.min_players AS team2_min
                 FROM matches m
                 JOIN teams t1 ON m.team1_id = t1.id
                 JOIN teams t2 ON m.team2_id = t2.id
                 ORDER BY m.match_date DESC";
$result_matches = $conn->query($matchesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Manage Matches</title>
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
    .match-card {
      transition: all 0.3s ease;
    }
    .match-card:hover {
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
        <a href="admin_dashboard.php" class="hover:text-blue-400 transition-colors">
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
          <i class="fas fa-futbol text-blue-600 mr-2"></i>
          Manage Matches
        </h1>
      </div>

      <?php if ($msg): ?>
        <div class="mb-6 bg-green-100 text-green-700 p-4 rounded-lg">
          <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($msg); ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="mb-6 bg-red-100 text-red-700 p-4 rounded-lg">
          <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Schedule New Match Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-calendar-plus text-blue-600 mr-2"></i>
            Schedule New Match
          </h2>
          <form method="POST" action="" class="space-y-4">
            <div>
              <label class="block mb-2 font-medium">
                <i class="fas fa-users mr-2"></i>Team 1
              </label>
              <select name="team1_id" required 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all">
                <option value="">Select Team 1</option>
                <?php foreach ($teamsArray as $team): ?>
                  <option value="<?php echo $team['id']; ?>">
                    <?php echo htmlspecialchars($team['team_name']); ?> (<?php echo htmlspecialchars($team['sport']); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block mb-2 font-medium">
                <i class="fas fa-users mr-2"></i>Team 2
              </label>
              <select name="team2_id" required 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all">
                <option value="">Select Team 2</option>
                <?php foreach ($teamsArray as $team): ?>
                  <option value="<?php echo $team['id']; ?>">
                    <?php echo htmlspecialchars($team['team_name']); ?> (<?php echo htmlspecialchars($team['sport']); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block mb-2 font-medium">
                <i class="fas fa-clock mr-2"></i>Match Date And Time
              </label>
              <input type="datetime-local" name="match_date" required 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all">
            </div>

            <button type="submit" name="schedule_match" 
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 flex items-center justify-center">
              <i class="fas fa-calendar-plus mr-2"></i>
              Schedule Match
            </button>
          </form>
        </div>

        <!-- Update Match Result Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-edit text-blue-600 mr-2"></i>
            Update Match Result
          </h2>
          <form method="POST" action="" class="space-y-4">
            <div>
              <label class="block mb-2 font-medium">
                <i class="fas fa-hashtag mr-2"></i>Match ID
              </label>
              <input type="number" name="match_id" required 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
                placeholder="Enter match ID">
            </div>

            <div>
              <label class="block mb-2 font-medium">
                <i class="fas fa-star mr-2"></i>Result
              </label>
              <input type="text" name="result" required 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
                placeholder="Enter match result (e.g., '3-2')">
            </div>

            <button type="submit" name="update_result" 
              class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 flex items-center justify-center">
              <i class="fas fa-save mr-2"></i>
              Update Result
            </button>
          </form>
        </div>
      </div>

      <!-- All Matches Section -->
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-6 flex items-center">
          <i class="fas fa-list text-blue-600 mr-2"></i>
          All Matches
        </h2>
        
        <?php if($result_matches && $result_matches->num_rows > 0): ?>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-800 text-white">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                    <i class="fas fa-hashtag mr-2"></i>ID
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                    <i class="fas fa-users mr-2"></i>Teams
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                    <i class="fas fa-calendar mr-2"></i>Date
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                    <i class="fas fa-star mr-2"></i>Result
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                    <i class="fas fa-info-circle mr-2"></i>Status
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php while($match = $result_matches->fetch_assoc()): 
                  $match_time = strtotime($match['match_date']);
                  $current_time = time();
                  $status = '';
                  $status_class = '';
                  
                  if ($match['result']) {
                    $status = 'Completed';
                    $status_class = 'bg-green-100 text-green-800';
                  } elseif ($match_time < $current_time) {
                    $status = 'In Progress';
                    $status_class = 'bg-yellow-100 text-yellow-800';
                  } else {
                    $status = 'Upcoming';
                    $status_class = 'bg-blue-100 text-blue-800';
                  }
                ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      #<?php echo htmlspecialchars($match['id']); ?>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex flex-col">
                        <span class="font-medium"><?php echo htmlspecialchars($match['team1_name']); ?></span>
                        <span class="text-gray-500">vs</span>
                        <span class="font-medium"><?php echo htmlspecialchars($match['team2_name']); ?></span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <?php echo date('M d, Y - h:i A', strtotime($match['match_date'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <?php echo $match['result'] ? htmlspecialchars($match['result']) : 'Pending'; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $status_class; ?>">
                        <?php echo $status; ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-8">
            <div class="text-6xl text-gray-400 mb-4">
              <i class="fas fa-calendar-times"></i>
            </div>
            <p class="text-xl text-gray-600">No matches scheduled yet</p>
            <p class="text-gray-500">Use the form above to schedule your first match!</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php $conn->close(); ?>
</body>
</html>
