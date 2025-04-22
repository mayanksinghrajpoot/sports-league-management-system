<?php
// match_schedule.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_config.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$matches = [];

if ($role == 'admin') {
    // Admin sees all matches.
    $query = "SELECT m.id, m.match_date, m.result,
                     t1.team_name AS team1_name, t2.team_name AS team2_name
              FROM matches m
              JOIN teams t1 ON m.team1_id = t1.id
              JOIN teams t2 ON m.team2_id = t2.id
              ORDER BY m.match_date DESC";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
} elseif ($role == 'team') {
    // Team user: get the team id based on the owner_id
    $stmt = $conn->prepare("SELECT id FROM teams WHERE owner_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($team_id);
    if ($stmt->fetch()) {
        $stmt->close();
        // Fetch matches where this team is involved.
        $stmt = $conn->prepare("SELECT m.id, m.match_date, m.result,
                                       t1.team_name AS team1_name, t2.team_name AS team2_name
                                FROM matches m
                                JOIN teams t1 ON m.team1_id = t1.id
                                JOIN teams t2 ON m.team2_id = t2.id
                                WHERE m.team1_id = ? OR m.team2_id = ?
                                ORDER BY m.match_date DESC");
        $stmt->bind_param("ii", $team_id, $team_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $matches[] = $row;
        }
        $stmt->close();
    } else {
        $stmt->close();
    }
} elseif ($role == 'player') {
    // Player: get the approved team join record.
    $stmt = $conn->prepare("SELECT team_id FROM players WHERE user_id = ? AND join_status = 'approved'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($team_id);
    if ($stmt->fetch()) {
        $stmt->close();
        // Fetch matches where player's team is involved.
        $stmt = $conn->prepare("SELECT m.id, m.match_date, m.result,
                                       t1.team_name AS team1_name, t2.team_name AS team2_name
                                FROM matches m
                                JOIN teams t1 ON m.team1_id = t1.id
                                JOIN teams t2 ON m.team2_id = t2.id
                                WHERE m.team1_id = ? OR m.team2_id = ?
                                ORDER BY m.match_date DESC");
        $stmt->bind_param("ii", $team_id, $team_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $matches[] = $row;
        }
        $stmt->close();
    } else {
        $stmt->close();
    }
}

$conn->close();

// Helper function to get status badge class
function getStatusBadgeClass($result) {
    if (empty($result)) return 'bg-yellow-100 text-yellow-800'; // Pending
    if (strpos($result, 'vs') !== false) return 'bg-blue-100 text-blue-800'; // Upcoming
    return 'bg-green-100 text-green-800'; // Completed
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Match Schedule</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    .match-row {
      transition: all 0.3s ease;
    }
    .match-row:hover {
      transform: translateY(-2px);
      background-color: #f8fafc;
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
    <div class="glass-effect p-8 rounded-xl shadow-xl">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">
          <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
          Match Schedule
        </h1>
        <div class="text-gray-600">
          <i class="fas fa-user-circle mr-2"></i>
          Logged in as: <?php echo ucfirst($role); ?>
        </div>
      </div>

      <?php if (count($matches) > 0): ?>
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white rounded-lg overflow-hidden">
            <thead class="bg-gray-800 text-white">
              <tr>
                <th class="py-3 px-4 text-left">
                  <i class="fas fa-hashtag mr-2"></i>Match ID
                </th>
                <th class="py-3 px-4 text-left">
                  <i class="fas fa-users mr-2"></i>Teams
                </th>
                <th class="py-3 px-4 text-left">
                  <i class="far fa-calendar mr-2"></i>Date
                </th>
                <th class="py-3 px-4 text-left">
                  <i class="fas fa-star mr-2"></i>Result
                </th>
                <th class="py-3 px-4 text-left">
                  <i class="fas fa-info-circle mr-2"></i>Status
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($matches as $match): ?>
                <tr class="match-row">
                  <td class="py-4 px-4">
                    #<?php echo htmlspecialchars($match['id']); ?>
                  </td>
                  <td class="py-4 px-4">
                    <div class="flex items-center">
                      <span class="font-medium"><?php echo htmlspecialchars($match['team1_name']); ?></span>
                      <span class="mx-2 text-gray-500">vs</span>
                      <span class="font-medium"><?php echo htmlspecialchars($match['team2_name']); ?></span>
                    </div>
                  </td>
                  <td class="py-4 px-4">
                    <?php 
                      $date = new DateTime($match['match_date']);
                      echo $date->format('M d, Y - h:i A');
                    ?>
                  </td>
                  <td class="py-4 px-4">
                    <?php echo $match['result'] ? htmlspecialchars($match['result']) : 'Pending'; ?>
                  </td>
                  <td class="py-4 px-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo getStatusBadgeClass($match['result']); ?>">
                      <?php 
                        if (empty($match['result'])) echo 'Pending';
                        elseif (strpos($match['result'], 'vs') !== false) echo 'Upcoming';
                        else echo 'Completed';
                      ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-8">
          <div class="text-6xl text-gray-400 mb-4">
            <i class="fas fa-calendar-times"></i>
          </div>
          <p class="text-xl text-gray-600">No matches scheduled at the moment.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
