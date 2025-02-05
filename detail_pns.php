<?php
ob_start();
session_start();
require_once 'config/database.php';
require_once 'includes/upload_handler.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek ID
if(!isset($_GET['id'])) {
    header("Location: data_pns.php");
    exit();
}

$id = $_GET['id'];

// Ambil data lengkap PNS
$sql = "SELECT p.*, g.nama_golongan, g.pangkat, j.nama_jabatan, i.nama_instansi 
        FROM pns p 
        LEFT JOIN golongan g ON p.golongan_id = g.id 
        LEFT JOIN jabatan j ON p.jabatan_id = j.id 
        LEFT JOIN instansi i ON p.instansi_id = i.id 
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pns = $result->fetch_assoc();

if(!$pns) {
    header("Location: data_pns.php");
    exit();
}

ob_clean();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail PNS - SI-PNS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .foto-profil {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
        
        @media (max-width: 768px) {
            .foto-profil {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <?php include('includes/navbar.php'); ?>

    <div class="flex min-h-screen">
        <?php include('includes/sidebar.php'); ?>

        <!-- Konten Utama -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Detail PNS</h1>
                <div>
                    <a href="edit_pns.php?id=<?php echo $id; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded mr-2">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <a href="data_pns.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>

            <!-- Informasi Utama -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex flex-col md:flex-row">
                    <!-- Foto Profil -->
                    <div class="md:w-1/4 flex justify-center mb-6 md:mb-0">
                        <img src="<?php echo !empty($pns['foto']) ? $pns['foto'] : 'Assets/image/default-user.png'; ?>" 
                             alt="Foto <?php echo htmlspecialchars($pns['nama_lengkap']); ?>"
                             class="foto-profil">
                    </div>

                    <!-- Detail Informasi -->
                    <div class="md:w-3/4 md:pl-6">
                        <h2 class="text-xl font-bold mb-4">Informasi Pribadi</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="font-bold mb-1">NIP</p>
                                <p class="text-gray-600"><?php echo htmlspecialchars($pns['nip']); ?></p>
                            </div>
                            <div>
                                <p class="font-bold mb-1">Nama Lengkap</p>
                                <p class="text-gray-600"><?php echo htmlspecialchars($pns['nama_lengkap']); ?></p>
                            </div>
                            <div>
                                <p class="font-bold mb-1">Tempat, Tanggal Lahir</p>
                                <p class="text-gray-600">
                                    <?php 
                                    echo htmlspecialchars($pns['tempat_lahir']) . ', ' . 
                                         date('d F Y', strtotime($pns['tanggal_lahir'])); 
                                    ?>
                                </p>
                            </div>
                            <div>
                                <p class="font-bold mb-1">Jenis Kelamin</p>
                                <p class="text-gray-600">
                                    <?php echo $pns['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                </p>
                            </div>
                            <div>
                                <p class="font-bold mb-1">Alamat</p>
                                <p class="text-gray-600"><?php echo htmlspecialchars($pns['alamat']); ?></p>
                            </div>
                            <div>
                                <p class="font-bold mb-1">Kontak</p>
                                <p class="text-gray-600">
                                    Telepon: <?php echo htmlspecialchars($pns['telepon']); ?><br>
                                    Email: <?php echo htmlspecialchars($pns['email']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Kepegawaian -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Informasi Kepegawaian</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="font-bold mb-1">Instansi</p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($pns['nama_instansi']); ?></p>
                    </div>
                    <div>
                        <p class="font-bold mb-1">Jabatan</p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($pns['nama_jabatan']); ?></p>
                    </div>
                    <div>
                        <p class="font-bold mb-1">Golongan</p>
                        <p class="text-gray-600">
                            <?php 
                            echo htmlspecialchars($pns['nama_golongan'] . ' - ' . $pns['pangkat']); 
                            ?>
                        </p>
                    </div>
                    <div>
                        <p class="font-bold mb-1">Pendidikan Terakhir</p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($pns['pendidikan_terakhir']); ?></p>
                    </div>
                    <div>
                        <p class="font-bold mb-1">Tanggal Masuk</p>
                        <p class="text-gray-600">
                            <?php echo date('d F Y', strtotime($pns['tanggal_masuk'])); ?>
                        </p>
                    </div>
                    <div>
                        <p class="font-bold mb-1">Status</p>
                        <p class="text-gray-600">
                            <?php if($pns['status'] == 'aktif'): ?>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm">
                                    Aktif
                                </span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm">
                                    Tidak Aktif
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>