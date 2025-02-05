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

// Proses Hapus Data
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Ambil info foto sebelum menghapus
    $stmt = $conn->prepare("SELECT foto FROM pns WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pns = $result->fetch_assoc();
    
    // Hapus file foto jika ada
    if($pns && $pns['foto']) {
        deleteFoto($pns['foto']);
    }
    
    // Hapus data dari database
    $sql = "DELETE FROM pns WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $_SESSION['sukses'] = "Data PNS berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus data PNS";
    }
    header("Location: data_pns.php");
    exit();
}

// Ambil data untuk dropdown
$query_instansi = "SELECT * FROM instansi ORDER BY nama_instansi ASC";
$query_golongan = "SELECT * FROM golongan ORDER BY nama_golongan ASC";
$query_jabatan = "SELECT * FROM jabatan ORDER BY nama_jabatan ASC";

$instansi_list = $conn->query($query_instansi);
$golongan_list = $conn->query($query_golongan);
$jabatan_list = $conn->query($query_jabatan);

// Query untuk menampilkan data PNS
$sql = "SELECT p.*, g.nama_golongan, j.nama_jabatan, i.nama_instansi 
        FROM pns p 
        LEFT JOIN golongan g ON p.golongan_id = g.id 
        LEFT JOIN jabatan j ON p.jabatan_id = j.id 
        LEFT JOIN instansi i ON p.instansi_id = i.id 
        ORDER BY p.nama_lengkap ASC";
$result = $conn->query($sql);
ob_clean();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data PNS - SI-PNS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .foto-pns {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }
        
        /* Responsive table styles */
        @media (max-width: 768px) {
            .hide-on-mobile {
                display: none;
            }

            .mobile-card {
                display: block;
                margin-bottom: 1rem;
                padding: 1rem;
                background: white;
                border-radius: 0.5rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            }

            .mobile-card-header {
                font-weight: bold;
                margin-bottom: 0.5rem;
            }

            .mobile-card-content {
                margin-bottom: 0.5rem;
            }

            .desktop-table {
                display: none;
            }

            .foto-pns-mobile {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border-radius: 50%;
                margin-right: 1rem;
            }
        }

        @media (min-width: 769px) {
            .mobile-cards {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <?php include('includes/navbar.php'); ?>

    <div class="flex min-h-screen">
        <?php include('includes/sidebar.php'); ?>

        <!-- Konten Utama -->
        <div class="flex-1 p-4 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Data PNS</h1>
                <div class="flex flex-col sm:flex-row gap-2 sm:space-x-3">
                    <button onclick="window.location.href='dashboard.php'" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </button>
                    <button onclick="window.location.href='tambah_pns.php'" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-plus mr-2"></i>Tambah PNS
                    </button>
                </div>
            </div>

            <?php if(isset($_SESSION['sukses'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    <?php 
                    echo $_SESSION['sukses'];
                    unset($_SESSION['sukses']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Desktop Table View -->
            <div class="bg-white rounded-lg shadow overflow-hidden desktop-table">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Golongan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instansi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img src="<?php echo !empty($row['foto']) ? $row['foto'] : 'assets/img/default-user.png'; ?>" 
                                                 alt="Foto <?php echo htmlspecialchars($row['nama_lengkap']); ?>"
                                                 class="foto-pns">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['nip']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_golongan']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_jabatan']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_instansi']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($row['status'] == 'aktif'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Tidak Aktif
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="detail_pns.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_pns.php?id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" onclick="konfirmasiHapus(<?php echo $row['id']; ?>)" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center">Tidak ada data PNS</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-cards space-y-4">
                <?php 
                if($result->num_rows > 0):
                    $result->data_seek(0); // Reset pointer hasil query
                    while($row = $result->fetch_assoc()): 
                ?>
                    <div class="mobile-card">
                        <div class="flex items-start mb-4">
                            <img src="<?php echo !empty($row['foto']) ? $row['foto'] : 'Assets/image/default-user.png'; ?>" 
                                 alt="Foto <?php echo htmlspecialchars($row['nama_lengkap']); ?>"
                                 class="foto-pns-mobile">
                            <div>
                                <div class="font-bold text-lg"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                <div class="text-sm text-gray-600">NIP: <?php echo htmlspecialchars($row['nip']); ?></div>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-semibold">Golongan:</span> <?php echo htmlspecialchars($row['nama_golongan']); ?></p>
                            <p><span class="font-semibold">Jabatan:</span> <?php echo htmlspecialchars($row['nama_jabatan']); ?></p>
                            <p><span class="font-semibold">Instansi:</span> <?php echo htmlspecialchars($row['nama_instansi']); ?></p>
                            <p>
                                <span class="font-semibold">Status:</span>
                                <?php if($row['status'] == 'aktif'): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Tidak Aktif</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex justify-end space-x-3 mt-4 pt-3 border-t">
                            <a href="detail_pns.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit_pns.php?id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:text-yellow-900">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" onclick="konfirmasiHapus(<?php echo $row['id']; ?>)" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="mobile-card text-center text-gray-500">
                        Tidak ada data PNS
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function konfirmasiHapus(id) {
        if(confirm('Apakah Anda yakin ingin menghapus data PNS ini?')) {
            window.location.href = 'data_pns.php?hapus=' + id;
        }
    }
    </script>
</body>
</html>