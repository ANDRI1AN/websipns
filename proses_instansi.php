<?php
session_start();
require_once 'config/database.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Proses form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $errors = [];
    
    // Ambil dan bersihkan input menggunakan fungsi clean_input dari database.php
    $aksi = isset($_POST['aksi']) ? clean_input($_POST['aksi']) : '';
    $nama_instansi = isset($_POST['nama_instansi']) ? clean_input($_POST['nama_instansi']) : '';
    $alamat = isset($_POST['alamat']) ? clean_input($_POST['alamat']) : '';
    $telepon = isset($_POST['telepon']) ? clean_input($_POST['telepon']) : '';

    // Validasi input
    if(empty($nama_instansi)) {
        $errors[] = "Nama instansi wajib diisi";
    }
    if(empty($alamat)) {
        $errors[] = "Alamat wajib diisi";
    }
    if(empty($telepon)) {
        $errors[] = "Nomor telepon wajib diisi";
    }

    // Jika tidak ada error, proses data
    if(empty($errors)) {
        try {
            if($aksi == 'tambah') {
                // Cek apakah nama instansi sudah ada
                $check_sql = "SELECT id FROM instansi WHERE nama_instansi = ?";
                $check_stmt = $conn->prepare($check_sql);
                if(!$check_stmt) {
                    throw new Exception("Prepare statement error: " . $conn->error);
                }
                
                $check_stmt->bind_param("s", $nama_instansi);
                if(!$check_stmt->execute()) {
                    throw new Exception("Execute error: " . $check_stmt->error);
                }
                
                $result = $check_stmt->get_result();

                if($result->num_rows > 0) {
                    $_SESSION['error'] = "Nama instansi sudah terdaftar";
                } else {
                    // Tambah data baru
                    $sql = "INSERT INTO instansi (nama_instansi, alamat, telepon) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    if(!$stmt) {
                        throw new Exception("Prepare statement error: " . $conn->error);
                    }
                    
                    $stmt->bind_param("sss", $nama_instansi, $alamat, $telepon);
                    
                    if($stmt->execute()) {
                        $_SESSION['sukses'] = "Data instansi berhasil ditambahkan";
                        header("Location: data_instansi.php");
                        exit();
                    } else {
                        throw new Exception("Gagal menambahkan data: " . $stmt->error);
                    }
                }
            } 
            elseif($aksi == 'edit') {
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                if($id <= 0) {
                    throw new Exception("ID instansi tidak valid");
                }

                // Cek apakah nama instansi sudah ada (kecuali untuk instansi yang sedang diedit)
                $check_sql = "SELECT id FROM instansi WHERE nama_instansi = ? AND id != ?";
                $check_stmt = $conn->prepare($check_sql);
                if(!$check_stmt) {
                    throw new Exception("Prepare statement error: " . $conn->error);
                }
                
                $check_stmt->bind_param("si", $nama_instansi, $id);
                if(!$check_stmt->execute()) {
                    throw new Exception("Execute error: " . $check_stmt->error);
                }
                
                $result = $check_stmt->get_result();

                if($result->num_rows > 0) {
                    $_SESSION['error'] = "Nama instansi sudah terdaftar";
                } else {
                    // Update data
                    $sql = "UPDATE instansi SET nama_instansi = ?, alamat = ?, telepon = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if(!$stmt) {
                        throw new Exception("Prepare statement error: " . $conn->error);
                    }
                    
                    $stmt->bind_param("sssi", $nama_instansi, $alamat, $telepon, $id);
                    
                    if($stmt->execute()) {
                        $_SESSION['sukses'] = "Data instansi berhasil diperbarui";
                        header("Location: data_instansi.php");
                        exit();
                    } else {
                        throw new Exception("Gagal memperbarui data: " . $stmt->error);
                    }
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    // Redirect jika ada error
    header("Location: data_instansi.php");
    exit();
}

// Query untuk menampilkan data
$query = "SELECT * FROM instansi ORDER BY nama_instansi ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Instansi - SI-PNS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    
    <?php include('includes/navbar.php'); ?>

    <div class="flex min-h-screen">
        <?php include('includes/sidebar.php'); ?>

        <!-- Konten Utama -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Data Instansi</h1>
                <button onclick="showModal('tambah')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    <i class="fas fa-plus mr-2"></i>Tambah Instansi
                </button>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['sukses'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    <?php 
                    echo $_SESSION['sukses'];
                    unset($_SESSION['sukses']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Form Modal Tambah/Edit -->
            <div id="formModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg w-full max-w-md">
                    <h2 id="modalTitle" class="text-xl font-bold mb-4">Tambah Instansi</h2>
                    <form action="data_instansi.php" method="POST">
                        <input type="hidden" name="aksi" id="aksi" value="tambah">
                        <input type="hidden" name="id" id="instansi_id">
                        
                        <div class="mb-4">
                            <label class="block mb-2">Nama Instansi</label>
                            <input type="text" name="nama_instansi" id="nama_instansi" 
                                   class="w-full border rounded px-3 py-2" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block mb-2">Alamat</label>
                            <textarea name="alamat" id="alamat" class="w-full border rounded px-3 py-2" 
                                      rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block mb-2">Telepon</label>
                            <input type="tel" name="telepon" id="telepon" 
                                   class="w-full border rounded px-3 py-2" required>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="button" onclick="hideModal()" 
                                    class="bg-gray-500 text-white px-4 py-2 rounded mr-2">
                                Batal
                            </button>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">No</th>
                            <th class="px-6 py-3 text-left">Nama Instansi</th>
                            <th class="px-6 py-3 text-left">Alamat</th>
                            <th class="px-6 py-3 text-left">Telepon</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if($result->num_rows > 0):
                            $no = 1;
                            while($row = $result->fetch_assoc()): 
                        ?>
                            <tr class="border-t">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nama_instansi']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['alamat']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['telepon']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick="editInstansi(<?= htmlspecialchars(json_encode($row)) ?>)" 
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded mr-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr class="border-t">
                                <td colspan="5" class="px-6 py-4 text-center">Tidak ada data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function showModal(type, data = null) {
        document.getElementById('formModal').classList.remove('hidden');
        if(type === 'tambah') {
            document.getElementById('modalTitle').textContent = 'Tambah Instansi';
            document.getElementById('aksi').value = 'tambah';
            document.getElementById('instansi_id').value = '';
            document.getElementById('nama_instansi').value = '';
            document.getElementById('alamat').value = '';
            document.getElementById('telepon').value = '';
        }
    }

    function hideModal() {
        document.getElementById('formModal').classList.add('hidden');
    }

    function editInstansi(data) {
        document.getElementById('modalTitle').textContent = 'Edit Instansi';
        document.getElementById('aksi').value = 'edit';
        document.getElementById('instansi_id').value = data.id;
        document.getElementById('nama_instansi').value = data.nama_instansi;
        document.getElementById('alamat').value = data.alamat;
        document.getElementById('telepon').value = data.telepon;
        document.getElementById('formModal').classList.remove('hidden');
    }
    </script>
</body>
</html>