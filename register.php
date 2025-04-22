<?php
// register.php
include 'db_config.php';

$msg = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // If admin role is selected, validate the admin code.
    if ($role === 'admin') {
        $adminCodeInput = trim($_POST['admin_code'] ?? '');
        $secureAdminCode = "admin@321"; 
        if (!$secureAdminCode || $adminCodeInput !== $secureAdminCode) {
            $error = "Invalid admin code. You are not authorized to register as an admin.";
        }
    }
    
    // Continue with registration if no error
    if (empty($error)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashedPassword, $role);
        if ($stmt->execute()) {
            $msg = "Registration successful. Please <a class='text-blue-600 hover:text-blue-700 font-semibold' href='login.php'>login</a>.";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - Sports League</title>
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
    .role-card {
      transition: all 0.3s ease;
    }
    .role-card:hover {
      transform: translateY(-5px);
    }
    .role-card.selected {
      border-color: #2563eb;
      background-color: #eff6ff;
    }
  </style>
  <script>
    function selectRole(role) {
      document.getElementById('role').value = role;
      // Remove selected class from all cards
      document.querySelectorAll('.role-card').forEach(card => {
        card.classList.remove('selected');
      });
      // Add selected class to clicked card
      document.querySelector(`[data-role="${role}"]`).classList.add('selected');
      // Toggle admin code field
      document.getElementById('adminCodeField').style.display = 
        role === 'admin' ? 'block' : 'none';
    }
  </script>
</head>
<body class="min-h-screen flex flex-col">
  <!-- Navigation -->
  <nav class="bg-gray-900 bg-opacity-80 text-white py-4">
    <div class="container mx-auto px-6 flex justify-between items-center">
      <a href="index.php" class="text-xl font-bold flex items-center">
        <i class="fas fa-trophy mr-2"></i>
        Sports League
      </a>
      <a href="index.php" class="hover:text-blue-400 transition-colors">
        <i class="fas fa-home mr-1"></i> Home
      </a>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="flex-grow flex justify-center items-center p-6">
    <div class="glass-effect p-8 rounded-2xl shadow-2xl w-full max-w-2xl">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Create Your Account</h2>
        <p class="text-gray-600 mt-2">Join our sports community today</p>
      </div>

      <?php if(isset($msg)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
          <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo $msg; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if(isset($error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
          <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo $error; ?>
          </div>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="space-y-6">
        <div>
          <label class="block text-gray-700 mb-2 font-medium">
            <i class="fas fa-user mr-2"></i>Username
          </label>
          <input 
            type="text" 
            name="username" 
            required 
            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
            placeholder="Choose a username"
          >
        </div>
        
        <div>
          <label class="block text-gray-700 mb-2 font-medium">
            <i class="fas fa-lock mr-2"></i>Password
          </label>
          <input 
            type="password" 
            name="password" 
            required 
            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
            placeholder="Create a strong password"
          >
        </div>

        <div>
          <label class="block text-gray-700 mb-4 font-medium">
            <i class="fas fa-user-tag mr-2"></i>Select Your Role
          </label>
          <input type="hidden" name="role" id="role" value="player">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div 
              class="role-card selected cursor-pointer border-2 rounded-lg p-4 text-center"
              data-role="player"
              onclick="selectRole('player')"
            >
              <i class="fas fa-running text-3xl text-blue-600 mb-2"></i>
              <h3 class="font-semibold">Player</h3>
              <p class="text-sm text-gray-600">Join as an athlete</p>
            </div>
            <div 
              class="role-card cursor-pointer border-2 rounded-lg p-4 text-center"
              data-role="team"
              onclick="selectRole('team')"
            >
              <i class="fas fa-users text-3xl text-green-600 mb-2"></i>
              <h3 class="font-semibold">Team</h3>
              <p class="text-sm text-gray-600">Register your team</p>
            </div>
            <div 
              class="role-card cursor-pointer border-2 rounded-lg p-4 text-center"
              data-role="admin"
              onclick="selectRole('admin')"
            >
              <i class="fas fa-shield-alt text-3xl text-purple-600 mb-2"></i>
              <h3 class="font-semibold">Admin</h3>
              <p class="text-sm text-gray-600">Administrative access</p>
            </div>
          </div>
        </div>
        
        <!-- Hidden admin code field -->
        <div id="adminCodeField" style="display:none;">
          <label class="block text-gray-700 mb-2 font-medium">
            <i class="fas fa-key mr-2"></i>Admin Code
          </label>
          <input 
            type="text" 
            name="admin_code" 
            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
            placeholder="Enter admin access code"
          >
        </div>
        
        <button 
          type="submit" 
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 flex items-center justify-center"
        >
          <i class="fas fa-user-plus mr-2"></i>
          Create Account
        </button>
      </form>

      <div class="mt-6 text-center">
        <p class="text-gray-600">Already have an account? 
          <a href="login.php" class="text-blue-600 hover:text-blue-700 font-semibold">
            Sign In
          </a>
        </p>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-200">
        <p class="text-center text-gray-600 mb-4">Or sign up with</p>
        <div class="flex justify-center space-x-4">
          <button class="flex items-center justify-center w-12 h-12 rounded-full border-2 border-gray-300 hover:border-gray-400 transition-all">
            <i class="fab fa-google text-xl text-gray-600"></i>
          </button>
          <button class="flex items-center justify-center w-12 h-12 rounded-full border-2 border-gray-300 hover:border-gray-400 transition-all">
            <i class="fab fa-facebook-f text-xl text-gray-600"></i>
          </button>
          <button class="flex items-center justify-center w-12 h-12 rounded-full border-2 border-gray-300 hover:border-gray-400 transition-all">
            <i class="fab fa-twitter text-xl text-gray-600"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-gray-900 bg-opacity-80 text-white py-4">
    <div class="container mx-auto px-6 text-center">
      <p class="text-sm">© <?php echo date("Y"); ?> Sports League. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
