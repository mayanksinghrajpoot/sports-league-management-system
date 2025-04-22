<?php
// admin_dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
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
    <h1 class="text-3xl font-bold mb-4 text-center">Admin Dashboard</h1>
    <ul class="space-y-4">
      <li>
        <a href="admin_matches.php" class="bg-blue-500 text-white p-3 rounded-lg flex items-center justify-center transition-all duration-200 hover:bg-blue-600">
          <i class="fas fa-futbol mr-2"></i> Manage Matches
        </a>
      </li>
      <li>
        <a href="messaging.php" class="bg-blue-500 text-white p-3 rounded-lg flex items-center justify-center transition-all duration-200 hover:bg-blue-600">
          <i class="fas fa-comments mr-2"></i> Messaging
        </a>
      </li>
      <li>
        <a href="performance.php" class="bg-blue-500 text-white p-3 rounded-lg flex items-center justify-center transition-all duration-200 hover:bg-blue-600">
          <i class="fas fa-chart-line mr-2"></i> Performance Reports
        </a>
      </li>
      <li>
        <a href="logout.php" class="bg-red-500 text-white p-3 rounded-lg flex items-center justify-center transition-all duration-200 hover:bg-red-600">
          <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</body>
</html>
