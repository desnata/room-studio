// INISIALISASI SUPABASE
const db = window.supabase.createClient('https://vhsrmfmvblfqolhwgwbt.supabase.co', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZoc3JtZm12YmxmcW9saHdnd2J0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzU2ODQ4NjAsImV4cCI6MjA5MTI2MDg2MH0.jxVo-xAjpEyaqGiROMlWbdwmCga6GKNz_rnNrRYPpWQ');

// CEK LOGIN
async function checkAuth() {
  const { data: { session } } = await db.auth.getSession();
  if (!session) { window.location.href = 'login.html'; return; }
  document.getElementById('admin-email').textContent = session.user.email.split('@')[0];
  document.getElementById('admin-avatar').textContent = session.user.email.charAt(0).toUpperCase();
}
checkAuth();

async function logout() {
  await db.auth.signOut();
  window.location.href = 'login.html';
}

// UTILS
const $ = id => document.getElementById(id);
let currentFilter = 'all';
let allBookingsCache = []; 

function toggleSidebar() { $('sidebar').classList.toggle('open'); $('sidebar-overlay').classList.toggle('show'); }
function showToast(msg) { const t=$('toast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'), 3000); }
function openModal() { $('modal-bg').classList.add('show'); }
function closeModal() { $('modal-bg').classList.remove('show'); }
function formatRp(angka) { return 'Rp ' + parseInt(angka).toLocaleString('id-ID'); }
function formatDate(dateStr) { return new Date(dateStr).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'}); }

function statusBadge(s) {
  const m = { pending: ['badge-pending','Menunggu'], confirmed: ['badge-confirmed','Dikonfirmasi'], selesai: ['badge-selesai','Selesai'], batal: ['badge-batal','Batal'] };
  return `<span class="badge ${m[s]?.[0]}">${m[s]?.[1]}</span>`;
}

const panelTitles = { dashboard:'Dashboard Overview', bookings:'Manajemen Data Booking', schedule:'Jadwal Kunjungan', customers:'Database Pelanggan' };
function showPanel(p, btn) {
  document.querySelectorAll('.panel').forEach(x=>x.classList.remove('active')); document.querySelectorAll('.nav-item').forEach(x=>x.classList.remove('active'));
  $('panel-'+p).classList.add('active'); btn.classList.add('active'); $('topbar-title').textContent = panelTitles[p];
  if (window.innerWidth <= 768) { $('sidebar').classList.remove('open'); $('sidebar-overlay').classList.remove('show'); }
  if(p==='dashboard') loadDashboard(); if(p==='bookings') loadBookings(); if(p==='schedule') loadJadwal(); if(p==='customers') loadPelanggan();
}

function formatBookingData(data) {
  return data.map(b => {
    let totalHarga = 0; let layananList = [];
    if(b.booking_layanan) {
      b.booking_layanan.forEach(bl => { totalHarga += bl.harga_saat_booking; if(bl.layanan) layananList.push(bl.layanan.nama); });
    }
    return { ...b, pelanggan_nama: b.pelanggan?.nama, telepon: b.pelanggan?.telepon, layanan_list: layananList, total_harga: totalHarga, jam_fmt: b.jam.substring(0,5), tanggal_fmt: formatDate(b.tanggal) };
  });
}

// DASHBOARD
async function loadDashboard() {
  const today = new Date().toISOString().split('T')[0];
  
  const { count: tAll } = await db.from('booking').select('*', { count: 'exact', head: true });
  const { count: tPending } = await db.from('booking').select('*', { count: 'exact', head: true }).eq('status', 'pending');
  const { count: tToday } = await db.from('booking').select('*', { count: 'exact', head: true }).eq('tanggal', today).neq('status', 'batal');
  const { count: tCust } = await db.from('pelanggan').select('*', { count: 'exact', head: true });

  // PERBAIKAN WARNA: Mengubah var(--bg-sidebar) menjadi var(--primary-hover) agar terlihat jelas
  $('dash-stats').innerHTML = `
    <div class="stat-card"><div class="stat-title">Total Booking</div><div class="stat-value">${tAll||0}</div></div>
    <div class="stat-card"><div class="stat-title">Menunggu Konfirmasi</div><div class="stat-value" style="color:#d9534f">${tPending||0}</div></div>
    <div class="stat-card"><div class="stat-title">Booking Hari Ini</div><div class="stat-value" style="color:var(--primary-hover)">${tToday||0}</div></div>
    <div class="stat-card"><div class="stat-title">Total Pelanggan</div><div class="stat-value">${tCust||0}</div></div>
  `;

  const { data: recentRaw } = await db.from('booking').select('*, pelanggan(nama), booking_layanan(layanan(nama))').order('dibuat', {ascending: false}).limit(6);
  const recent = formatBookingData(recentRaw || []);
  $('dash-recent').innerHTML = recent.map(b=>`<tr>
    <td><div><strong style="font-weight:600; color:var(--text-main);">${b.pelanggan_nama}</strong></div><div style="font-size:0.8rem;color:var(--text-muted)">${b.tanggal_fmt} | ${b.jam_fmt}</div></td>
    <td style="text-align:right">${statusBadge(b.status)}</td>
  </tr>`).join('');

  const { data: services } = await db.from('layanan').select('*').eq('aktif', true).limit(5);
  $('dash-popular').innerHTML = (services||[]).map(p=>`<tr>
    <td><div style="font-size:0.9rem;font-weight:500">${p.nama}</div></td>
    <td style="text-align:right;font-weight:600;color:var(--primary-hover)">Tersedia</td>
  </tr>`).join('');
}

// DATA BOOKING
async function loadBookings() {
  const cari = $('s-booking').value.toLowerCase(); const tgl = $('s-tgl').value;
  
  let query = db.from('booking').select('*, pelanggan(nama, telepon), booking_layanan(harga_saat_booking, layanan(nama))').order('tanggal', {ascending: false}).order('jam', {ascending: false});
  if (currentFilter !== 'all') query = query.eq('status', currentFilter);
  if (tgl) query = query.eq('tanggal', tgl);

  const { data } = await query;
  let formatted = formatBookingData(data || []);
  
  if (cari) {
    formatted = formatted.filter(b => b.kode.toLowerCase().includes(cari));
  }
  
  allBookingsCache = formatted;

  $('booking-tbody').innerHTML = formatted.map(b=>`<tr>
    <td style="font-size:0.85rem;color:var(--text-muted);font-weight:600;">${b.kode}</td>
    <td><div style="font-weight:600">${b.pelanggan_nama}</div><div style="font-size:0.8rem;color:var(--text-muted)">${b.telepon}</div></td>
    <td>${b.layanan_list.map(l=>`<span class="badge-svc">${l}</span>`).join('')}</td>
    <td>${b.tanggal_fmt}<br><span style="font-size:0.85rem;color:var(--text-muted);font-weight:500;">${b.jam_fmt} WITA</span></td>
    <td style="font-weight:600">${formatRp(b.total_harga)}</td>
    <td>${statusBadge(b.status)}</td>
    <td>
      <div class="action-btns" style="display: flex; gap: 0.5rem; align-items: center;">
        
        <button class="btn btn-detail" onclick="detailBooking(${b.id})">Detail</button>        
        ${b.status==='pending' ? `<button class="btn btn-success" style="padding: 0.4rem 0.6rem; display: flex; align-items: center;" onclick="updateStatus(${b.id},'confirmed')" title="Konfirmasi">✓</button>` : ''}
        <button class="btn btn-danger" style="padding: 0.4rem 0.6rem; display: flex; align-items: center;" onclick="hapusBooking('${b.id}')" title="Hapus Booking">
          <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
        </button>
      </div>
    </td>
  </tr>`).join('');
}

function setFilter(f, btn){ currentFilter=f; document.querySelectorAll('.btn-filter').forEach(b=>b.classList.remove('active')); btn.classList.add('active'); loadBookings(); }

async function updateStatus(id, status) {
  const { error } = await db.from('booking').update({ status: status }).eq('id', id);
  if(!error) { showToast('Status berhasil diupdate'); loadBookings(); loadDashboard(); closeModal(); loadJadwal(); }
}

function detailBooking(id) {
  const b = allBookingsCache.find(x=>x.id==id);
  if(!b) return;
  $('m-title').textContent = 'Reservasi ' + b.kode;
  $('m-body').innerHTML = `
    <div class="detail-row"><div class="detail-lbl">Pelanggan</div><div class="detail-val" style="font-weight:600;">${b.pelanggan_nama} (${b.telepon})</div></div>
    <div class="detail-row"><div class="detail-lbl">Layanan</div><div class="detail-val">${b.layanan_list.join('<br>')}</div></div>
    <div class="detail-row"><div class="detail-lbl">Jadwal</div><div class="detail-val" style="font-weight:600;">${b.tanggal_fmt} pukul ${b.jam_fmt}</div></div>
    <div class="detail-row"><div class="detail-lbl">Catatan</div><div class="detail-val">${b.catatan||'-'}</div></div>
    <div class="detail-row"><div class="detail-lbl">Status</div><div class="detail-val">${statusBadge(b.status)}</div></div>
  `;
  let foot = `<button class="btn btn-detail" onclick="closeModal()">Tutup</button>`;
  if(b.status==='pending') foot += `<button class="btn btn-danger" onclick="updateStatus(${b.id},'batal')">Batalkan</button> <button class="btn btn-success" onclick="updateStatus(${b.id},'confirmed')">Konfirmasi Terima</button>`;
  if(b.status==='confirmed') foot += `<button class="btn btn-success" onclick="updateStatus(${b.id},'selesai')">Tandai Selesai</button>`;
  $('m-foot').innerHTML = foot; openModal();
}

// JADWAL
function shiftWeek(d) { const dt = new Date($('week-start').value || new Date()); dt.setDate(dt.getDate() + d); $('week-start').value = dt.toISOString().split('T')[0]; loadJadwal(); }

async function loadJadwal() {
  const mulai = $('week-start').value || new Date().toISOString().split('T')[0];
  const endD = new Date(mulai); endD.setDate(endD.getDate() + 6);
  const akhir = endD.toISOString().split('T')[0];

  const { data } = await db.from('booking').select('*, pelanggan(nama), booking_layanan(layanan(nama))').gte('tanggal', mulai).lte('tanggal', akhir).neq('status', 'batal');
  const formatData = formatBookingData(data || []);
  const times = ['10:00','10:30','11:00','11:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00'];
  let html = '';
  for(let i=0; i<7; i++) {
    const d = new Date(mulai+'T00:00:00'); d.setDate(d.getDate()+i); const ds = d.toISOString().split('T')[0];
    const dayData = formatData.filter(b=>b.tanggal===ds);
    // PERBAIKAN WARNA: Slot terisi menjadi warna pink utama
    html += `<div class="sched-day"><div class="sched-header"><div>${d.toLocaleDateString('id-ID',{weekday:'long', day:'numeric', month:'long'})}</div><div style="font-size:0.85rem;color:var(--primary-hover)">${dayData.length} slot terisi</div></div>`;
    times.forEach(t => {
      const bk = dayData.find(b=>b.jam_fmt===t);
      if(bk) html += `<div class="sched-slot" style="background:${bk.status==='confirmed'?'var(--primary-soft)':'#fff'}"><div class="sched-time">${t}</div><div class="sched-info"><div style="font-weight:600">${bk.pelanggan_nama}</div><div style="font-size:0.85rem;color:var(--text-muted)">${bk.layanan_list.join(', ')}</div></div><div>${statusBadge(bk.status)}</div></div>`;
    });
    html += `</div>`;
  }
  $('sched-week').innerHTML = html;
}

// PELANGGAN
async function loadPelanggan() {
  const cari = $('s-cust').value.toLowerCase();
  const { data } = await db.from('pelanggan').select('*').order('dibuat', {ascending:false});
  let filtered = data || [];
  if(cari) filtered = filtered.filter(p => p.nama.toLowerCase().includes(cari) || p.telepon.includes(cari));

  // PERBAIKAN WARNA: Mengubah color teks tanggal pada badge menjadi warna gelap (text-main) atau pink (primary-hover)
  $('cust-tbody').innerHTML = filtered.map(c=>`<tr>
    <td style="font-weight:600">${c.nama}</td>
    <td>${c.telepon}</td>
    <td><span class="badge" style="background:var(--primary-soft);color:var(--primary-hover);font-weight:600;">${formatDate(c.dibuat)}</span></td>
    <td>
  <button class="btn btn-danger" style="padding: 0.4rem 0.8rem;" onclick="hapusPelanggan('${c.id}')" title="Hapus Pelanggan">
    <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
  </button>
</td>
  </tr>`).join('');
}

// NOTIFIKASI REAL-TIME
function setupRealtime() {
  db.channel('custom-insert-channel')
    .on('postgres_changes', { event: 'INSERT', schema: 'public', table: 'booking' }, (payload) => {
      showToast(`🔔 BOOKING BARU MASUK: ${payload.new.kode}`);
      loadDashboard();
      if($('panel-bookings').classList.contains('active')) loadBookings();
    })
    .subscribe();
}

// INIT
document.addEventListener('DOMContentLoaded', () => {
  $('topbar-date').textContent = new Date().toLocaleDateString('id-ID', {weekday:'long',day:'numeric',month:'long',year:'numeric'});
  if($('week-start')) $('week-start').value = new Date().toISOString().split('T')[0];
  loadDashboard();
  setupRealtime();
});
// --- FUNGSI HAPUS BOOKING ---
async function hapusBooking(id) {
  // Konfirmasi sebelum menghapus
  const yakin = confirm("Apakah Anda yakin ingin menghapus data booking ini? Data yang dihapus tidak dapat dikembalikan.");
  if (!yakin) return;

  try {
    // Gunakan 'db', BUKAN 'supabase'
    const { error } = await db
      .from('booking') 
      .delete()
      .eq('id', id);  

    if (error) throw error;

    if(typeof showToast === 'function') showToast("Data booking berhasil dihapus!");
    else alert("Data booking berhasil dihapus!");

    loadBookings(); // Refresh tabel
    loadDashboard(); // Refresh angka di dashboard atas
    
  } catch (error) {
    console.error("Error menghapus booking:", error);
    alert("Gagal menghapus data booking. Silakan coba lagi.");
  }
}

// --- FUNGSI HAPUS PELANGGAN ---
async function hapusPelanggan(id) {
  const yakin = confirm("Yakin ingin menghapus pelanggan ini? Pastikan pelanggan ini tidak memiliki data booking yang masih aktif.");
  if (!yakin) return;

  try {
    // Gunakan 'db', BUKAN 'supabase'
    const { error } = await db
      .from('pelanggan') 
      .delete()
      .eq('id', id);

    if (error) throw error;

    if(typeof showToast === 'function') showToast("Data pelanggan berhasil dihapus!");
    else alert("Data pelanggan berhasil dihapus!");

    loadPelanggan(); // Refresh tabel pelanggan
    loadDashboard(); // Refresh angka pelanggan di dashboard
    
  } catch (error) {
    console.error("Error menghapus pelanggan:", error);
    alert("Gagal menghapus data. Jika pelanggan ini memiliki history booking, hapus bookingnya terlebih dahulu.");
  }
}