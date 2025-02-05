<?php
require_once 'config/database.php';

// Data admin default
$username = 'admin';
$password = '123';  // Ubah password menjadi 123
$nama_lengkap = 'Administrator';

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Hapus admin yang ada 
$sql = "DELETE FROM pengguna WHERE username = 'admin'";
$conn->query($sql);

// Buat admin baru
$sql = "INSERT INTO pengguna (username, password, nama_lengkap) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("sss", $username, $hashed_password, $nama_lengkap);
    
    if ($stmt->execute()) {
        echo "Admin berhasil dibuat!<br>";
        echo "Username: admin<br>";
        echo "Password: 123<br>";
    } else {
        echo "Gagal membuat admin: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Error dalam prepared statement: " . $conn->error;
}
$conn->close();
?>