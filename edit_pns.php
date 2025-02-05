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

// Ambil data PNS
$sql = "SELECT * FROM pns WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pns = $result->fetch_assoc();

if(!$pns) {
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

// Proses update data
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = $_POST['nip'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];
    $email = $_POST['email'];
    $instansi_id = $_POST['instansi_id'];
    $jabatan_id = $_POST['jabatan_id'];
    $golongan_id = $_POST['golongan_id'];
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $status = $_POST['status'];

    // Validasi NIP unik kecuali untuk data yang sedang diedit
    $check_nip = "SELECT id FROM pns WHERE nip = ? AND id != ?";
    $stmt = $conn->prepare($check_nip);
    $stmt->bind_param("si", $nip, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $_SESSION['error'] = "NIP sudah terdaftar!";
    } else {
        try {
            // Handle foto
            $foto_path = $pns['foto']; // Gunakan foto yang ada
            
            // Jika ada foto baru diupload
            if(isset($_FILES['foto']) && $_FILES['foto']['error'] != UPLOAD_ERR_NO_FILE) {
                $foto_path = uploadFoto($_FILES['foto'], $pns['foto']);
            }
            
            // Jika checkbox hapus foto dicentang
            if(isset($_POST['hapus_foto']) && $_POST['hapus_foto'] == 1) {
                if($pns['foto'] && file_exists($pns['foto'])) {
                    unlink($pns['foto']);
                }
                $foto_path = null;
            }

            // Query update dengan foto
            $sql = "UPDATE pns SET 
                    nip = ?, 
                    nama_lengkap = ?, 
                    tempat_lahir = ?, 
                    tanggal_lahir = ?, 
                    jenis_kelamin = ?,
                    alamat = ?, 
                    telepon = ?, 
                    email = ?, 
                    instansi_id = ?, 
                    jabatan_id = ?, 
                    golongan_id = ?,
                    pendidikan_terakhir = ?, 
                    tanggal_masuk = ?, 
                    status = ?,
                    foto = ? 
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssiiissssi", 
                $nip, $nama_lengkap, $tempat_lahir, $tanggal_lahir, $jenis_kelamin,
                $alamat, $telepon, $email, $instansi_id, $jabatan_id, $golongan_id,
                $pendidikan_terakhir, $tanggal_masuk, $status, $foto_path, $id
            );

            if($stmt->execute()) {
                $_SESSION['sukses'] = "Data PNS berhasil diperbarui";
                header("Location: data_pns.php");
                exit();
            } else {
                throw new Exception("Gagal memperbarui data PNS");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
}
ob_clean();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data PNS - SI-PNS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .preview-foto {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
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
                <h1 class="text-2xl font-bold">Edit Data PNS</h1>
                <a href="data_pns.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
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

            <!-- Form Edit PNS -->
            <div class="bg-white rounded-lg shadow p-6">
                <form action="edit_pns.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Input Foto -->
                        <div class="md:col-span-2">
                            <label class="block mb-2">Foto</label>
                            <div class="flex items-start space-x-4">
                                <div>
                                    <img src="<?php echo !empty($pns['foto']) ? $pns['foto'] : 'assets/img/default-user.png'; ?>" 
                                         alt="Preview" id="preview" class="preview-foto mb-2">
                                    <input type="file" name="foto" id="foto" accept="image/*" class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100" onchange="previewImage(this)">
                                    <small class="text-gray-500 block">Format: JPG, JPEG, PNG. Maksimal 5MB</small>
                                    
                                    <?php if(!empty($pns['foto'])): ?>
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="hapus_foto" value="1" class="form-checkbox h-4 w-4 text-red-600">
                                            <span class="ml-2 text-sm text-red-600">Hapus foto saat ini</span>
                                        </label>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2">NIP</label>
                            <input 
                                type="text" 
                                name="nip" 
                                value="<?php echo htmlspecialchars($pns['nip']); ?>" 
                                class="w-full border rounded px-3 py-2" 
                                required 
                                minlength="18" 
                                maxlength="18"
                                pattern="\d{18}"
                                title="NIP harus terdiri dari 18 digit angka"
                            >
                        </div>
                        
                        <div>
                            <label class="block mb-2">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($pns['nama_lengkap']); ?>" 
                                   class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block mb-2">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($pns['tempat_lahir']); ?>" 
                                   class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($pns['tanggal_lahir']); ?>" 
                                   class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block mb-2">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full border rounded px-3 py-2" required>
                                <option value="L" <?php echo $pns['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="P" <?php echo $pns['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2">Telepon</label>
                            <input type="tel" name="telepon" value="<?php echo htmlspecialchars($pns['telepon']); ?>" 
                                   class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block mb-2">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($pns['email']); ?>" 
                                   class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block mb-2">Instansi</label>
                            <select name="instansi_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">Pilih Instansi</option>
                                <?php while($instansi = $instansi_list->fetch_assoc()): ?>
                                    <option value="<?php echo $instansi['id']; ?>" 
                                            <?php echo $pns['instansi_id'] == $instansi['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($instansi['nama_instansi']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2">Jabatan</label>
                            <select name="jabatan_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">Pilih Jabatan</option>
                                <?php while($jabatan = $jabatan_list->fetch_assoc()): ?>
                                    <option value="<?php echo $jabatan['id']; ?>"
                                            <?php echo $pns['jabatan_id'] == $jabatan['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($jabatan['nama_jabatan']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2">Golongan</label>
                            <select name="golongan_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">Pilih Golongan</option>
                                <?php while($golongan = $golongan_list->fetch_assoc()): ?>
                                    <option value="<?php echo $golongan['id']; ?>"
                                            <?php echo $pns['golongan_id'] == $golongan['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($golongan['nama_golongan'] . ' - ' . $golongan['pangkat']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2">Pendidikan Terakhir</label>
                            <select name="pendidikan_terakhir" class="w-full border rounded px-3 py-2" required>
                                <?php
                                $pendidikan = array('SMA', 'D3', 'D4', 'S1', 'S2', 'S3');
                                foreach($pendidikan as $p): ?>
                                    <option value="<?php echo $p; ?>"
                                            <?php echo $pns['pendidikan_terakhir'] == $p ? 'selected' : ''; ?>>
                                        <?php echo $p; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2">Tanggal Masuk</label>
                            <input type="date" name="tanggal_masuk" value="<?php echo htmlspecialchars($pns['tanggal_masuk']); ?>"
                                   class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block mb-2">Status</label>
                            <select name="status" class="w-full border rounded px-3 py-2" required>
                                <option value="aktif" <?php echo $pns['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="tidak_aktif" <?php echo $pns['status'] == 'tidak_aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-2">Alamat</label>
                            <textarea name="alamat" class="w-full border rounded px-3 py-2" rows="3" required><?php echo htmlspecialchars($pns['alamat']); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mr-2">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                        <a href="data_pns.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Preview foto sebelum upload
    function previewImage(input) {
        var preview = document.getElementById('preview');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Reset preview jika checkbox hapus foto dicentang
    document.addEventListener('DOMContentLoaded', function() {
        var hapusFotoCheckbox = document.querySelector('input[name="hapus_foto"]');
        if(hapusFotoCheckbox) {
            hapusFotoCheckbox.addEventListener('change', function() {
                var preview = document.getElementById('preview');
                if(this.checked) {
                    preview.src = 'Assets/default-user.png';
                } else {
                    preview.src = '<?php echo !empty($pns['foto']) ? $pns['foto'] : 'Assets/image/default-user.png'; ?>';
                }
            });
        }
    });

    // Validasi format NIP
    document.querySelector('input[name="nip"]').addEventListener('input', function(e) {
        let value = e.target.value;
        value = value.replace(/\D/g, '');
        if (value.length > 18) value = value.slice(0, 18);
        e.target.value = value;
    });
    </script>
</body>
</html>