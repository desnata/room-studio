    <?php
// ============================================================
//  api.php — REST-like API endpoint Room Studio
//  Semua request AJAX dari frontend menuju file ini
// ============================================================

require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = $_REQUEST['action'] ?? '';
$db     = getDB();

// ─────────────────────────────────────────────────────────────
//  LAYANAN
// ─────────────────────────────────────────────────────────────
if ($action === 'get_layanan') {
    $rows = $db->query("
        SELECT l.*, k.nama AS kategori
        FROM layanan l
        JOIN kategori k ON k.id = l.kategori_id
        WHERE l.aktif = 1
        ORDER BY k.urutan, l.id
    ")->fetchAll();

    foreach ($rows as &$r) {
        $r['harga_display'] = formatHarga((int)$r['harga_min'], $r['harga_max'] ? (int)$r['harga_max'] : null, $r['satuan']);
    }
    jsonResponse(true, '', $rows);
}

// ─────────────────────────────────────────────────────────────
//  CEK SLOT (double-booking guard)
// ─────────────────────────────────────────────────────────────
if ($action === 'cek_slot') {
    $tanggal = $_GET['tanggal'] ?? '';
    $jam     = $_GET['jam']     ?? '';
    if (!$tanggal || !$jam) jsonResponse(false, 'Parameter tidak lengkap');

    $stmt = $db->prepare("
        SELECT COUNT(*) AS jml FROM booking
        WHERE tanggal = ? AND jam = ? AND status NOT IN ('batal')
    ");
    $stmt->execute([$tanggal, $jam]);
    $jml = (int)$stmt->fetch()['jml'];

    jsonResponse(true, '', ['tersedia' => $jml === 0]);
}

// ─────────────────────────────────────────────────────────────
//  BUAT BOOKING (POST) — NOW SUPPORTS MULTIPLE SERVICES
// ─────────────────────────────────────────────────────────────
if ($action === 'buat_booking' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama']       ?? '');
    $telepon    = trim($_POST['telepon']    ?? '');
    $tanggal    = $_POST['tanggal']  ?? '';
    $jam        = $_POST['jam']      ?? '';
    $catatan    = trim($_POST['catatan'] ?? '') ?: null;
    
    // Support multiple layanan_ids (array)
    $layanan_ids = $_POST['layanan_ids'] ?? [];
    if (!is_array($layanan_ids)) {
        $layanan_ids = [$layanan_ids];
    }
    $layanan_ids = array_filter($layanan_ids, 'intval');

    // Validasi
    if (!$nama || !$telepon || empty($layanan_ids) || !$tanggal || !$jam)
        jsonResponse(false, 'Harap lengkapi semua kolom wajib.');

    if (!preg_match('/^[0-9]{8,15}$/', $telepon))
        jsonResponse(false, 'Format nomor telepon tidak valid.');

    if (strtotime($tanggal) < strtotime(date('Y-m-d')))
        jsonResponse(false, 'Tanggal tidak boleh di masa lalu.');

    // Cek double booking
    $cek = $db->prepare("SELECT COUNT(*) AS jml FROM booking WHERE tanggal=? AND jam=? AND status!='batal'");
    $cek->execute([$tanggal, $jam]);
    if ((int)$cek->fetch()['jml'] > 0)
        jsonResponse(false, 'Waktu tersebut sudah dipesan. Pilih jam lain.');

    $db->beginTransaction();
    try {
        // Upsert pelanggan
        $db->prepare("
            INSERT INTO pelanggan (nama, telepon)
            VALUES (?, ?)
            ON CONFLICT (telepon) DO UPDATE SET nama = EXCLUDED.nama
        ")->execute([$nama, $telepon ]);

        $stmt = $db->prepare("SELECT id FROM pelanggan WHERE telepon=?");
        $stmt->execute([$telepon]);
        $pelanggan_id = (int)$stmt->fetch()['id'];

        // Create main booking record
        $kode = generateKode($db);
        $db->prepare("
            INSERT INTO booking (kode, pelanggan_id, tanggal, jam, catatan, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ")->execute([$kode, $pelanggan_id, $tanggal, $jam, $catatan]);
        
        $booking_id = (int)$db->lastInsertId();
        
        // Insert multiple services into booking_layanan
        $total_harga = 0;
        $layanan_names = [];
        
        foreach ($layanan_ids as $lid) {
            $layanan = $db->prepare("SELECT id, nama, harga_min, harga_max FROM layanan WHERE id=?");
            $layanan->execute([$lid]);
            $l = $layanan->fetch();
            
            if ($l) {
                $harga = (int)$l['harga_min'];
                $db->prepare("
                    INSERT INTO booking_layanan (booking_id, layanan_id, harga_saat_booking)
                    VALUES (?, ?, ?)
                ")->execute([$booking_id, $lid, $harga]);
                
                $total_harga += $harga;
                $layanan_names[] = $l['nama'];
            }
        }

        $db->commit();
        jsonResponse(true, 'Booking berhasil dibuat!', [
            'kode' => $kode, 
            'total_harga' => $total_harga,
            'layanan' => $layanan_names
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

// ─────────────────────────────────────────────────────────────
//  ADMIN: DAFTAR BOOKING (with multiple services support)
// ─────────────────────────────────────────────────────────────
if ($action === 'get_bookings') {
    $status  = $_GET['status']  ?? 'all';
    $cari    = '%' . ($_GET['cari'] ?? '') . '%';
    $tanggal = $_GET['tanggal'] ?? '';

    $where = "WHERE 1=1";
    $params = [];

    if ($status !== 'all') {
        $where .= " AND b.status = ?";
        $params[] = $status;
    }
    if ($_GET['cari'] ?? '') {
        $where .= " AND (p.nama LIKE ? OR l.nama LIKE ? OR b.kode LIKE ?)";
        $params = array_merge($params, [$cari, $cari, $cari]);
    }
    if ($tanggal) {
        $where .= " AND b.tanggal = ?";
        $params[] = $tanggal;
    }

    // Get bookings with aggregated services
    $rows = $db->prepare("
        SELECT b.id, b.kode, b.tanggal, b.jam, b.catatan, b.status, b.dibuat,
               p.nama AS pelanggan, p.telepon,
               STRING_AGG(DISTINCT l.nama, ', ') AS layanan,
               STRING_AGG(DISTINCT l.id, ', ') AS layanan_ids,
               SUM(bl.harga_saat_booking) AS total_harga
        FROM booking b
        JOIN pelanggan p ON p.id = b.pelanggan_id
        LEFT JOIN booking_layanan bl ON bl.booking_id = b.id
        LEFT JOIN layanan l ON l.id = bl.layanan_id
        $where
        GROUP BY b.id
        ORDER BY b.tanggal ASC, b.jam ASC
    ");
    $rows->execute($params);
    $data = $rows->fetchAll();

    foreach ($data as &$r) {
        $r['harga_display'] = 'Rp ' . number_format((int)$r['total_harga'], 0, ',', '.');
        $r['tanggal_fmt']   = date('d M Y', strtotime($r['tanggal']));
        $r['jam_fmt']       = substr($r['jam'], 0, 5);
        $r['layanan_list']  = explode(', ', $r['layanan']);
    }
    jsonResponse(true, '', $data);
}

// ─────────────────────────────────────────────────────────────
//  ADMIN: UPDATE STATUS BOOKING
// ─────────────────────────────────────────────────────────────
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id']     ?? 0);
    $status = $_POST['status'] ?? '';
    $valid  = ['pending','confirmed','selesai','batal'];

    if (!$id || !in_array($status, $valid))
        jsonResponse(false, 'Data tidak valid.');

    $db->prepare("UPDATE booking SET status=? WHERE id=?")->execute([$status, $id]);
    jsonResponse(true, 'Status diperbarui.');
}

// ─────────────────────────────────────────────────────────────
//  ADMIN: STATISTIK DASHBOARD
// ─────────────────────────────────────────────────────────────
if ($action === 'get_stats') {
    $today = date('Y-m-d');

    $stats = [];
    $stats['total']     = (int)$db->query("SELECT COUNT(*) FROM booking")->fetchColumn();
    $stats['pending']   = (int)$db->query("SELECT COUNT(*) FROM booking WHERE status='pending'")->fetchColumn();
    $stats['today']     = (int)$db->query("SELECT COUNT(*) FROM booking WHERE tanggal='$today' AND status!='batal'")->fetchColumn();
    $stats['selesai']   = (int)$db->query("SELECT COUNT(*) FROM booking WHERE status='selesai'")->fetchColumn();
    $stats['pelanggan'] = (int)$db->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();

    // Layanan terpopuler (count by booking_layanan)
    $popular = $db->query("
        SELECT l.nama, COUNT(*) AS jml
        FROM booking_layanan bl 
        JOIN booking b ON b.id = bl.booking_id
        JOIN layanan l ON l.id = bl.layanan_id
        WHERE b.status != 'batal'
        GROUP BY l.id ORDER BY jml DESC LIMIT 5
    ")->fetchAll();

    // Booking terbaru
    $recent = $db->query("
        SELECT b.kode, b.status, p.nama AS pelanggan, 
               STRING_AGG(l.nama SEPARATOR ', ') AS layanan, 
               b.tanggal, b.jam
        FROM booking b
        JOIN pelanggan p ON p.id=b.pelanggan_id
        LEFT JOIN booking_layanan bl ON bl.booking_id = b.id
        LEFT JOIN layanan l ON l.id = bl.layanan_id
        GROUP BY b.id
        ORDER BY b.dibuat DESC LIMIT 6
    ")->fetchAll();

    foreach ($recent as &$r) {
        $r['tanggal_fmt'] = date('d M', strtotime($r['tanggal']));
        $r['jam_fmt']     = substr($r['jam'], 0, 5);
    }

    jsonResponse(true, '', compact('stats', 'popular', 'recent'));
}

// ─────────────────────────────────────────────────────────────
//  ADMIN: JADWAL MINGGUAN
// ─────────────────────────────────────────────────────────────
if ($action === 'get_jadwal') {
    $mulai = $_GET['mulai'] ?? date('Y-m-d');
    $akhir = date('Y-m-d', strtotime($mulai . ' +6 days'));

    $rows = $db->prepare("
        SELECT b.tanggal, b.jam, b.status, b.kode,
               p.nama AS pelanggan, p.telepon,
               STRING_AGG(l.nama SEPARATOR ', ') AS layanan
        FROM booking b
        JOIN pelanggan p ON p.id=b.pelanggan_id
        LEFT JOIN booking_layanan bl ON bl.booking_id = b.id
        LEFT JOIN layanan l ON l.id = bl.layanan_id
        WHERE b.tanggal BETWEEN ? AND ? AND b.status != 'batal'
        GROUP BY b.id
        ORDER BY b.tanggal, b.jam
    ");
    $rows->execute([$mulai, $akhir]);
    $data = $rows->fetchAll();

    foreach ($data as &$r) {
        $r['jam_fmt'] = substr($r['jam'], 0, 5);
        $r['layanan_list'] = explode(', ', $r['layanan']);
    }
    jsonResponse(true, '', $data);
}

// ─────────────────────────────────────────────────────────────
//  ADMIN: DATA PELANGGAN
// ─────────────────────────────────────────────────────────────
if ($action === 'get_pelanggan') {
    $cari = '%' . ($_GET['cari'] ?? '') . '%';
    $rows = $db->prepare("
        SELECT p.*,
               COUNT(DISTINCT b.id)                    AS total_booking,
               SUM(CASE WHEN b.status='selesai' THEN 1 ELSE 0 END) AS total_selesai,
               MAX(b.tanggal)                          AS terakhir_kunjungan,
               (SELECT l2.nama FROM booking b2 
                JOIN booking_layanan bl2 ON bl2.booking_id = b2.id
                JOIN layanan l2 ON l2.id = bl2.layanan_id 
                WHERE b2.pelanggan_id=p.id 
                ORDER BY b2.dibuat DESC LIMIT 1) AS layanan_terakhir
        FROM pelanggan p
        LEFT JOIN booking b ON b.pelanggan_id = p.id AND b.status != 'batal'
        WHERE p.nama LIKE ? OR p.telepon LIKE ?
        GROUP BY p.id
        ORDER BY total_booking DESC
    ");
    $rows->execute([$cari, $cari]);
    $data = $rows->fetchAll();

    foreach ($data as &$r) {
        $r['terakhir_fmt'] = $r['terakhir_kunjungan'] ? date('d M Y', strtotime($r['terakhir_kunjungan'])) : '-';
    }
    jsonResponse(true, '', $data);
}

// ─────────────────────────────────────────────────────────────
//  ADMIN: RIWAYAT PELANGGAN
// ─────────────────────────────────────────────────────────────
if ($action === 'riwayat_pelanggan') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(false, 'ID tidak valid');

    $pelanggan = $db->prepare("SELECT * FROM pelanggan WHERE id=?");
    $pelanggan->execute([$id]);
    $p = $pelanggan->fetch();

    $riwayat = $db->prepare("
        SELECT b.kode, b.tanggal, b.jam, b.status, b.catatan,
               STRING_AGG(l.nama SEPARATOR ', ') AS layanan,
               SUM(bl.harga_saat_booking) AS total_harga
        FROM booking b 
        LEFT JOIN booking_layanan bl ON bl.booking_id = b.id
        LEFT JOIN layanan l ON l.id = bl.layanan_id
        WHERE b.pelanggan_id=?
        GROUP BY b.id
        ORDER BY b.tanggal DESC, b.jam DESC
    ");
    $riwayat->execute([$id]);
    $rw = $riwayat->fetchAll();

    foreach ($rw as &$r) {
        $r['tanggal_fmt']   = date('d M Y', strtotime($r['tanggal']));
        $r['jam_fmt']       = substr($r['jam'], 0, 5);
        $r['harga_display'] = 'Rp ' . number_format((int)$r['total_harga'], 0, ',', '.');
    }

    jsonResponse(true, '', ['pelanggan' => $p, 'riwayat' => $rw]);
}

// ─────────────────────────────────────────────────────────────
//  ADMIN: CEK NOTIFIKASI BOOKING BARU (POLLING)
// ─────────────────────────────────────────────────────────────
if ($action === 'cek_notif') {
    $last_id = (int)($_GET['last_id'] ?? 0);
    
    // Ambil booking yang ID-nya lebih besar dari ID terakhir yang diketahui admin
    $stmt = $db->prepare("
        SELECT b.id, b.kode, p.nama AS pelanggan 
        FROM booking b 
        JOIN pelanggan p ON p.id = b.pelanggan_id 
        WHERE b.id > ? 
        ORDER BY b.id ASC
    ");
    $stmt->execute([$last_id]);
    $new_bookings = $stmt->fetchAll();
    
    $new_last_id = $last_id;
    if (count($new_bookings) > 0) {
        $new_last_id = end($new_bookings)['id']; // Update ke ID paling baru
    } else {
        // Jika first load (last_id = 0), ambil ID paling maksimal saat ini agar tidak spam notif lama
        if ($last_id === 0) {
            $new_last_id = (int)$db->query("SELECT MAX(id) FROM booking")->fetchColumn();
        }
    }
    
    jsonResponse(true, '', [
        'new_bookings' => $new_bookings, 
        'last_id' => $new_last_id
    ]);
}

jsonResponse(false, 'Action tidak dikenal: ' . $action);