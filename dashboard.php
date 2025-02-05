<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil total data
$sql_total_pns = "SELECT COUNT(*) as total FROM pns WHERE status='aktif'";
$sql_total_instansi = "SELECT COUNT(*) as total FROM instansi";
$sql_total_kenaikan = "SELECT COUNT(*) as total FROM kenaikan_pangkat WHERE status='diajukan'";

$total_pns = $conn->query($sql_total_pns)->fetch_assoc()['total'];
$total_instansi = $conn->query($sql_total_instansi)->fetch_assoc()['total'];
$total_kenaikan = $conn->query($sql_total_kenaikan)->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - SI-PNS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .menu-item {
            position: relative;
            transition: all 0.3s ease;
            background: linear-gradient(to right, transparent 50%, #4B5563 50%);
            background-size: 200% 100%;
            background-position: 0 0;
        }
        
        .menu-item:hover {
            background-position: -100% 0;
            transform: translateX(10px);
        }
        
        .menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background-color: #60A5FA;
            transform: scaleY(0);
            transition: transform 0.2s ease;
        }
        
        .menu-item:hover::before {
            transform: scaleY(1);
        }
        
        .menu-icon {
            transition: transform 0.3s ease;
        }
        
        .menu-item:hover .menu-icon {
            transform: scale(1.2);
        }
        
        .menu-text {
            position: relative;
            z-index: 1;
        }

        @media (max-width: 768px) {
            .sidebar-mobile {
                position: fixed;
                left: -100%;
                transition: 0.3s;
                z-index: 40;
            }
            
            .sidebar-mobile.active {
                left: 0;
            }
            
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.5);
                z-index: 30;
            }
            
            .overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-gray-800 text-white p-4 sticky top-0 z-50">
        <div class="container mx-auto">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="lg:hidden mr-2">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <img src="assets/kopri.png" alt="Logo" class="h-8 mr-2">
                    <span class="text-xl font-bold">SI-PNS</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="hidden md:inline">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="hidden md:inline ml-2">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div id="overlay" class="overlay"></div>

    <!-- Main Container -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-mobile w-64 bg-gray-800 text-white lg:relative lg:left-0">
            <div class="p-4">
                <nav>
                    <a href="data_pns.php" class="menu-item block py-3 px-4 rounded mb-2 flex items-center">
                        <span class="menu-icon mr-3">
                            <i class="fas fa-users text-blue-400"></i>
                        </span>
                        <span class="menu-text">Data PNS</span>
                    </a>
                    <a href="kenaikan_pangkat.php" class="menu-item block py-3 px-4 rounded mb-2 flex items-center">
                        <span class="menu-icon mr-3">
                            <i class="fas fa-level-up-alt text-green-400"></i>
                        </span>
                        <span class="menu-text">Kenaikan Pangkat</span>
                    </a>
                    <a href="data_instansi.php" class="menu-item block py-3 px-4 rounded mb-2 flex items-center">
                        <span class="menu-icon mr-3">
                            <i class="fas fa-building text-yellow-400"></i>
                        </span>
                        <span class="menu-text">Data Instansi</span>
                    </a>
                    <a href="laporan.php" class="menu-item block py-3 px-4 rounded mb-2 flex items-center">
                        <span class="menu-icon mr-3">
                            <i class="fas fa-file-alt text-purple-400"></i>
                        </span>
                        <span class="menu-text">Laporan</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 min-w-0 overflow-hidden">
            <div class="p-4 md:p-6 lg:p-8">
                <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                    <!-- Total PNS Card -->
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                                <i class="fas fa-users text-blue-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600">Total PNS</h2>
                                <p class="text-2xl font-bold"><?php echo $total_pns; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Instansi Card -->
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                                <i class="fas fa-building text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600">Total Instansi</h2>
                                <p class="text-2xl font-bold"><?php echo $total_instansi; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kenaikan Pangkat Card -->
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-20">
                                <i class="fas fa-level-up-alt text-yellow-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600">Pengajuan Kenaikan Pangkat</h2>
                                <p class="text-2xl font-bold"><?php echo $total_kenaikan; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table and Mobile Cards -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-4 md:p-6">
                        <h2 class="text-xl font-bold mb-4">Data PNS Terbaru</h2>
                    </div>
                    
                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3 text-left">NIP</th>
                                    <th class="px-4 py-3 text-left">Nama</th>
                                    <th class="px-4 py-3 text-left">Golongan</th>
                                    <th class="px-4 py-3 text-left">Instansi</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT p.*, g.nama_golongan, i.nama_instansi 
                                        FROM pns p 
                                        LEFT JOIN golongan g ON p.golongan_id = g.id 
                                        LEFT JOIN instansi i ON p.instansi_id = i.id 
                                        ORDER BY p.dibuat_pada DESC LIMIT 5";
                                $result = $conn->query($sql);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr class='border-b hover:bg-gray-50'>";
                                        echo "<td class='px-4 py-3'>" . htmlspecialchars($row['nip']) . "</td>";
                                        echo "<td class='px-4 py-3'>" . htmlspecialchars($row['nama_lengkap']) . "</td>";
                                        echo "<td class='px-4 py-3'>" . htmlspecialchars($row['nama_golongan']) . "</td>";
                                        echo "<td class='px-4 py-3'>" . htmlspecialchars($row['nama_instansi']) . "</td>";
                                        echo "<td class='px-4 py-3'>";
                                        if($row['status'] == 'aktif') {
                                            echo "<span class='bg-green-100 text-green-800 rounded-full px-3 py-1 text-sm'>Aktif</span>";
                                        } else {
                                            echo "<span class='bg-red-100 text-red-800 rounded-full px-3 py-1 text-sm'>Tidak Aktif</span>";
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='px-4 py-3 text-center'>Tidak ada data PNS</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden">
                        <?php
                        // Reset result pointer
                        $result->data_seek(0);
                        
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                ?>
                                <div class="p-4 border-b last:border-b-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-medium"><?php echo htmlspecialchars($row['nama_lengkap']); ?></h3>
                                        <span class="<?php echo $row['status'] == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> rounded-full px-3 py-1 text-sm">
                                            <?php echo $row['status'] == 'aktif' ? 'Aktif' : 'Tidak Aktif'; ?>
                                        </span>
                                    </div>
                                    <div class="space-y-1 text-sm text-gray-600">
                                        <div class="flex justify-between">
                                            <span class="font-medium">NIP:</span>
                                            <span><?php echo htmlspecialchars($row['nip']); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Golongan:</span>
                                            <span><?php echo htmlspecialchars($row['nama_golongan']); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Instansi:</span>
                                            <span><?php echo htmlspecialchars($row['nama_instansi']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<div class='p-4 text-center text-gray-500'>Tidak ada data PNS</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar Toggle Functionality
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });

       // Close sidebar on window resize if it's open
       window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    </script>
</body>
</html>