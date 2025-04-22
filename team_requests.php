<?php
// team_requests.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'team') {
    header("Location: login.php");
    exit();
}
include 'db_config.php';
$user_id = $_SESSION['user_id'];
$msg = "";

// Get the team for the current team user
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

// Handle approval/rejection actions for join requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $player_id = $_POST['player_id'];
    $action = $_POST['action']; // approve or reject
    $status = ($action == 'approve') ? 'approved' : 'rejected';
    
    // Update join request where the team_id matches this team
    $stmt = $conn->prepare("UPDATE players SET join_status = ? WHERE id = ? AND team_id = ?");
    $stmt->bind_param("sii", $status, $player_id, $team_id);
    if ($stmt->execute()) {
        $msg = "Player request has been " . ucfirst($status) . " successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch pending join requests for this team
$stmt = $conn->prepare("SELECT players.id as request_id, users.username, players.sport, players.join_status,
                              players.created_at
                       FROM players 
                       JOIN users ON players.user_id = users.id 
                       WHERE players.team_id = ? AND players.join_status = 'pending'
                       ORDER BY players.created_at DESC");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result_requests = $stmt->get_result();
$requests = $result_requests->fetch_all(MYSQLI_ASSOC);
$request_count = count($requests);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Join Requests - Sports League</title>
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
    .request-card {
      transition: all 0.3s ease;
    }
    .request-card:hover {
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
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold">
            <i class="fas fa-user-plus text-blue-600 mr-2"></i>
            Join Requests
          </h1>
          <p class="text-gray-600 mt-2">
            <i class="fas fa-users mr-2"></i>
            Team: <?php echo htmlspecialchars($team_name); ?>
          </p>
        </div>
        <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full">
          <i class="fas fa-clipboard-list mr-2"></i>
          <?php echo $request_count; ?> Pending Requests
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

      <?php if($request_count > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach($requests as $req): ?>
            <div class="request-card bg-white rounded-lg shadow-md overflow-hidden">
              <div class="bg-gray-800 text-white p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <i class="fas fa-user-circle text-2xl mr-3"></i>
                    <div>
                      <h3 class="font-semibold"><?php echo htmlspecialchars($req['username']); ?></h3>
                      <p class="text-sm text-gray-300">Request #<?php echo $req['request_id']; ?></p>
                    </div>
                  </div>
                  <span class="bg-yellow-500 px-2 py-1 rounded-full text-xs">Pending</span>
                </div>
              </div>
              <div class="p-4">
                <div class="flex items-center text-gray-600 mb-4">
                  <i class="fas fa-running mr-2"></i>
                  <span>Sport: <?php echo htmlspecialchars($req['sport']); ?></span>
                </div>
                <div class="flex items-center text-gray-600 mb-4">
                  <i class="far fa-clock mr-2"></i>
                  <span>Requested: <?php echo date('M d, Y', strtotime($req['created_at'])); ?></span>
                </div>
                <div class="flex space-x-2">
                  <form method="POST" class="flex-1"> 
                    <input type="hidden" name="player_id" value="<?php echo $req['request_id']; ?>">
                    <button type="submit" name="action" value="approve" 
                      class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg transition-colors duration-200 flex items-center justify-center">
                      <i class="fas fa-check mr-2"></i> Approve
                    </button>
                  </form>
                  <form method="POST" class="flex-1"> 
                    <input type="hidden" name="player_id" value="<?php echo $req['request_id']; ?>">
                    <button type="submit" name="action" value="reject" 
                      class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg transition-colors duration-200 flex items-center justify-center">
                      <i class="fas fa-times mr-2"></i> Reject
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-12">
          <div class="text-6xl text-gray-400 mb-4">
            <i class="fas fa-inbox"></i>
          </div>
          <h2 class="text-2xl font-semibold text-gray-600 mb-2">No Pending Requests</h2>
          <p class="text-gray-500">There are no pending join requests for your team at the moment.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
