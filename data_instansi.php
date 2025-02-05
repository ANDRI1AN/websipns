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
    
    // Cek apakah instansi masih digunakan
    $check = "SELECT id FROM pns WHERE instansi_id = ?";
    $stmt = $conn->prepare($check);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $_SESSION['error'] = "Instansi tidak dapat dihapus karena masih digunakan oleh PNS";
    } else {
        $sql = "DELETE FROM instansi WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            $_SESSION['sukses'] = "Data instansi berhasil dihapus";
        } else {
            $_SESSION['error'] = "Gagal menghapus data instansi";
        }
    }
    header("Location: data_instansi.php");
    exit();
}

// Query untuk menampilkan data instansi
$sql = "SELECT * FROM instansi ORDER BY nama_instansi ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Instansi - SI-PNS</title>
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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold">Data Instansi</h1>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button onclick="window.location.href='dashboard.php'" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-arrow-left mr-2"></i>Dashboard
                    </button>
                    <button onclick="tampilFormTambah()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-plus mr-2"></i>Tambah Instansi
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
            <div class="desktop-table bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Instansi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['nama_instansi']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['alamat']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['telepon']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="tampilFormEdit(<?php echo $row['id']; ?>, 
                                                '<?php echo addslashes($row['nama_instansi']); ?>', 
                                                '<?php echo addslashes($row['alamat']); ?>', 
                                                '<?php echo addslashes($row['telepon']); ?>')" 
                                                class="text-yellow-600 hover:text-yellow-900 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="#" onclick="konfirmasiHapus(<?php echo $row['id']; ?>)" 
                                               class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center">Tidak ada data instansi</td>
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
                ?>
                    <div class="mobile-card">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="font-bold text-lg"><?php echo htmlspecialchars($row['nama_instansi']); ?></h3>
                            <div class="flex gap-2">
                                <button onclick="tampilFormEdit(<?php echo $row['id']; ?>, 
                                    '<?php echo addslashes($row['nama_instansi']); ?>', 
                                    '<?php echo addslashes($row['alamat']); ?>', 
                                    '<?php echo addslashes($row['telepon']); ?>')" 
                                    class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="#" onclick="konfirmasiHapus(<?php echo $row['id']; ?>)" 
                                   class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <p class="text-sm font-semibold text-gray-600">Alamat</p>
                                <p class="text-gray-800"><?php echo htmlspecialchars($row['alamat']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-600">Telepon</p>
                                <p class="text-gray-800"><?php echo htmlspecialchars($row['telepon']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="mobile-card text-center text-gray-500">
                        Tidak ada data instansi
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div id="modalForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="modalTitle" class="text-2xl font-bold">Tambah Instansi</h2>
                    <button onclick="tutupModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="formInstansi" action="proses_instansi.php" method="POST">
                    <input type="hidden" name="aksi" id="aksi" value="tambah">
                    <input type="hidden" name="id" id="instansi_id">
                    
                    <div class="mb-4">
                        <label for="nama_instansi" class="block text-gray-700 text-sm font-bold mb-2">Nama Instansi</label>
                        <input type="text" name="nama_instansi" id="nama_instansi" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="alamat" class="block text-gray-700 text-sm font-bold mb-2">Alamat</label>
                        <textarea name="alamat" id="alamat" rows="3" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label for="telepon" class="block text-gray-700 text-sm font-bold mb-2">Telepon</label>
                        <input type="tel" name="telepon" id="telepon" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="tutupModal()" 
                                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function tampilFormTambah() {
        document.getElementById('modalTitle').textContent = 'Tambah Instansi';
        document.getElementById('aksi').value = 'tambah';
        document.getElementById('instansi_id').value = '';
        document.getElementById('nama_instansi').value = '';
        document.getElementById('alamat').value = '';
        document.getElementById('telepon').value = '';
        document.getElementById('modalForm').classList.remove('hidden');
    }

    function tampilFormEdit(id, nama, alamat, telepon) {
        document.getElementById('modalTitle').textContent = 'Edit Instansi';
        document.getElementById('aksi').value = 'edit';
        document.getElementById('instansi_id').value = id;
        document.getElementById('nama_instansi').value = nama;
        document.getElementById('alamat').value = alamat;
        document.getElementById('telepon').value = telepon;
        document.getElementById('modalForm').classList.remove('hidden');
    }

    function tutupModal() {
        document.getElementById('modalForm').classList.add('hidden');
    }

    function konfirmasiHapus(id) {
        if(confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            window.location.href = 'data_instansi.php?hapus=' + id;
        }
    }
    </script>
</body>
</html>