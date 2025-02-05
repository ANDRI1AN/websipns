<?php
session_start();
require_once 'config/database.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Proses Hapus Data
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $sql = "DELETE FROM kenaikan_pangkat WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $_SESSION['sukses'] = "Data kenaikan pangkat berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus data kenaikan pangkat";
    }
    header("Location: kenaikan_pangkat.php");
    exit();
}

// Query untuk menampilkan data kenaikan pangkat
$sql = "SELECT kp.*, 
        p.nip, p.nama_lengkap,
        g1.nama_golongan as golongan_lama, 
        g2.nama_golongan as golongan_baru 
        FROM kenaikan_pangkat kp
        LEFT JOIN pns p ON kp.pns_id = p.id
        LEFT JOIN golongan g1 ON kp.golongan_lama_id = g1.id
        LEFT JOIN golongan g2 ON kp.golongan_baru_id = g2.id
        ORDER BY kp.tanggal_kenaikan DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenaikan Pangkat - SI-PNS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media (max-width: 768px) {
            .mobile-card {
                margin-bottom: 1rem;
                padding: 1rem;
                background: white;
                border-radius: 0.5rem;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            }

            .desktop-table {
                display: none;
            }

            .mobile-cards {
                display: block;
            }
        }

        @media (min-width: 769px) {
            .mobile-cards {
                display: none;
            }

            .desktop-table {
                display: block;
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
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold">Kenaikan Pangkat</h1>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button onclick="window.location.href='dashboard.php'" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </button>
                    <a href="tambah_kenaikan_pangkat.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-plus mr-2"></i>Tambah Kenaikan Pangkat
                    </a>
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
            <div class="desktop-table bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gol. Lama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gol. Baru</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kenaikan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['nip']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['golongan_lama']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['golongan_baru']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo date('d/m/Y', strtotime($row['tanggal_kenaikan'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $status_class = [
                                                'diajukan' => 'bg-yellow-100 text-yellow-800',
                                                'proses' => 'bg-blue-100 text-blue-800',
                                                'disetujui' => 'bg-green-100 text-green-800',
                                                'ditolak' => 'bg-red-100 text-red-800'
                                            ];
                                            $status_text = ucfirst($row['status']);
                                            $class = $status_class[$row['status']];
                                            ?>
                                            <span class="<?php echo $class; ?> px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="#" onclick="konfirmasiHapus(<?php echo $row['id']; ?>)" 
                                               class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center">Tidak ada data kenaikan pangkat</td>
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
                    $result->data_seek(0);
                    while($row = $result->fetch_assoc()): 
                        $status_class = [
                            'diajukan' => 'bg-yellow-100 text-yellow-800',
                            'proses' => 'bg-blue-100 text-blue-800',
                            'disetujui' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800'
                        ];
                        $status_text = ucfirst($row['status']);
                        $class = $status_class[$row['status']];
                ?>
                    <div class="mobile-card">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-lg"><?php echo htmlspecialchars($row['nama_lengkap']); ?></h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['nip']); ?></p>
                            </div>
                            <span class="<?php echo $class; ?> px-2 py-1 text-xs rounded-full">
                                <?php echo $status_text; ?>
                            </span>
                        </div>
                        <div class="space-y-2 mb-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-600">Golongan Lama</p>
                                    <p><?php echo htmlspecialchars($row['golongan_lama']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-600">Golongan Baru</p>
                                    <p><?php echo htmlspecialchars($row['golongan_baru']); ?></p>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-600">Tanggal Kenaikan</p>
                                <p><?php echo date('d/m/Y', strtotime($row['tanggal_kenaikan'])); ?></p>
                            </div>
                        </div>
                        <div class="flex justify-end border-t pt-3">
                            <a href="#" onclick="konfirmasiHapus(<?php echo $row['id']; ?>)" 
                               class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="mobile-card text-center text-gray-500">
                        Tidak ada data kenaikan pangkat
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function konfirmasiHapus(id) {
        if(confirm('Apakah Anda yakin ingin menghapus data kenaikan pangkat ini?')) {
            window.location.href = 'kenaikan_pangkat.php?hapus=' + id;
        }
    }
    </script>
</body>
</html>