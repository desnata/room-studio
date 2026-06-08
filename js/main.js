// ==========================================
// LOGIKA UTAMA: HALAMAN RESERVASI PELANGGAN
// ==========================================

// Pengaturan header REST API murni (mengambil kunci anon dari config global)
const SUPABASE_URL = 'https://vhsrmfmvblfqolhwgwbt.supabase.co';
const SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZoc3JtZm12YmxmcW9saHdnd2J0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzU2ODQ4NjAsImV4cCI6MjA5MTI2MDg2MH0.jxVo-xAjpEyaqGiROMlWbdwmCga6GKNz_rnNrRYPpWQ';

const headers = {
  'apikey': SUPABASE_KEY,
  'Authorization': 'Bearer ' + SUPABASE_KEY,
  'Content-Type': 'application/json'
};

let allLayanan = [];
let selectedServices = new Set();
let uniqueCategories = [];

// Kamus Gambar & Deskripsi Katalog Layanan
const sampleImages = {'Eyelash': 'image/Eyelash.webp', 'Nail': 'image/Nail.webp'};
const defaultImg = 'image/Latar.jpg';

const categoryDescriptions = {
  'Eyelash': 'Perawatan bulu mata eksklusif untuk tampilan lentik, bervolume natural, dan tahan lama.',
  'Nail': 'Perawatan esensial dan seni kuku (Manicure/Pedicure, Gel Paint, Extension) untuk kuku cantik yang merefleksikan gaya Anda.',
};

const specificServiceDescriptions = {
  'Natural Lash': 'Pemasangan bulu mata yang terlihat seperti bulu mata asli, sangat natural dan ringan.',
  'Standar Lash': 'Pemasangan bulu mata dengan ketebalan standar untuk tampilan lentik yang pas.',
  'Volume Lash': 'Teknik pemasangan bulu mata bercabang untuk hasil yang lebih penuh, tebal, dan dramatis.',
  'Wispy Lash': 'Gaya pemasangan dengan panjang bulu mata yang bervariasi.',
  'Anime Lash': 'Gaya bulu mata populer yang menyerupai karakter anime.',
  'Cat Eye Lash': 'Pemasangan dengan bulu mata yang lebih panjang di ujung luar mata.',
  'Remove': 'Pelepasan bulu mata extension lama secara aman.',
  'Retouch': 'Pengisian kembali area bulu mata extension yang sudah rontok.',
  'Menicure': 'Perawatan esensial untuk membersihkan kutikula dan kuku tangan.',
  'Pedicure': 'Perawatan relaksasi untuk kaki dan merapikan kuku jari kaki.',
  'Pedicure+Callus': 'Paket pedicure lengkap ditambah perawatan mengangkat kapalan.',
  'Classic Gel Hand': 'Aplikasi pewarnaan kuku dengan cat gel pada tangan.',
  'Classic Gel Feet': 'Aplikasi pewarnaan kuku dengan cat gel pada kaki.',
  'French Nail': 'Gaya kuku klasik dengan ujung kuku berwarna putih.',
  'Ombré Nail': 'Teknik pewarnaan kuku dengan gradasi dua warna.',
  'Cat Eye': 'Teknik pewarnaan gel khusus dengan efek mata kucing.',
  'Acrylic Ext': 'Pemanjangan kuku buatan menggunakan bahan acrylic.',
  'Half Ext': 'Pemanjangan kuku hanya pada bagian setengah ujung kuku.',
  'Full Ext': 'Pemanjangan kuku penuh menutupi seluruh kuku asli.',
  'Remove Gel': 'Pembersihan cat kuku gel lama secara profesional.',
  'Remove Ext': 'Pelepasan kuku sambung (extension) lama.',
  'Art 1pcs': 'Penambahan seni lukis kuku/hiasan untuk 1 jari.',
  'Art 2pcs': 'Penambahan seni lukis kuku/hiasan untuk 2 jari.',
  'Art 4pcs': 'Penambahan seni lukis kuku/hiasan untuk 4 jari.',
  'Overlay': 'Lapisan pelindung tambahan bening di atas kuku asli.',
};

function formatHarga(min, max, satuan) {
  const fmt = n => 'Rp ' + parseInt(n).toLocaleString('id-ID');
  if (satuan === '(add)') return 'add ' + fmt(min);
  let base = max ? fmt(min) + ' – ' + fmt(max) : fmt(min);
  return satuan ? base + ' ' + satuan : base;
}

// 1. Ambil data layanan publik dari Supabase via REST API
async function loadLayanan() {
  try {
    document.getElementById('kat-view').innerHTML = `<p style="text-align:center;">Mengambil data layanan dari server...</p>`;
    
    const resKat = await fetch(`${SUPABASE_URL}/rest/v1/kategori?select=*`, { headers });
    if (!resKat.ok) throw new Error("Gagal mengambil Kategori");
    const katData = await resKat.json();

    const resLay = await fetch(`${SUPABASE_URL}/rest/v1/layanan?select=*`, { headers });
    if (!resLay.ok) throw new Error("Gagal mengambil Layanan");
    const layData = await resLay.json();

    if (layData.length === 0) {
      document.getElementById('kat-view').innerHTML = `<p style="text-align:center;">Tabel layanan di database masih kosong.</p>`;
      return;
    }

    allLayanan = layData
      .filter(l => l.aktif === true || l.aktif === 1 || String(l.aktif) === "1" || l.aktif === "t")
      .map(l => {
        const k = katData.find(kat => kat.id === l.kategori_id);
        return {
          id: l.id,
          nama: l.nama,
          kategori: k ? k.nama : 'Lainnya',
          harga_display: formatHarga(l.harga_min, l.harga_max, l.satuan),
          urutan_kategori: k ? k.urutan : 99,
          harga_min: l.harga_min
        };
      }).sort((a, b) => a.urutan_kategori - b.urutan_kategori);

    uniqueCategories = [...new Set(allLayanan.map(l => l.kategori))];
    
    showCategories();
    renderPriceList(allLayanan);

  } catch (err) {
    document.getElementById('kat-view').innerHTML = `<div style="background:#fee; color:red; padding:20px; border-radius:10px; text-align:center;">
      <strong>Error Koneksi Data:</strong><br>${err.message}
    </div>`;
  }
}

// 2. Render tampilan kategori produk
function showCategories() {
  document.getElementById('kat-view').style.display = 'grid';
  document.getElementById('svc-view').style.display = 'none';
  document.getElementById('section-subtitle').textContent = 'Silakan pilih kategori perawatan yang Anda butuhkan.';
  document.getElementById('kat-view').innerHTML = uniqueCategories.map(cat => {
    let imgSrc = sampleImages[cat] || defaultImg;
    let desc = categoryDescriptions[cat] || 'Perawatan kecantikan eksklusif untuk hasil sempurna dan memuaskan.';
    return `
      <div class="kat-card" onclick="showServices('${cat}')">
        <div class="kat-img"><img src="${imgSrc}" alt="${cat}" /></div>
        <div class="kat-title">${cat}</div>
        <div class="kat-desc" style="padding: 0 1.5rem 1.5rem 1.5rem; text-align: center; font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; font-weight: 300;">${desc}</div>
      </div>
    `;
  }).join('');
}

// 3. Tampilkan sub-layanan spesifik dari kategori terpilih
function showServices(catName) {
  document.getElementById('kat-view').style.display = 'none';
  document.getElementById('svc-view').style.display = 'block';
  document.getElementById('svc-view-title').textContent = catName;
  document.getElementById('section-subtitle').textContent = `Pilih layanan dari menu ${catName}. Anda bisa memilih lebih dari satu.`;
  
  const list = allLayanan.filter(l => l.kategori === catName);
  document.getElementById('svc-list').innerHTML = list.map(l => {
    let desc = specificServiceDescriptions[l.nama] || categoryDescriptions[catName] || 'Perawatan kecantikan eksklusif untuk hasil terbaik.';
    return `
    <div class="svc-item ${selectedServices.has(l.id) ? 'selected' : ''}" id="svc-${l.id}" onclick="toggleService(${l.id})">
      <div class="svc-item-name">${l.nama}</div>
      <div class="svc-item-desc">${desc}</div>
    </div>
  `}).join('');
}

function renderPriceList(list) {
  const cats = [...new Set(list.map(l => l.kategori))];
  document.getElementById('price-grid').innerHTML = cats.map(cat => `
    <div class="price-block">
      <div class="price-block-head">${cat}</div>
      ${list.filter(l => l.kategori === cat).map(l => `
        <div class="price-row"><span>${l.nama}</span><span>${l.harga_display}</span></div>
      `).join('')}
    </div>`).join('');
}

function toggleService(id) {
  if (selectedServices.has(id)) selectedServices.delete(id); else selectedServices.add(id);
  const itemEl = document.getElementById(`svc-${id}`);
  if (itemEl) itemEl.classList.toggle('selected', selectedServices.has(id));
  updateSelectedPanel();
}

function updateSelectedPanel() {
  const panel = document.getElementById('selected-panel');
  const list = document.getElementById('selected-list');
  if (selectedServices.size === 0) { panel.classList.remove('show'); return; }
  panel.classList.add('show');
  list.innerHTML = Array.from(selectedServices).map(sid => {
    const svc = allLayanan.find(l => l.id == sid);
    return svc ? `<div class="selected-tag">${svc.nama} <button onclick="event.stopPropagation(); toggleService(${sid})">&times;</button></div>` : '';
  }).join('');
}

function clearAllServices() {
  selectedServices.clear();
  document.querySelectorAll('.svc-item').forEach(c => c.classList.remove('selected'));
  updateSelectedPanel();
}

// 4. Verifikasi slot jam yang tersedia di tanggal tertentu
async function cekSlot() {
  const tgl = document.getElementById('f-tanggal').value;
  if (!tgl) return;

  const pills = document.querySelectorAll('.time-pill');
  pills.forEach(p => p.classList.remove('disabled', 'active'));
  document.getElementById('f-jam').value = ""; 

  document.getElementById('slot-hint').innerHTML = '<span style="color:var(--text-muted)">Memeriksa ketersediaan jadwal...</span>';

  try {
    const res = await fetch(`${SUPABASE_URL}/rest/v1/booking?select=jam&tanggal=eq.${tgl}&status=neq.batal`, { headers });
    if (!res.ok) throw new Error("Gagal mengambil data jadwal");
    const bookedData = await res.json();
    
    const bookedTimes = bookedData.map(b => b.jam.substring(0, 5));

    pills.forEach(p => {
      const pillTime = p.textContent.trim();
      if (bookedTimes.includes(pillTime)) {
        p.classList.add('disabled'); 
      }
    });

    document.getElementById('slot-hint').innerHTML = '<span class="ok">Pilih jam yang tersedia (warna putih).</span>';
  } catch (err) {
    console.error(err);
    document.getElementById('slot-hint').innerHTML = '<span class="err">Gagal memuat jadwal. Cek koneksi Anda.</span>';
  }
}

// 5. Submit entri reservasi baru ke database Supabase
async function submitBooking() {
  const nama = document.getElementById('f-nama').value.trim();
  const telepon = "62" + document.getElementById('f-telepon').value.trim();
  const tanggal = document.getElementById('f-tanggal').value;
  const jam = document.getElementById('f-jam').value;
  const catatan = document.getElementById('f-catatan').value.trim();

  if (!nama || !telepon || selectedServices.size === 0 || !tanggal || !jam) { 
    showToast('Silakan lengkapi formulir & pilih layanan terlebih dahulu.', 'err'); 
    return; 
  }

  const btn = document.getElementById('btn-submit'); 
  btn.disabled = true; 
  document.getElementById('btn-text').textContent = 'Memproses...';
  btn.style.opacity = "0.7";
  btn.style.cursor = "not-allowed";

  try {
    // 5A. Validasi / Simpan Informasi Pelanggan
    const resP = await fetch(`${SUPABASE_URL}/rest/v1/pelanggan?select=id&telepon=eq.${encodeURIComponent(telepon)}`, { headers });
    if (!resP.ok) throw new Error("Gagal memeriksa data pelanggan.");
    const pData = await resP.json();
    
    let pelangganId;
    if (pData.length > 0) {
      pelangganId = pData[0].id;
      const resPatch = await fetch(`${SUPABASE_URL}/rest/v1/pelanggan?id=eq.${pelangganId}`, {
        method: 'PATCH', headers: headers, body: JSON.stringify({ nama: nama })
      });
      if (!resPatch.ok) throw new Error("Gagal memperbarui data pelanggan.");
    } else {
      const resNewP = await fetch(`${SUPABASE_URL}/rest/v1/pelanggan`, {
        method: 'POST', headers: { ...headers, 'Prefer': 'return=representation' },
        body: JSON.stringify({ nama: nama, telepon: telepon })
      });
      if (!resNewP.ok) throw new Error("Gagal mendaftarkan pelanggan baru.");
      const newPData = await resNewP.json();
      pelangganId = newPData[0].id;
    }

    // 5B. Input Lembar Reservasi Utama
    const kodeBooking = 'RS-' + Math.floor(1000 + Math.random() * 9000);
    const resB = await fetch(`${SUPABASE_URL}/rest/v1/booking`, {
      method: 'POST', headers: { ...headers, 'Prefer': 'return=representation' },
      body: JSON.stringify({ kode: kodeBooking, pelanggan_id: pelangganId, tanggal: tanggal, jam: jam, catatan: catatan, status: 'pending' })
    });
    
    if (!resB.ok) {
        const errorDB = await resB.json();
        if (errorDB.code === '23505') {
            throw new Error('Maaf, jadwal ini baru saja dipesan orang lain. Silakan pilih waktu lain.');
        }
        throw new Error("Gagal membuat reservasi di database.");
    }

    const bData = await resB.json();
    const newBookingId = bData[0].id;

    // 5C. Petakan Layanan Terpilih ke Hubungan Relasi N-to-N
    const arrLayanan = Array.from(selectedServices).map(id => {
      const svc = allLayanan.find(l => l.id === id);
      return { booking_id: newBookingId, layanan_id: id, harga_saat_booking: svc.harga_min };
    });
    const resL = await fetch(`${SUPABASE_URL}/rest/v1/booking_layanan`, {
      method: 'POST', headers: headers, body: JSON.stringify(arrLayanan)
    });
    if (!resL.ok) throw new Error("Gagal menyimpan detail layanan.");

    // 5D. Perbarui Antarmuka Sukses
    document.getElementById('form-section').style.display = 'none'; 
    document.getElementById('selected-panel').style.display = 'none';
    
    const sc = document.getElementById('success-card'); 
    sc.classList.add('show');
    document.getElementById('success-kode').textContent = kodeBooking;
    document.getElementById('success-layanan').textContent = Array.from(selectedServices).map(id => allLayanan.find(l => l.id === id).nama).join(', ');
    
    sc.scrollIntoView({ behavior: 'smooth' }); 
    showToast('Reservasi berhasil!', 'ok');

  } catch (err) {
    showToast(err.message || 'Error sistem. Gagal membuat reservasi.', 'err');
    console.error("Booking Error:", err);
  } finally {
    btn.disabled = false; 
    document.getElementById('btn-text').textContent = 'Konfirmasi Reservasi';
    btn.style.opacity = "1";
    btn.style.cursor = "pointer";
  }
}

function resetForm() {
  document.getElementById('success-card').classList.remove('show'); document.getElementById('form-section').style.display = ''; document.getElementById('selected-panel').style.display = '';
  ['f-nama','f-telepon','f-catatan','f-tanggal','f-jam'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('slot-hint').textContent = ''; clearAllServices(); showCategories(); window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showToast(msg, type = '') {
  const t = document.getElementById('toast'); t.textContent = msg; t.className = 'toast ' + type + ' show';
  setTimeout(() => t.classList.remove('show'), 4000);
}

// Inisialisasi awal pembatasan kalender hari ini
document.getElementById('f-tanggal').min = new Date().toISOString().split('T')[0];
loadLayanan();