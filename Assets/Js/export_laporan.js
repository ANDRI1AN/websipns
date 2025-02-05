// Fungsi helper untuk mendapatkan nama bulan
function getNamaBulan(bulan) {
    const bulanList = {
        '01': 'Januari', '02': 'Februari', '03': 'Maret',
        '04': 'April', '05': 'Mei', '06': 'Juni',
        '07': 'Juli', '08': 'Agustus', '09': 'September',
        '10': 'Oktober', '11': 'November', '12': 'Desember'
    };
    return bulanList[bulan] || '';
}

// Fungsi untuk mendapatkan informasi laporan
function getReportInfo() {
    const jenis = document.querySelector('select[name="jenis"]').value;
    const bulan = document.querySelector('select[name="bulan"]').value;
    const tahun = document.querySelector('select[name="tahun"]').value;
    const namaBulan = getNamaBulan(bulan);
    
    let jenisLabel = '';
    switch(jenis) {
        case 'pns': jenisLabel = 'Data PNS'; break;
        case 'kenaikan_pangkat': jenisLabel = 'Kenaikan Pangkat'; break;
        case 'instansi': jenisLabel = 'Data Instansi'; break;
    }
    
    return {
        jenis: jenisLabel,
        bulan: namaBulan,
        tahun: tahun,
        filename: `Laporan_${jenis}_${namaBulan}_${tahun}`
    };
}

// Fungsi mengubah foto ke base64 
function convertImageToBase64(imagePath) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const size = 40;
            canvas.width = size;
            canvas.height = size;
            
            const ctx = canvas.getContext('2d');
            
            // Background putih
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, size, size);
            
            // Clip lingkaran
            ctx.beginPath();
            ctx.arc(size/2, size/2, size/2 - 1, 0, Math.PI * 2);
            ctx.clip();
            
            // Hitung scale untuk aspect ratio
            const scale = Math.min(size/img.width, size/img.height);
            const x = (size - img.width * scale) / 2;
            const y = (size - img.height * scale) / 2;
            
            // Gambar foto
            ctx.drawImage(img, x, y, img.width * scale, img.height * scale);
            
            // Border
            ctx.beginPath();
            ctx.arc(size/2, size/2, size/2 - 1, 0, Math.PI * 2);
            ctx.strokeStyle = '#dee2e6';
            ctx.lineWidth = 1;
            ctx.stroke();
            
            resolve(canvas.toDataURL('image/jpeg', 0.7));
        };
        
        img.onerror = () => reject(new Error('Gagal memuat gambar'));
        img.src = imagePath + '?t=' + new Date().getTime();
    });
}

// Fungsi mendapatkan data tabel
async function getTableData() {
    const rows = document.querySelectorAll('table tbody tr');
    const data = [];
    
    for (const row of rows) {
        const photoPath = row.getAttribute('data-foto') || 'Assets/image/default-user.png';
        try {
            const photoBase64 = await convertImageToBase64(photoPath);
            const rowData = [photoBase64];
            
            row.querySelectorAll('td:not(:first-child)').forEach(td => {
                const badge = td.querySelector('.rounded-full');
                rowData.push(badge ? badge.textContent.trim() : td.textContent.trim());
            });
            
            data.push(rowData);
        } catch (error) {
            console.error('Error loading photo:', error);
            const rowData = [''];
            row.querySelectorAll('td:not(:first-child)').forEach(td => {
                const badge = td.querySelector('.rounded-full');
                rowData.push(badge ? badge.textContent.trim() : td.textContent.trim());
            });
            data.push(rowData);
        }
    }
    return data;
}

// Fungsi export ke PDF
async function exportToPDF() {
    window.jsPDF = window.jspdf.jsPDF;
    
    const reportInfo = getReportInfo();
    const data = await getTableData();
    
    const doc = new jsPDF('l', 'mm', 'a4');
    
    // Header
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(14);
    doc.text(`Laporan ${reportInfo.jenis}`, 15, 15);
    doc.setFontSize(11);
    doc.text(`Periode: ${reportInfo.bulan} ${reportInfo.tahun}`, 15, 22);
    
    // Table config
    const tableConfig = {
        startY: 30,
        theme: 'grid',
        styles: {
            fontSize: 8,
            cellPadding: 3,
            lineWidth: 0.3,
            lineColor: [80, 80, 80]
        },
        headStyles: {
            fillColor: [41, 128, 185],
            textColor: 255,
            fontStyle: 'bold',
        },
        columnStyles: {
            0: { cellWidth: 18, halign: 'center', valign: 'middle' },
            1: { cellWidth: 35 },
            2: { cellWidth: 40 },
            3: { cellWidth: 25 },
            4: { cellWidth: 35 },
            5: { cellWidth: 35 },
            6: { cellWidth: 20 }
        },
        alternateRowStyles: {
            fillColor: [250, 250, 250]
        },
        margin: {
            top: 30,
            left: 15,
            right: 15
        },
        didDrawCell: function(data) {
            if (data.section === 'body' && data.column.index === 0 && data.cell.raw) {
                const dim = 12;
                const x = data.cell.x + (data.cell.width - dim) / 2;
                const y = data.cell.y + (data.cell.height - dim) / 2;
                doc.addImage(data.cell.raw, 'JPEG', x, y, dim, dim);
            }
        }
    };

    doc.autoTable({
        head: [['FOTO', 'NIP', 'NAMA LENGKAP', 'GOLONGAN', 'JABATAN', 'INSTANSI', 'STATUS']],
        body: data,
        ...tableConfig
    });
    
    // Footer
    const pageCount = doc.internal.getNumberOfPages();
    doc.setFontSize(8);
    for(let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.text(
            `Dicetak pada: ${new Date().toLocaleString('id-ID')}`,
            15,
            doc.internal.pageSize.height - 10
        );
        doc.text(
            `Halaman ${i} dari ${pageCount}`,
            doc.internal.pageSize.width - 35,
            doc.internal.pageSize.height - 10
        );
    }
    
    doc.save(`${reportInfo.filename}.pdf`);
}

// Fungsi export ke Excel (tanpa foto)
function exportToExcel() {
    const reportInfo = getReportInfo();
    
    // Header tanpa kolom foto
    const headers = [];
    document.querySelectorAll('table thead th:not(:first-child)').forEach(th => {
        headers.push(th.textContent.trim());
    });
    
    // Data tanpa kolom foto
    const data = [];
    document.querySelectorAll('table tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td:not(:first-child)').forEach(td => {
            const badge = td.querySelector('.rounded-full');
            row.push(badge ? badge.textContent.trim() : td.textContent.trim());
        });
        data.push(row);
    });
    
    // Buat workbook
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet([headers, ...data]);
    
    // Style untuk lebar kolom
    ws['!cols'] = [
        { width: 20 }, // NIP
        { width: 25 }, // Nama
        { width: 15 }, // Golongan
        { width: 25 }, // Jabatan
        { width: 25 }, // Instansi
        { width: 15 }  // Status
    ];
    
    XLSX.utils.book_append_sheet(wb, ws, reportInfo.jenis);
    XLSX.writeFile(wb, `${reportInfo.filename}.xlsx`);
}

// Fungsi print biasa
function printReport() {
    window.print();
}

// Event listener untuk form filter
document.querySelectorAll('select').forEach(select => {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});