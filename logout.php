<?php
// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua data session
$_SESSION = array();
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Clear semua cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-3600, '/');
    }
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Set security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    
    <!-- Prevent caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <style>
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }
    </style>
    
    <script>
        // Fungsi untuk deteksi mobile browser
        function isMobileBrowser() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // Redirect langsung untuk mobile
        if(isMobileBrowser()) {
            window.location.replace('login.php');
        }

        // Disable back functionality
        window.history.pushState(null, '', 'login.php');
        window.history.pushState(null, '', 'login.php');
        window.history.pushState(null, '', 'login.php');
        
        window.onpopstate = function(event) {
            window.location.replace('login.php');
        };
        
        // Additional back prevention
        window.addEventListener('load', function() {
            // Clear storages
            localStorage.clear();
            sessionStorage.clear();
            
            history.pushState(null, '', 'login.php');
            
            // Force redirect setelah clear storage
            setTimeout(function() {
                window.location.replace('login.php');
            }, 100);
        });
        
        // Prevent default browser behaviors
        window.addEventListener('beforeunload', function(e) {
            localStorage.clear();
            sessionStorage.clear();
        });
        
        // Handle Android back button
        document.addEventListener('deviceready', function() {
            document.addEventListener('backbutton', function(e) {
                e.preventDefault();
                window.location.replace('login.php');
            }, false);
        }, false);
        
        // Disable all possible navigation methods
        window.addEventListener('popstate', function(e) {
            window.location.replace('login.php');
        });
        
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                window.location.replace('login.php');
            }
        });
        
        // Force reload on focus
        window.addEventListener('focus', function() {
            window.location.replace('login.php');
        });
    </script>
</head>
<body onload="window.location.replace('login.php');">
    <div class="loading">
        <p>Logging out...</p>
    </div>
    <script>
        // Final redirect
        setTimeout(function() {
            window.location.replace('login.php');
        }, 100);
    </script>
</body>
</html>