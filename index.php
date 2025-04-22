<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sports League</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
    @keyframes slide {
      0% { transform: translateX(0%); }
      25% { transform: translateX(-100%); }
      50% { transform: translateX(-200%); }
      75% { transform: translateX(-300%); }
      100% { transform: translateX(0%); }
    }
    .carousel-track {
      animation: slide 20s infinite;
    }
    .stat-card:hover {
      transform: translateY(-5px);
      transition: all 0.3s ease;
    }
  </style>
</head>
<body class="bg-gray-50">
  <!-- Header / Hero Section -->
  <header class="relative">
    <div class="overflow-hidden relative h-screen max-h-[600px]">
      <div class="flex carousel-track h-full">
        <img src="https://images.pexels.com/photos/46798/the-ball-stadion-football-the-pitch-46798.jpeg" alt="Football Stadium" class="w-full object-cover">
        <img src="https://images.pexels.com/photos/248547/pexels-photo-248547.jpeg" alt="Basketball" class="w-full object-cover">
        <img src="https://images.pexels.com/photos/209977/pexels-photo-209977.jpeg" alt="Tennis Court" class="w-full object-cover">
        <img src="https://images.pexels.com/photos/163452/basketball-dunk-blue-game-163452.jpeg" alt="Sports Action" class="w-full object-cover">
      </div>
      <!-- Overlay -->
      <div class="absolute inset-0 bg-gradient-to-r from-black via-black/70 to-transparent flex items-center">
        <div class="text-white max-w-4xl mx-auto px-6">
          <h1 class="text-5xl md:text-6xl font-bold mb-4 leading-tight">Elevate Your Game in Our Ultimate Sports League</h1>
          <p class="text-xl mb-8 text-gray-300">Join thousands of athletes competing at the highest level. Your journey to greatness starts here!</p>
          <div class="space-x-4">
            <a href="login.php" class="bg-blue-600 hover:bg-blue-700 px-8 py-4 rounded-full text-lg font-semibold inline-flex items-center transition-all duration-300">
              <i class="fas fa-sign-in-alt mr-2"></i> Login
            </a>
            <a href="register.php" class="bg-green-600 hover:bg-green-700 px-8 py-4 rounded-full text-lg font-semibold inline-flex items-center transition-all duration-300">
              <i class="fas fa-user-plus mr-2"></i> Register
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Statistics Section -->
  <section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl text-white text-center">
          <i class="fas fa-users text-4xl mb-4"></i>
          <h3 class="text-3xl font-bold mb-2">1000+</h3>
          <p>Active Players</p>
        </div>
        <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl text-white text-center">
          <i class="fas fa-trophy text-4xl mb-4"></i>
          <h3 class="text-3xl font-bold mb-2">50+</h3>
          <p>Tournaments</p>
        </div>
        <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl text-white text-center">
          <i class="fas fa-medal text-4xl mb-4"></i>
          <h3 class="text-3xl font-bold mb-2">200+</h3>
          <p>Teams</p>
        </div>
        <div class="stat-card bg-gradient-to-br from-red-500 to-red-600 p-6 rounded-xl text-white text-center">
          <i class="fas fa-map-marker-alt text-4xl mb-4"></i>
          <h3 class="text-3xl font-bold mb-2">20+</h3>
          <p>Venues</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Sports Categories -->
  <section class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-3xl font-bold text-center mb-12">Popular Sports Categories</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
          <img src="https://images.pexels.com/photos/47730/the-ball-stadion-football-the-pitch-47730.jpeg" alt="Football" class="w-full h-48 object-cover">
          <div class="p-6">
            <h3 class="text-xl font-bold mb-2">Football</h3>
            <p class="text-gray-600">Join competitive football leagues and tournaments.</p>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
          <img src="https://images.pexels.com/photos/358042/pexels-photo-358042.jpeg" alt="Basketball" class="w-full h-48 object-cover">
          <div class="p-6">
            <h3 class="text-xl font-bold mb-2">Basketball</h3>
            <p class="text-gray-600">Compete in exciting basketball championships.</p>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
          <img src="https://images.pexels.com/photos/209977/pexels-photo-209977.jpeg" alt="Tennis" class="w-full h-48 object-cover">
          <div class="p-6">
            <h3 class="text-xl font-bold mb-2">Tennis</h3>
            <p class="text-gray-600">Participate in professional tennis tournaments.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Reviews Section -->
  <section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-3xl font-bold text-center mb-12">What Our Members Say</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Review Card 1 -->
        <div class="bg-gray-50 p-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
          <div class="text-yellow-400 mb-4">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
          </div>
          <p class="text-gray-700 mb-6">"An amazing experience! The community is very supportive and the matches are always exciting. I've improved my game significantly."</p>
          <div class="flex items-center">
            <img src="https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg" alt="Reviewer" class="w-12 h-12 rounded-full mr-4 object-cover">
            <div>
              <p class="font-semibold">Alex Johnson</p>
              <p class="text-sm text-gray-500">Football Player</p>
            </div>
          </div>
        </div>
        <!-- Review Card 2 -->
        <div class="bg-gray-50 p-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
          <div class="text-yellow-400 mb-4">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
          </div>
          <p class="text-gray-700 mb-6">"The platform is so user-friendly and the atmosphere is electric. The tournaments are well-organized and competitive!"</p>
          <div class="flex items-center">
            <img src="https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg" alt="Reviewer" class="w-12 h-12 rounded-full mr-4 object-cover">
            <div>
              <p class="font-semibold">Maria Gonzalez</p>
              <p class="text-sm text-gray-500">Basketball Player</p>
            </div>
          </div>
        </div>
        <!-- Review Card 3 -->
        <div class="bg-gray-50 p-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
          <div class="text-yellow-400 mb-4">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
          </div>
          <p class="text-gray-700 mb-6">"A top-notch league that offers both competitive play and a fun community experience. The coaching is excellent!"</p>
          <div class="flex items-center">
            <img src="https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg" alt="Reviewer" class="w-12 h-12 rounded-full mr-4 object-cover">
            <div>
              <p class="font-semibold">Sarah Lee</p>
              <p class="text-sm text-gray-500">Tennis Player</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer Section -->
  <footer class="bg-gray-900 text-gray-300 py-12">
    <div class="max-w-6xl mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <div>
          <h3 class="text-xl font-bold text-white mb-4">Sports League</h3>
          <p class="text-sm">Empowering athletes to reach their full potential through competitive sports and community engagement.</p>
        </div>
        <div>
          <h4 class="text-lg font-semibold text-white mb-4">Quick Links</h4>
          <ul class="space-y-2">
            <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Tournaments</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Schedule</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-lg font-semibold text-white mb-4">Sports</h4>
          <ul class="space-y-2">
            <li><a href="#" class="hover:text-white transition-colors">Football</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Basketball</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Tennis</a></li>
            <li><a href="#" class="hover:text-white transition-colors">Cricket</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-lg font-semibold text-white mb-4">Connect With Us</h4>
          <div class="flex space-x-4">
            <a href="#" class="text-2xl hover:text-white transition-colors"><i class="fab fa-facebook"></i></a>
            <a href="#" class="text-2xl hover:text-white transition-colors"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-2xl hover:text-white transition-colors"><i class="fab fa-instagram"></i></a>
            <a href="#" class="text-2xl hover:text-white transition-colors"><i class="fab fa-youtube"></i></a>
          </div>
        </div>
      </div>
      <div class="border-t border-gray-800 pt-8 text-center">
        <p class="text-sm">© <?php echo date("Y"); ?> Sports League. All rights reserved.</p>
      </div>
    </div>
  </footer>
</body>
</html>
