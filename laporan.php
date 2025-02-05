<?php
session_start();
require_once 'config/database.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Filter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'pns';

// Reuse existing query logic
switch($jenis) {
    case 'pns':
        $sql = "SELECT p.*, g.nama_golongan, j.nama_jabatan, i.nama_instansi 
                FROM pns p 
                LEFT JOIN golongan g ON p.golongan_id = g.id 
                LEFT JOIN jabatan j ON p.jabatan_id = j.id 
                LEFT JOIN instansi i ON p.instansi_id = i.id 
                WHERE MONTH(p.dibuat_pada) = ? AND YEAR(p.dibuat_pada) = ?
                ORDER BY p.nama_lengkap ASC";
        break;
        
    case 'kenaikan_pangkat':
        $sql = "SELECT kp.*, p.nip, p.nama_lengkap, 
                g1.nama_golongan as golongan_lama, g2.nama_golongan as golongan_baru
                FROM kenaikan_pangkat kp
                LEFT JOIN pns p ON kp.pns_id = p.id
                LEFT JOIN golongan g1 ON kp.golongan_lama_id = g1.id
                LEFT JOIN golongan g2 ON kp.golongan_baru_id = g2.id
                WHERE MONTH(kp.tanggal_kenaikan) = ? AND YEAR(kp.tanggal_kenaikan) = ?
                ORDER BY kp.tanggal_kenaikan DESC";
        break;
        
    case 'instansi':
        $sql = "SELECT i.*, COUNT(p.id) as jumlah_pns
                FROM instansi i
                LEFT JOIN pns p ON i.id = p.instansi_id
                WHERE MONTH(i.dibuat_pada) = ? AND YEAR(i.dibuat_pada) = ?
                GROUP BY i.id
                ORDER BY i.nama_instansi ASC";
        break;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - SI-PNS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- PDF Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body class="bg-gray-100">
    
    <?php include('includes/navbar.php'); ?>

    <div class="flex flex-col md:flex-row min-h-screen">
        <?php include('includes/sidebar.php'); ?>

        <!-- Konten Utama -->
        <div class="flex-1 p-4 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <h1 class="text-2xl font-bold mb-4 md:mb-0">Laporan</h1>
                <div class="flex flex-wrap gap-2">
                    <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded print:hidden">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <button onclick="printReport()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded print:hidden">
                        <i class="fas fa-print mr-2"></i>Cetak
                    </button>
                    <button onclick="exportToPDF()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded print:hidden">
                        <i class="fas fa-file-pdf mr-2"></i>PDF
                    </button>
                    <button onclick="exportToExcel()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded print:hidden">
                        <i class="fas fa-file-excel mr-2"></i>Excel
                    </button>
                </div>
            </div>

            <!-- Form Filter -->
            <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6 print:hidden">
                <form method="GET" class="flex flex-col md:flex-row md:items-end space-y-4 md:space-y-0 md:space-x-4">
                    <div class="flex-1">
                        <label class="block mb-2">Jenis Laporan</label>
                        <select name="jenis" class="w-full border rounded px-3 py-2">
                            <option value="pns" <?php echo $jenis == 'pns' ? 'selected' : ''; ?>>Data PNS</option>
                            <option value="kenaikan_pangkat" <?php echo $jenis == 'kenaikan_pangkat' ? 'selected' : ''; ?>>Kenaikan Pangkat</option>
                            <option value="instansi" <?php echo $jenis == 'instansi' ? 'selected' : ''; ?>>Data Instansi</option>
                        </select>
                    </div>

                    <div class="flex-1">
                        <label class="block mb-2">Bulan</label>
                        <select name="bulan" class="w-full border rounded px-3 py-2">
                            <?php
                            $bulan_list = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ];
                            foreach($bulan_list as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo $bulan == $key ? 'selected' : ''; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex-1">
                        <label class="block mb-2">Tahun</label>
                        <select name="tahun" class="w-full border rounded px-3 py-2">
                            <?php
                            $tahun_sekarang = date('Y');
                            for($i = $tahun_sekarang; $i >= $tahun_sekarang - 5; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Tabel Laporan dengan overflow scroll untuk mobile -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <?php if($jenis == 'pns'): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">FOTO</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAMA LENGKAP</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GOLONGAN</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">JABATAN</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">INSTANSI</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr data-foto="<?php echo htmlspecialchars($row['foto'] ?? 'Assets/image/default-user.png'); ?>">
                                        <td class="px-4 py-3">
                                            <div class="w-12 h-12 relative">
                                                <img src="<?php echo htmlspecialchars($row['foto'] ?? 'Assets/image/default-user.png'); ?>" 
                                                     alt="Foto <?php echo htmlspecialchars($row['nama_lengkap']); ?>"
                                                     class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap"><?php echo htmlspecialchars($row['nip']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['nama_golongan']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['nama_jabatan']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['nama_instansi']); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                   <?php echo $row['status'] == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                    <?php elseif($jenis == 'kenaikan_pangkat'): ?>
                        <!-- Kode tabel kenaikan pangkat tetap sama seperti sebelumnya -->

                    <?php else: ?>
                        <!-- Kode tabel instansi tetap sama seperti sebelumnya -->

                    <?php endif; ?>
                
                    <?php if($result->num_rows == 0): ?>
                        <div class="p-6 text-center text-gray-500">
                            Tidak ada data untuk periode ini
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer Laporan (untuk cetakan) -->
            <div class="mt-8 hidden print:block">
                <div class="text-right">
                    <p class="mb-4">Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
                    <p>Mengetahui,</p>
                    <p class="mt-16">_____________________</p>
                    <p>Kepala Bagian Kepegawaian</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Style untuk responsive dan cetak -->
    <style>
        /* Responsive styles */
        @media (max-width: 768px) {
            .flex-col-mobile { flex-direction: column; }
            .w-full-mobile { width: 100%; }
            .mb-4-mobile { margin-bottom: 1rem; }
            .px-4-mobile { padding-left: 1rem; padding-right: 1rem; }
        }

        /* Print styles */
        @media print {
            @page {
                size: landscape;
                margin: 2cm;
            }
            
            body { font-size: 12pt; }
            
            .print\:hidden { display: none !important; }
            .print\:block { display: block !important; }
            .print\:no-shadow { box-shadow: none !important; }
            
            table {
                width: 100% !important;
                border-collapse: collapse;
            }
            
            th, td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }
            
            /* Style untuk foto saat dicetak */
            td img.rounded-full {
                width: 80px !important;
                height: 80px !important;
                border-radius: 50% !important;
                object-fit: cover !important;
                border: 2px solid #e5e7eb !important;
                background: white !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            /* Status badges untuk cetakan */
            .rounded-full {
                border: 1px solid #000;
                padding: 2px 8px;
            }
            
            /* Warna status untuk cetakan */
            .bg-green-100 { background-color: #f0fff4 !important; }
            .text-green-800 { color: #276749 !important; }
            .bg-red-100 { background-color: #fff5f5 !important; }
            .text-red-800 { color: #9b2c2c !important; }
            .bg-yellow-100 { background-color: #fffff0 !important; }
            .text-yellow-800 { color: #975a16 !important; }
            .bg-blue-100 { background-color: #ebf8ff !important; }
            .text-blue-800 { color: #2c5282 !important; }
        }

        /* Fix untuk overflow pada mobile */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Custom scrollbar untuk table overflow */
        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Style khusus untuk foto */
        table td .rounded-full {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            background: white;
        }
        
        /* Style foto saat cetak */
        @media print {
            table td .rounded-full {
                width: 80px !important;
                height: 80px !important;
                border-radius: 50% !important;
                object-fit: cover !important;
                border: 2px solid #e5e7eb !important;
                background: white !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>

    <!-- Script -->
    <script src="assets/js/export_laporan.js"></script>
</body>
</html>