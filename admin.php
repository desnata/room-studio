<?php
// admin.php — Profesional Admin Dashboard (Black & Pink Logo Theme)
require_once 'config.php';
requireAdminLogin();
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Dashboard | Room Studio</title>
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  /* TEMA HITAM & PINK (Sesuai Logo & Index) */
  --bg-app: #fdf8f9; /* Pink sangat muda untuk area konten */
  --bg-sidebar: #0a0a0a; /* Hitam pekat untuk Sidebar agar JELAS */
  
  --text-main: #1a1a1a;
  --text-muted: #6b6b6b;
  --border: #ebebeb;
  --border-dark: #222222; /* Border khusus area gelap */
  
  --primary: #e6a8b1; /* Pink pastel/Rose gold */
  --primary-hover: #d68f9a;
  --primary-soft: rgba(230, 168, 177, 0.15);
  
  --white: #ffffff;
  --radius: 12px;
  --shadow: 0 8px 25px rgba(0,0,0,0.05);
}
body{font-family:'Jost',sans-serif;background:var(--bg-app);color:var(--text-main);display:flex;height:100vh;overflow:hidden;font-weight:300;position:relative;}

/* ── SIDEBAR & OVERLAY (TEMA GELAP) ── */
.sidebar{width:260px;background:var(--bg-sidebar);display:flex;flex-direction:column;border-right:1px solid var(--border-dark);flex-shrink:0;z-index:1000;transition:transform 0.3s ease;}
.sidebar-header{padding:1.5rem;border-bottom:1px solid var(--border-dark);display:flex;align-items:center;justify-content:space-between;gap:1rem}
.sidebar-brand-group{display:flex;align-items:center;gap:1rem;}
.sidebar-logo{width:40px;height:40px;background:transparent;border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.sidebar-brand{font-weight:500;font-size:1.2rem;color:var(--white); letter-spacing: 0.5px;}
.close-sidebar{display:none;background:none;border:none;font-size:1.5rem;color:#aaa;cursor:pointer;}

.sidebar-nav{flex:1;padding:1.5rem 1rem;overflow-y:auto}
.nav-section{font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:#777;margin-bottom:0.5rem;padding:0 0.75rem;font-weight:500}
.nav-item{display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;color:#aaa;text-decoration:none;border-radius:8px;margin-bottom:0.3rem;cursor:pointer;transition:all 0.2s;font-weight:400}
.nav-item:hover{background:rgba(230, 168, 177, 0.1);color:var(--primary)}
.nav-item.active{background:var(--primary);color:var(--bg-sidebar);font-weight:600;box-shadow:0 4px 12px rgba(230, 168, 177, 0.2)}

.sidebar-footer{padding:1.2rem 1rem;border-top:1px solid var(--border-dark)}
.user-info{display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;padding:0 0.5rem}
.user-avatar{width:36px;height:36px;background:rgba(230, 168, 177, 0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.9rem;color:var(--primary);font-weight:600}
.logout-btn{width:100%;padding:0.7rem;background:transparent;color:var(--primary);border:1px solid var(--primary);border-radius:20px;cursor:pointer;font-family:inherit;font-size:0.85rem;transition:all 0.2s; font-weight:500;}
.logout-btn:hover{background:var(--primary);color:var(--bg-sidebar);}

/* Overlay Transparan untuk Mobile */
.sidebar-overlay{position:fixed;inset:0;background:rgba(10, 10, 10, 0.6);backdrop-filter:blur(2px);z-index:999;display:none;opacity:0;transition:opacity 0.3s;}
.sidebar-overlay.show{display:block;opacity:1;}

/* ── MAIN CONTENT ── */
.main-wrapper{flex:1;display:flex;flex-direction:column;overflow:hidden;width:100%;}
.topbar{height:70px;background:var(--white);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 2rem;flex-shrink:0}
.topbar-left{display:flex;align-items:center;gap:1rem;}
.menu-toggle{display:none;background:none;border:none;font-size:1.6rem;color:var(--text-main);cursor:pointer;}
.topbar-title{font-size:1.3rem;font-weight:600;font-family:serif;}
.topbar-right{font-size:0.85rem;color:var(--text-muted)}

.content-area{flex:1;padding:2rem;overflow-y:auto;overflow-x:hidden;}
.panel{display:none}
.panel.active{display:block}

/* ── STATS CARDS ── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem;margin-bottom:2.5rem}
.stat-card{background:var(--white);padding:1.5rem;border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow)}
.stat-title{font-size:0.85rem;color:var(--text-muted);font-weight:500;margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.5px}
.stat-value{font-size:2.2rem;font-weight:500;color:var(--bg-sidebar);font-family:serif;}

/* ── TABLES & CARDS ── */
.card{background:var(--white);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1.5rem;}
.card-header{padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem}
.table-responsive{overflow-x:auto;width:100%;}
table{width:100%;border-collapse:collapse;min-width:600px;}
th{padding:1rem 1.5rem;text-align:left;font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);font-weight:600;border-bottom:1px solid var(--border);background:var(--bg-app);letter-spacing:0.5px;white-space:nowrap;}
td{padding:1rem 1.5rem;font-size:0.9rem;border-bottom:1px solid var(--border);color:var(--text-main);vertical-align:middle}
tr:hover td{background:var(--bg-app)}

/* ── INPUTS & BADGES ── */
.input-search{padding:0.6rem 1rem;border:1px solid var(--border);border-radius:8px;font-size:0.85rem;outline:none;font-family:inherit;background:var(--bg-app);width:100%;max-width:300px;}
.input-search:focus{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft);background:var(--white)}
.filter-group{display:flex;gap:0.5rem;flex-wrap:wrap;}
.btn-filter{padding:0.5rem 1rem;border:1px solid var(--border);background:var(--white);border-radius:20px;font-size:0.8rem;cursor:pointer;color:var(--text-muted);font-family:inherit;transition:all 0.2s}
.btn-filter.active{background:var(--bg-sidebar);color:var(--white);border-color:var(--bg-sidebar)}

.badge{display:inline-block;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:500;white-space:nowrap;}
.badge-pending{background:#fff3cd;color:#856404;border:1px solid #ffeeba}
.badge-confirmed{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
.badge-selesai{background:#e2e3e5;color:#383d41;border:1px solid #d6d8db}
.badge-batal{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
.badge-svc{background:var(--primary-soft);border:1px solid rgba(230, 168, 177, 0.3);padding:0.2rem 0.6rem;border-radius:6px;font-size:0.75rem;color:var(--bg-sidebar);margin-right:4px;display:inline-block;margin-bottom:4px;white-space:nowrap;font-weight:500;}

/* ── ACTIONS ── */
.action-btns{display:flex;gap:0.5rem}
.btn{padding:0.4rem 1rem;border-radius:20px;font-size:0.85rem;font-weight:500;cursor:pointer;border:1px solid transparent;font-family:inherit;transition:all 0.2s;white-space:nowrap;}
.btn-detail{background:transparent;border-color:var(--border);color:var(--text-main)}
.btn-detail:hover{border-color:var(--primary);color:var(--bg-sidebar);background:var(--primary-soft);}
.btn-success{background:#4a8a5f;color:#fff}
.btn-success:hover{background:#3b704c}
.btn-danger{background:#d9534f;color:#fff}
.btn-danger:hover{background:#c9302c}

/* ── SCHEDULE SPECIFIC ── */
.sched-day{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:1.5rem;overflow:hidden;box-shadow:var(--shadow)}
.sched-header{background:var(--bg-app);padding:1rem 1.5rem;font-weight:600;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;color:var(--text-main);flex-wrap:wrap;gap:0.5rem;}
.sched-slot{display:flex;align-items:center;padding:1rem 1.5rem;border-bottom:1px solid var(--border);gap:1.5rem;flex-wrap:wrap;}
.sched-slot:last-child{border-bottom:none}
.sched-time{font-weight:600;color:var(--text-muted);font-size:0.95rem;width:60px}
.sched-info{flex:1;min-width:150px;}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(10, 10, 10, 0.6);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:9999;padding:1rem;}
.modal-overlay.show{display:flex}
.modal{background:var(--white);width:100%;max-width:500px;border-radius:var(--radius);box-shadow:0 20px 40px rgba(0,0,0,0.2);overflow:hidden;border:1px solid var(--border);display:flex;flex-direction:column;max-height:90vh;}
.modal-header{padding:1.2rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.modal-title{font-weight:600;font-size:1.2rem;font-family:serif;}
.modal-body{padding:1.5rem;overflow-y:auto;}
.modal-footer{padding:1.2rem 1.5rem;border-top:1px solid var(--border);background:var(--bg-app);display:flex;justify-content:flex-end;gap:0.8rem;flex-wrap:wrap;}
.detail-row{display:flex;padding:0.7rem 0;border-bottom:1px dashed var(--border);flex-wrap:wrap;gap:0.5rem;}
.detail-lbl{width:120px;color:var(--text-muted);font-size:0.85rem;font-weight:600}
.detail-val{flex:1;font-size:0.9rem;}

/* ── TOAST ── */
.toast{position:fixed;bottom:2rem;right:2rem;background:var(--primary);color:var(--bg-sidebar);padding:1rem 1.5rem;border-radius:8px;font-size:0.9rem;font-weight:500;transform:translateY(100px);opacity:0;transition:all 0.3s;z-index:10000;box-shadow:var(--shadow)}
.toast.show{transform:translateY(0);opacity:1}

/* ========================================================= */
/* ── RESPONSIVE MOBILE CSS ── */
/* ========================================================= */
@media (max-width: 992px) {
  .dashboard-grid-container {
    grid-template-columns: 1fr !important;
  }
}

@media (max-width: 768px) {
  .menu-toggle { display: block; }
  .close-sidebar { display: block; }
  .topbar { padding: 0 1.2rem; }
  .topbar-right { display: none; }
  
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    transform: translateX(-100%);
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
  }
  
  .sidebar.open {
    transform: translateX(0);
  }

  .content-area { padding: 1.2rem; }
  .stats-grid { grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
  .card-header { flex-direction: column; align-items: stretch; }
  .input-search { max-width: 100%; }

  .panel-schedule-controls { flex-direction: column; gap: 1rem; align-items: stretch !important; }
  .panel-schedule-controls > div:last-child { justify-content: space-between; }
}

@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }
  .sched-slot { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
  .sched-time { margin-bottom: 0.2rem; }
}
</style>
</head>
<body>

<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-brand-group">
      <div class="sidebar-logo">
        <img src="image/logo.jpeg" alt="Room Studio Logo" style="width: 100%; height: 100%; object-fit: cover;" />
      </div>
      <div class="sidebar-brand">Room Studio</div>
    </div>
    <button class="close-sidebar" onclick="toggleSidebar()">&times;</button>
  </div>
  <div class="sidebar-nav">
    <div class="nav-section">Main Menu</div>
    <div class="nav-item active" onclick="showPanel('dashboard',this)">📊 Dashboard</div>
    <div class="nav-item" onclick="showPanel('bookings',this)">📋 Data Booking</div>
    <div class="nav-item" onclick="showPanel('schedule',this)">📅 Jadwal Kunjungan</div>
    <div class="nav-item" onclick="showPanel('customers',this)">👥 Data Pelanggan</div>
  </div>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?></div>
      <div style="font-size:0.85rem">
        <div style="font-weight:500;color:var(--white)">Administrator</div>
        <a href="index.php" target="_blank" style="color:var(--primary);text-decoration:none;font-size:0.75rem">Lihat Website ↗</a>
      </div>
    </div>
    <form method="POST" action="logout.php" style="margin:0">
      <button type="submit" class="logout-btn">Keluar (Logout)</button>
    </form>
  </div>
</div>

<div class="main-wrapper">
  <div class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
      <div class="topbar-title" id="topbar-title">Dashboard Overview</div>
    </div>
    <div class="topbar-right" id="topbar-date"></div>
  </div>

  <div class="content-area">

    <div class="panel active" id="panel-dashboard">
      <div class="stats-grid" id="dash-stats"></div>
      <div class="dashboard-grid-container" style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
        <div class="card">
          <div class="card-header"><span style="font-weight:600">Booking Terbaru</span></div>
          <div class="table-responsive">
            <table>
              <tbody id="dash-recent"></tbody>
            </table>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><span style="font-weight:600">Layanan Paling Sering Dipesan</span></div>
          <div class="table-responsive">
            <table>
              <tbody id="dash-popular"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="panel" id="panel-bookings">
      <div class="card">
        <div class="card-header">
          <input type="text" class="input-search" placeholder="Cari nama, kode..." id="s-booking" oninput="loadBookings()"/>
          <input type="date" class="input-search" id="s-tgl" onchange="loadBookings()"/>
          <div class="filter-group">
            <button class="btn-filter active" onclick="setFilter('all',this)">Semua</button>
            <button class="btn-filter" onclick="setFilter('pending',this)">Pending</button>
            <button class="btn-filter" onclick="setFilter('confirmed',this)">Confirmed</button>
            <button class="btn-filter" onclick="setFilter('selesai',this)">Selesai</button>
          </div>
        </div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Kode</th><th>Pelanggan</th><th>Layanan</th><th>Waktu</th><th>Total</th><th>Status</th><th>Aksi</th>
              </tr>
            </thead>
            <tbody id="booking-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="panel" id="panel-schedule">
      <div class="panel-schedule-controls" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;color:var(--text-muted)">
        <div>Menampilkan jadwal 7 hari ke depan.</div>
        <div style="display:flex;gap:0.5rem;">
          <button class="btn btn-detail" onclick="shiftWeek(-7)">← Prev</button>
          <input type="date" class="input-search" id="week-start" onchange="loadJadwal()" style="padding:0.4rem 1rem; width:auto;"/>
          <button class="btn btn-detail" onclick="shiftWeek(7)">Next →</button>
        </div>
      </div>
      <div id="sched-week"></div>
    </div>

    <div class="panel" id="panel-customers">
      <div class="card">
        <div class="card-header">
          <input type="text" class="input-search" placeholder="Cari nama pelanggan..." id="s-cust" oninput="loadPelanggan()"/>
        </div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr><th>Nama Pelanggan</th><th>No. WhatsApp</th><th>Total Booking</th><th>Kunjungan Terakhir</th><th>Aksi</th></tr>
            </thead>
            <tbody id="cust-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="modal-overlay" id="modal-bg" onclick="if(event.target===this)closeModal()">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="m-title">Detail</div>
      <button style="border:none;background:none;font-size:1.8rem;cursor:pointer;color:var(--text-muted);line-height:1;" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body" id="m-body"></div>
    <div class="modal-footer" id="m-foot"></div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
// --- UTILITIES ---
const $ = id => document.getElementById(id);
let currentFilter = 'all';

function toggleSidebar() {
  $('sidebar').classList.toggle('open');
  $('sidebar-overlay').classList.toggle('show');
}

function showToast(msg) {
  const t=$('toast'); t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'), 3000);
}
function openModal() { $('modal-bg').classList.add('show'); }
function closeModal() { $('modal-bg').classList.remove('show'); }

function statusBadge(s) {
  const m = { pending: ['badge-pending','Menunggu'], confirmed: ['badge-confirmed','Dikonfirmasi'], selesai: ['badge-selesai','Selesai'], batal: ['badge-batal','Batal'] };
  return `<span class="badge ${m[s]?.[0]}">${m[s]?.[1]}</span>`;
}

// --- NAVIGATION ---
const panelTitles = { dashboard:'Dashboard Overview', bookings:'Manajemen Data Booking', schedule:'Jadwal Kunjungan', customers:'Database Pelanggan' };
function showPanel(p, btn) {
  document.querySelectorAll('.panel').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(x=>x.classList.remove('active'));
  $('panel-'+p).classList.add('active');
  btn.classList.add('active');
  $('topbar-title').textContent = panelTitles[p];
  
  if (window.innerWidth <= 768) {
    $('sidebar').classList.remove('open');
    $('sidebar-overlay').classList.remove('show');
  }

  if(p==='dashboard') loadDashboard();
  if(p==='bookings') loadBookings();
  if(p==='schedule') loadJadwal();
  if(p==='customers') loadPelanggan();
}

// --- DASHBOARD ---
async function loadDashboard() {
  const res = await fetch('api.php?action=get_stats');
  const j = await res.json();
  if(!j.success) return;
  const {stats, popular, recent} = j.data;
  $('dash-stats').innerHTML = `
    <div class="stat-card"><div class="stat-title">Total Booking</div><div class="stat-value">${stats.total}</div></div>
    <div class="stat-card"><div class="stat-title">Menunggu Konfirmasi</div><div class="stat-value" style="color:#d9534f">${stats.pending}</div></div>
    <div class="stat-card"><div class="stat-title">Booking Hari Ini</div><div class="stat-value" style="color:var(--bg-sidebar)">${stats.today}</div></div>
    <div class="stat-card"><div class="stat-title">Total Pelanggan</div><div class="stat-value">${stats.pelanggan}</div></div>
  `;
  $('dash-recent').innerHTML = recent.map(b=>`<tr>
    <td><div><strong style="font-weight:600">${b.pelanggan}</strong></div><div style="font-size:0.8rem;color:var(--text-muted)">${b.tanggal_fmt} | ${b.jam_fmt}</div></td>
    <td style="text-align:right">${statusBadge(b.status)}</td>
  </tr>`).join('');
  $('dash-popular').innerHTML = popular.map(p=>`<tr>
    <td><div style="font-size:0.9rem;font-weight:500">${p.nama}</div></td>
    <td style="text-align:right;font-weight:600;color:var(--bg-sidebar)">${p.jml}x dipesan</td>
  </tr>`).join('');
}

// --- BOOKINGS ---
async function loadBookings() {
  const cari = $('s-booking').value; const tgl = $('s-tgl').value;
  const res = await fetch(`api.php?action=get_bookings&status=${currentFilter}&cari=${encodeURIComponent(cari)}&tanggal=${tgl}`);
  const j = await res.json();
  $('booking-tbody').innerHTML = j.data.map(b=>`<tr>
    <td style="font-size:0.85rem;color:var(--text-muted);font-weight:500;">${b.kode}</td>
    <td><div style="font-weight:600">${b.pelanggan}</div><div style="font-size:0.8rem;color:var(--text-muted)">${b.telepon}</div></td>
    <td>${(b.layanan_list||[b.layanan]).map(l=>`<span class="badge-svc">${l}</span>`).join('')}</td>
    <td>${b.tanggal_fmt}<br><span style="font-size:0.85rem;color:var(--text-muted);font-weight:500;">${b.jam_fmt} WITA</span></td>
    <td style="font-weight:600">${b.total_harga ? 'Rp '+parseInt(b.total_harga).toLocaleString('id-ID') : b.harga_display}</td>
    <td>${statusBadge(b.status)}</td>
    <td>
      <div class="action-btns">
        <button class="btn btn-detail" onclick="detailBooking(${b.id})">Detail</button>
        ${b.status==='pending' ? `<button class="btn btn-success" onclick="updateStatus(${b.id},'confirmed')">✓</button>` : ''}
      </div>
    </td>
  </tr>`).join('');
}
function setFilter(f, btn){ currentFilter=f; document.querySelectorAll('.btn-filter').forEach(b=>b.classList.remove('active')); btn.classList.add('active'); loadBookings(); }

async function updateStatus(id, status) {
  const fd = new FormData(); fd.append('action', 'update_status'); fd.append('id', id); fd.append('status', status);
  const res = await fetch('api.php', {method:'POST', body:fd});
  if((await res.json()).success) { showToast('Status diupdate'); loadBookings(); loadDashboard(); closeModal(); }
}

async function detailBooking(id) {
  const j = await (await fetch(`api.php?action=get_bookings&status=all`)).json();
  const b = j.data.find(x=>x.id==id);
  $('m-title').textContent = 'Reservasi ' + b.kode;
  $('m-body').innerHTML = `
    <div class="detail-row"><div class="detail-lbl">Pelanggan</div><div class="detail-val" style="font-weight:500;">${b.pelanggan} (${b.telepon})</div></div>
    <div class="detail-row"><div class="detail-lbl">Layanan</div><div class="detail-val">${b.layanan_list?b.layanan_list.join('<br>'):b.layanan}</div></div>
    <div class="detail-row"><div class="detail-lbl">Jadwal</div><div class="detail-val" style="font-weight:500;">${b.tanggal_fmt} pukul ${b.jam_fmt}</div></div>
    <div class="detail-row"><div class="detail-lbl">Catatan</div><div class="detail-val">${b.catatan||'-'}</div></div>
    <div class="detail-row"><div class="detail-lbl">Status</div><div class="detail-val">${statusBadge(b.status)}</div></div>
  `;
  let foot = `<button class="btn btn-detail" onclick="closeModal()">Tutup</button>`;
  if(b.status==='pending') foot += `<button class="btn btn-danger" onclick="updateStatus(${b.id},'batal')">Batalkan</button> <button class="btn btn-success" onclick="updateStatus(${b.id},'confirmed')">Konfirmasi Terima</button>`;
  if(b.status==='confirmed') foot += `<button class="btn btn-success" onclick="updateStatus(${b.id},'selesai')">Tandai Selesai</button>`;
  $('m-foot').innerHTML = foot;
  openModal();
}

// --- SCHEDULE ---
function shiftWeek(d) {
  const dt = new Date($('week-start').value || new Date());
  dt.setDate(dt.getDate() + d);
  $('week-start').value = dt.toISOString().split('T')[0];
  loadJadwal();
}
async function loadJadwal() {
  const mulai = $('week-start').value || new Date().toISOString().split('T')[0];
  const j = await (await fetch(`api.php?action=get_jadwal&mulai=${mulai}`)).json();
  const times = ['10:00','10:30','11:00','11:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30'];
  let html = '';
  for(let i=0; i<7; i++) {
    const d = new Date(mulai+'T00:00:00'); d.setDate(d.getDate()+i);
    const ds = d.toISOString().split('T')[0];
    const dayData = j.data.filter(b=>b.tanggal===ds);
    html += `<div class="sched-day"><div class="sched-header"><div>${d.toLocaleDateString('id-ID',{weekday:'long', day:'numeric', month:'long'})}</div><div style="font-size:0.85rem;color:var(--bg-sidebar)">${dayData.length} slot terisi</div></div>`;
    times.forEach(t => {
      const bk = dayData.find(b=>b.jam_fmt===t);
      if(bk) html += `<div class="sched-slot" style="background:${bk.status==='confirmed'?'var(--bg-app)':'#fff'}"><div class="sched-time">${t}</div><div class="sched-info"><div style="font-weight:600">${bk.pelanggan}</div><div style="font-size:0.85rem;color:var(--text-muted)">${bk.layanan_list?bk.layanan_list.join(', '):bk.layanan}</div></div><div>${statusBadge(bk.status)}</div></div>`;
    });
    html += `</div>`;
  }
  $('sched-week').innerHTML = html;
}

// --- CUSTOMERS ---
async function loadPelanggan() {
  const cari = $('s-cust').value;
  const j = await (await fetch(`api.php?action=get_pelanggan&cari=${encodeURIComponent(cari)}`)).json();
  $('cust-tbody').innerHTML = j.data.map(c=>`<tr>
    <td style="font-weight:600">${c.nama}</td>
    <td>${c.telepon}</td>
    <td><span class="badge" style="background:var(--primary-soft);color:var(--bg-sidebar);font-weight:600;">${c.total_booking} Booking</span></td>
    <td>${c.terakhir_fmt}</td>
    <td><button class="btn btn-detail" onclick="riwayatPelanggan(${c.id})">Lihat Riwayat</button></td>
  </tr>`).join('');
}
async function riwayatPelanggan(id) {
  const j = await (await fetch(`api.php?action=riwayat_pelanggan&id=${id}`)).json();
  $('m-title').textContent = 'Riwayat ' + j.data.pelanggan.nama;
  $('m-body').innerHTML = j.data.riwayat.map(r=>`<div style="padding:1rem;border:1px solid var(--border);border-radius:8px;margin-bottom:0.8rem;background:var(--bg-app)">
    <div style="display:flex;justify-content:space-between;margin-bottom:0.4rem;flex-wrap:wrap;gap:0.5rem;"><strong style="font-weight:600">${r.tanggal_fmt}</strong> ${statusBadge(r.status)}</div>
    <div style="font-size:0.9rem;color:var(--text-muted)">${r.layanan}</div>
  </div>`).join('') || 'Belum ada riwayat reservasi.';
  $('m-foot').innerHTML = `<button class="btn btn-detail" onclick="closeModal()">Tutup</button>`;
  openModal();
}

// --- SISTEM NOTIFIKASI REAL-TIME ---
let lastBookingId = 0;

async function initNotif() {
  try {
    const res = await fetch('api.php?action=cek_notif&last_id=0',{ cache: 'no-store' });
    const j = await res.json();
    if (j.success) {
      lastBookingId = j.data.last_id || 0;
      setInterval(pollNotif, 10000); 
    }
  } catch (e) { console.error('Notif init error:', e); }
}

async function pollNotif() {
  if (lastBookingId === 0) return; 
  try {
    const res = await fetch(`api.php?action=cek_notif&last_id=${lastBookingId}`, { cache: 'no-store' });
    const j = await res.json();
    
    if (j.success && j.data.new_bookings && j.data.new_bookings.length > 0) {
      lastBookingId = j.data.last_id; 
      
      j.data.new_bookings.forEach(b => {
        showToast(`🔔 BOOKING BARU: ${b.pelanggan} (${b.kode})`);
        triggerBrowserNotif(b.kode, b.pelanggan);
      });

      loadDashboard();
      if (document.getElementById('panel-bookings').classList.contains('active')) {
        loadBookings();
      }
    }
  } catch (e) { console.error('Polling error:', e); }
}

function triggerBrowserNotif(kode, pelanggan) {
  if (!("Notification" in window)) return; 
  
  const title = "Ada Booking Baru! 🔔";
  const options = {
    body: `Pelanggan: ${pelanggan}\nKode: ${kode}\nSegera cek panel admin.`,
    icon: "https://cdn-icons-png.flaticon.com/512/3602/3602145.png" 
  };

  if (Notification.permission === "granted") {
    new Notification(title, options);
  } else if (Notification.permission !== "denied") {
    Notification.requestPermission().then(permission => {
      if (permission === "granted") {
        new Notification(title, options);
      }
    });
  }
}
// --- INIT ---
document.addEventListener('DOMContentLoaded', () => {
  $('topbar-date').textContent = new Date().toLocaleDateString('id-ID', {weekday:'long',day:'numeric',month:'long',year:'numeric'});
  if($('week-start')) $('week-start').value = new Date().toISOString().split('T')[0];
  loadDashboard();
  
  if ("Notification" in window && Notification.permission !== "denied") Notification.requestPermission(); 
  initNotif(); 
});
</script>
</body>
</html>