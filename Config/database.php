<?php
// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'si_pns';

// Buat koneksi
try {
    $conn = new mysqli($host, $username, $password, $database);
    
    // Cek koneksi
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }
    
    // Set karakter encoding
    $conn->set_charset("utf8mb4");
    
    // Set error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Fungsi untuk debug query
function debug_query($query) {
    global $conn;
    try {
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception($conn->error);
        }
        return $result;
    } catch (Exception $e) {
        die("Query Error: " . $e->getMessage());
    }
}

// Fungsi untuk menjalankan prepared statement dengan aman
function safe_prepare($sql) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    return $stmt;
}

// Fungsi untuk mengecek apakah tabel ada
function table_exists($table_name) {
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE '$table_name'");
    return $result->num_rows > 0;
}

// Cek apakah tabel-tabel yang diperlukan sudah ada
$required_tables = ['pns', 'instansi', 'jabatan', 'golongan'];
$missing_tables = [];

foreach ($required_tables as $table) {
    if (!table_exists($table)) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    die("Error: Tabel berikut belum dibuat: " . implode(", ", $missing_tables));
}

?>