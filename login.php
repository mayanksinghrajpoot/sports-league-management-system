<?php
// login.php
session_start();
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $role);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        if($role === 'admin') {
            header("Location: admin_dashboard.php");
            exit();
        } 
        else if($role === 'player') {
          header("Location: player_dashboard.php");
          exit();
        }
        else {
            header("Location: team_dashboard.php");
            exit();
        }
    } else {
        $error = "Invalid username or password.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Sports League</title>
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
  </style>
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
    <div class="glass-effect p-8 rounded-2xl shadow-2xl w-full max-w-md">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Welcome Back!</h2>
        <p class="text-gray-600 mt-2">Sign in to access your account</p>
      </div>

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
            placeholder="Enter your username"
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
            placeholder="Enter your password"
          >
        </div>
        
        <button 
          type="submit" 
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 flex items-center justify-center"
        >
          <i class="fas fa-sign-in-alt mr-2"></i>
          Sign In
        </button>
      </form>

      <div class="mt-6 text-center">
        <p class="text-gray-600">Don't have an account? 
          <a href="register.php" class="text-blue-600 hover:text-blue-700 font-semibold">
            Sign Up
          </a>
        </p>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-200">
        <p class="text-center text-gray-600 mb-4">Or sign in with</p>
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
