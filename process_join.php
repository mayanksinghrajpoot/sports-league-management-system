<?php
// process_join.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'player') {
    header("Location: login.php");
    exit();
}

include 'db_config.php';

$user_id = $_SESSION['user_id'];
$error = null;
$success = false;
$team_name = '';

// Check that team_id is provided
if (!isset($_GET['team_id'])) {
    $error = "No team selected.";
} else {
    $team_id = intval($_GET['team_id']);

    // Get team details first
    $stmt = $conn->prepare("SELECT team_name, sport FROM teams WHERE id = ?");
    if (!$stmt) {
        $error = "Database error: " . $conn->error;
    } else {
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $team = $result->fetch_assoc();
        if (!$team) {
            $error = "Invalid team selected.";
        } else {
            $team_name = $team['team_name'];
            $sport = $team['sport'];
        }
        $stmt->close();
    }

    if (!$error) {
        // Check if the player already has any join record
        $stmt = $conn->prepare("SELECT id, team_id, join_status FROM players WHERE user_id = ? AND (join_status = 'pending' OR join_status = 'approved')");
        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $player_status = $result->fetch_assoc();
                if ($player_status['join_status'] == 'approved') {
                    $error = "You are already a member of a team.";
                } else {
                    $error = "You already have a pending join request.";
                }
            }
            $stmt->close();
        }
    }

    if (!$error) {
        // Insert join request
        $stmt = $conn->prepare("INSERT INTO players (user_id, sport, team_id, join_status) VALUES (?, ?, ?, 'pending')");
        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("isi", $user_id, $sport, $team_id);
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Error sending join request: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();

// After 3 seconds, redirect to dashboard
$redirect_url = $success 
    ? "player_dashboard.php?msg=" . urlencode("Join request sent successfully. Please wait for team approval.")
    : "join_team.php?msg=" . urlencode($error);
header("refresh:3;url=$redirect_url");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Join Request - Sports League</title>
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
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100 p-6">
    <div class="glass-effect p-8 rounded-xl shadow-xl max-w-md w-full">
        <?php if ($success): ?>
            <div class="text-center">
                <div class="text-green-500 text-6xl mb-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="text-2xl font-bold mb-4">Request Sent Successfully!</h1>
                <p class="text-gray-600 mb-4">
                    Your request to join <span class="font-semibold"><?php echo htmlspecialchars($team_name); ?></span> has been sent.
                </p>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Redirecting to dashboard...
                </div>
            </div>
        <?php else: ?>
            <div class="text-center">
                <div class="text-red-500 text-6xl mb-4">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h1 class="text-2xl font-bold mb-4">Request Failed</h1>
                <p class="text-gray-600 mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </p>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Redirecting back...
                </div>
            </div>
        <?php endif; ?>

        <!-- Loading indicator -->
        <div class="mt-6 flex justify-center">
            <div class="spinner text-blue-500 text-2xl">
                <i class="fas fa-circle-notch"></i>
            </div>
        </div>

        <!-- Manual redirect link -->
        <div class="mt-4 text-center">
            <a href="<?php echo $redirect_url; ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                Click here if you're not redirected automatically
            </a>
        </div>
    </div>
</body>
</html>
