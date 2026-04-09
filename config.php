<?php
// config.php — Konfigurasi Database Room Studio
session_start();

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        // --- MASUKKAN CREDENTIAL SUPABASE ANDA DI SINI ---
        // Contoh DSN PostgreSQL:
        $host = 'db.vhsrmfmvblfqolhwgwbt.supabase.co';
        $port = '5432'; // Ganti dengan Host Supabase Anda
        $db   = 'postgres'; 
        $user = 'postgres'; // Ganti dengan User Supabase Anda
        $pass = 'room-studio26'; // Password yang dibuat di Langkah 1

        $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
        // -------------------------------------------------

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

function jsonResponse(bool $success, string $message = '', mixed $data = null): void {
    header('Content-Type: application/json');
    echo json_encode(compact('success', 'message', 'data'));
    exit;
}

function generateKode(PDO $db): string {
    $row = $db->query("SELECT MAX(id) AS max_id FROM booking")->fetch();
    $next = ($row['max_id'] ?? 0) + 1;
    return 'RS-' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

function formatHarga(int $min, ?int $max, ?string $satuan): string {
    $fmt = fn(int $n) => 'Rp ' . number_format($n, 0, ',', '.');
    if ($satuan === '(add)') return 'add ' . $fmt($min);
    $base = $max ? $fmt($min) . ' – ' . $fmt($max) : $fmt($min);
    return $satuan ? $base . $satuan : $base;
}

function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}