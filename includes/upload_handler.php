<?php
function uploadFoto($file, $old_foto = null) {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Definisi path absolut untuk XAMPP
    $base_path = realpath(dirname(dirname(__FILE__))); // Dapatkan path absolut
    $target_dir = $base_path . "/Uploads/foto_pns/";
    
    // Log untuk debug
    error_log("Upload attempt started");
    error_log("Base path: " . $base_path);
    error_log("Target directory: " . $target_dir);
    
    // Cek dan buat folder upload jika belum ada
    if (!file_exists($target_dir)) {
        error_log("Creating upload directory");
        if (!mkdir($target_dir, 0777, true)) {
            $error = error_get_last();
            throw new Exception("Gagal membuat direktori upload: " . ($error['message'] ?? 'Unknown error'));
        }
    }
    
    // Cek permission direktori
    if (!is_writable($target_dir)) {
        error_log("Directory not writable: " . $target_dir);
        chmod($target_dir, 0777); // Coba set permission
        if (!is_writable($target_dir)) {
            throw new Exception("Direktori upload tidak writable");
        }
    }
    
    // Validasi file upload
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        error_log("No file uploaded or upload failed");
        throw new Exception("Tidak ada file yang diupload");
    }
    
    // Validasi file adalah gambar
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        error_log("Invalid image file");
        throw new Exception("File bukan gambar yang valid");
    }
    
    // Generate nama file unik
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Validasi ukuran (max 5MB)
    if ($file["size"] > 5000000) {
        error_log("File too large: " . $file["size"]);
        throw new Exception("Ukuran file terlalu besar (maksimal 5MB)");
    }
    
    // Validasi tipe file
    $allowed_types = array('jpg', 'jpeg', 'png');
    if (!in_array($file_extension, $allowed_types)) {
        error_log("Invalid file type: " . $file_extension);
        throw new Exception("Hanya file JPG, JPEG & PNG yang diizinkan");
    }
    
    // Hapus foto lama jika ada
    if ($old_foto && file_exists($old_foto)) {
        error_log("Deleting old file: " . $old_foto);
        unlink($old_foto);
    }
    
    // Upload file baru
    error_log("Moving uploaded file to: " . $target_file);
    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
        $error = error_get_last();
        error_log("Upload failed: " . ($error['message'] ?? 'Unknown error'));
        throw new Exception("Gagal mengupload file");
    }
    
    error_log("File uploaded successfully to: " . $target_file);
    
    // Return path relatif untuk database
    return "Uploads/foto_pns/" . $new_filename;
}

// Fungsi untuk menghapus foto
function deleteFoto($foto_path) {
    if ($foto_path && file_exists($foto_path)) {
        error_log("Deleting file: " . $foto_path);
        return unlink($foto_path);
    }
    return true;
}

// Fungsi untuk mendapatkan foto default
function getDefaultFoto() {
    return "Assets/image/default-user.png";
}

// Fungsi untuk mengecek file gambar valid
function isValidImage($file) {
    if(!$file || $file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error code: " . ($file['error'] ?? 'Unknown'));
        return false;
    }
    
    $check = getimagesize($file["tmp_name"]);
    return $check !== false;
}
?>a