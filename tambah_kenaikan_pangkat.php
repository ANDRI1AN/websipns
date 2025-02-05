<?php
session_start();
require_once 'config/database.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data untuk dropdown
$query_pns = "SELECT id, nip, nama_lengkap FROM pns WHERE status='aktif' ORDER BY nama_lengkap ASC";
$query_golongan = "SELECT * FROM golongan ORDER BY nama_golongan ASC";

$pns_list = $conn->query($query_pns);
$golongan_list = $conn->query($query_golongan);
$golongan_list2 = $conn->query($query_golongan); // Untuk golongan baru

// Proses tambah data
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pns_id = $_POST['pns_id'];
    $golongan_lama_id = $_POST['golongan_lama_id'];
    $golongan_baru_id = $_POST['golongan_baru_id'];
    $tanggal_kenaikan = $_POST['tanggal_kenaikan'];
    $nomor_sk = $_POST['nomor_sk'];
    $tanggal_sk = $_POST['tanggal_sk'];
    $jenis_kenaikan = $_POST['jenis_kenaikan'];
    $status = $_POST['status'];
    $keterangan = $_POST['keterangan'];

    $sql = "INSERT INTO kenaikan_pangkat (pns_id, golongan_lama_id, golongan_baru_id, 
            tanggal_kenaikan, nomor_sk, tanggal_sk, jenis_kenaikan, status, keterangan) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissssss", 
        $pns_id, $golongan_lama_id, $golongan_baru_id,
        $tanggal_kenaikan, $nomor_sk, $tanggal_sk, $jenis_kenaikan, $status, $keterangan
    );

    if($stmt->execute()) {
        $_SESSION['sukses'] = "Data kenaikan pangkat berhasil ditambahkan";
        header("Location: kenaikan_pangkat.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan data kenaikan pangkat";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kenaikan Pangkat - SI-PNS</title>
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
                <h1 class="text-2xl font-bold">Tambah Kenaikan Pangkat</h1>
                <a href="kenaikan_pangkat.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Form Tambah Kenaikan Pangkat -->
            <div class="bg-white rounded-lg shadow p-6">
                <form action="tambah_kenaikan_pangkat.php" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-2">PNS</label>
                            <select name="pns_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">Pilih PNS</option>
                                <?php while($pns = $pns_list->fetch_assoc()): ?>
                                    <option value="<?php echo $pns['id']; ?>">
                                        <?php echo htmlspecialchars($pns['nip'] . ' - ' . $pns['nama_lengkap']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2">Golongan Lama</label>
                            <select name="golongan_lama_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">Pilih Golongan Lama</option>
                                <?php while($golongan = $golongan_list->fetch_assoc()): ?>
                                    <option value="<?php echo $golongan['id']; ?>">
                                        <?php echo htmlspecialchars($golongan['nama_golongan'] . ' - ' . $golongan['pangkat']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2">Golongan Baru
                            <select name="golongan_baru_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">Pilih Golongan Baru</option>
                                <?php while($golongan = $golongan_list2->fetch_assoc()): ?>
                                    <option value="<?php echo $golongan['id']; ?>">
                                        <?php echo htmlspecialchars($golongan['nama_golongan'] . ' - ' . $golongan['pangkat']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2">Tanggal Kenaikan</label>
                            <input type="date" name="tanggal_kenaikan" class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block mb-2">Nomor SK</label>
                            <input type="text" name="nomor_sk" class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block mb-2">Tanggal SK</label>
                            <input type="date" name="tanggal_sk" class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block mb-2">Jenis Kenaikan</label>
                            <select name="jenis_kenaikan" class="w-full border rounded px-3 py-2" required>
                                <option value="">Pilih Jenis Kenaikan</option>
                                <option value="reguler">Reguler</option>
                                <option value="pilihan">Pilihan</option>
                                <option value="struktural">Struktural</option>
                                <option value="fungsional">Fungsional</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2">Status</label>
                            <select name="status" class="w-full border rounded px-3 py-2" required>
                                <option value="diajukan">Diajukan</option>
                                <option value="proses">Proses</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-2">Keterangan</label>
                            <textarea name="keterangan" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mr-2">
                            <i class="fas fa-save mr-2"></i>Simpan
                        </button>
                        <button type="reset" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>