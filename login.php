<?php
session_start();
require_once 'config/database.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Set security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Cek jika user sudah login
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Cek jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input tidak boleh kosong
    if(empty($_POST['username']) || empty($_POST['password'])) {
        $error = "Username dan Password harus diisi!";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $sql = "SELECT * FROM pengguna WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    
                    // Redirect ke dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Password yang anda masukan salah!";
                }
            } else {
                $error = "Username tidak ditemukan!";
            }
            $stmt->close();
        } else {
            $error = "Terjadi kesalahan pada sistem";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - SI-PNS</title>
    
    <!-- Prevent caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        footer {
            flex-shrink: 0;
        }
    </style>

    <script type="text/javascript">
        // Disable back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
        
        // Additional back prevention
        window.onload = function() {
            if(typeof history.pushState === "function") {
                history.pushState("jibberish", null, null);
                window.onpopstate = function () {
                    history.pushState('newjibberish', null, null);
                };
            }
            
            // Clear storages
            localStorage.clear();
            sessionStorage.clear();
        }
        
        // Disable back/forward buttons
        window.location.hash="no-back-button";
        window.location.hash="Again-No-back-button"; 
        window.onhashchange=function(){
            window.location.hash="no-back-button";
        }
        
        // Disable right click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        
        // Disable F5 and Ctrl+R
        document.onkeydown = function(e) {
            if (e.keyCode === 116 || (e.ctrlKey && e.keyCode === 82)) {
                e.preventDefault();
            }
        };
        
        function disableBack() {
            window.history.forward();
        }
        
        setTimeout("disableBack()", 0);
        window.onunload = function() { null };

        // Deteksi mobile browser
        function isMobileBrowser() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // Khusus handling untuk mobile
        if(isMobileBrowser()) {
            window.addEventListener('load', function() {
                // Clear history
                window.history.pushState(null, '', 'login.php');
                window.history.pushState(null, '', 'login.php');
                
                // Prevent back navigation
                window.addEventListener('popstate', function(e) {
                    window.history.pushState(null, '', 'login.php');
                });
            });
        }

        // Disable back button untuk semua browser
        window.addEventListener('load', function() {
            history.pushState(null, '', 'login.php');
            
            window.addEventListener('popstate', function(e) {
                history.pushState(null, '', 'login.php');
            });
        });
        
        // Handle page visibility
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') {
                localStorage.clear();
                sessionStorage.clear();
            }
        });
        
        // Handle page cache
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                window.location.reload();
            }
        });
    </script>
</head>
<body class="bg-gray-900">
    <!-- Navbar -->
    <nav class="bg-gray-800 p-4 w-full">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <img src="assets/kopri.png" alt="Logo" class="h-8 mr-2">
                    <h1 class="text-white text-xl font-bold">SI-PNS</h1>
                </div>
                <a href="index.php" class="text-white hover:text-yellow-400 transition duration-300">Beranda</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-6 md:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-center mb-6">MASUK MENGGUNAKAN AKUN ADMIN</h2>
            
            <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form name="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" 
                  onsubmit="return validateForm()" class="space-y-4">
                <div>
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" id="username" name="username" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div>
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>

                <div class="flex flex-col sm:flex-row gap-4 mt-6">
                    <button type="submit" 
                            class="w-full sm:flex-1 bg-yellow-400 text-gray-900 py-3 rounded-lg font-bold hover:bg-yellow-500 transition duration-300">
                        Masuk
                    </button>
                    <button type="reset" 
                            class="w-full sm:flex-1 bg-red-500 text-white py-3 rounded-lg font-bold hover:bg-red-600 transition duration-300">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-4 mt-auto">
        <p>ULAT PUCUK © 2024</p>
    </footer>

    <script>
        function validateForm() {
            var username = document.forms["loginForm"]["username"].value;
            var password = document.forms["loginForm"]["password"].value;
            
            if (username == "") {
                alert("Username harus diisi!");
                return false;
            }
            if (password == "") {
                alert("Password harus diisi!");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>