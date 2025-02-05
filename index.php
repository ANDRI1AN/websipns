<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi PNS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 h-screen">
    <?php
    require_once 'config/database.php';
    
    // Ambil total data
    $sql_total_pns = "SELECT COUNT(*) as total FROM pns WHERE status='aktif'";
    $sql_total_instansi = "SELECT COUNT(*) as total FROM instansi";
    
    $total_pns = $conn->query($sql_total_pns)->fetch_assoc()['total'];
    $total_instansi = $conn->query($sql_total_instansi)->fetch_assoc()['total'];
    ?>
    <nav class="bg-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <img src="assets/kopri.png" alt="Logo" class="h-8 mr-2">
                <h1 class="text-white text-xl font-bold">SI-PNS</h1>
            </div>
            <div>
                <a href="tentang.php" class="text-white mx-2 hover:text-yellow-400 transition duration-300">Tentang</a>
                <a href="login.php" class="text-white mx-2 hover:text-yellow-400 transition duration-300">Login</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 h-[calc(100vh-4rem)] flex items-center">
        <div class="flex flex-wrap items-center">
            <div class="w-full md:w-1/2">
                <img src="assets/pnspage.png" alt="Hero Image" class="rounded-lg shadow-xl">
            </div>
            <div class="w-full md:w-1/2 md:pl-12">
                <h2 class="text-yellow-400 text-5xl font-bold mb-2">Hello,</h2>
                <h3 class="text-yellow-400 text-4xl font-bold mb-6">Selamat Datang</h3>
                
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-800 rounded-lg p-6 text-center">
                        <div class="text-yellow-400 text-4xl font-bold mb-2"><?php echo $total_pns; ?></div>
                        <div class="text-white text-sm">Total PNS</div>
                    </div>
                    
                    <div class="bg-gray-800 rounded-lg p-6 text-center">
                        <div class="text-yellow-400 text-4xl font-bold mb-2"><?php echo $total_instansi; ?></div>
                        <div class="text-white text-sm">Instansi</div>
                    </div>
                </div>
                <p class="text-white mb-6">
                    Selamat datang di SI-PNS, kelola data pegawai negeri sipil dengan mudah dan efisien!
                </p>
                <a href="login.php" class="bg-yellow-400 text-gray-900 px-8 py-3 rounded-lg font-bold hover:bg-yellow-500 transition duration-300">
                    Masuk
                </a>
            </div>
        </div>
    </div>
</body>
</html>