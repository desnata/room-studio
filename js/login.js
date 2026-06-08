// ==========================================
// LOGIKA UTAMA: HALAMAN LOGIN ADMIN
// ==========================================

// Cek status sesi saat ini (jika sudah login, langsung lempar ke dashboard admin)
async function checkSession() {
  const { data } = await db.auth.getSession();
  if (data.session) window.location.href = 'admin.html';
}

// Jalankan pemeriksaan sesi saat halaman dimuat
checkSession();

// Handler Event Kirim Form Login
function togglePassword() {
    const p = document.getElementById('password');
    p.type = (p.type === 'password') ? 'text' : 'password';
}

document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const btn = document.getElementById('btn-login');
    const err = document.getElementById('error-msg');
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    // UX: Beri feedback saat proses loading
    btn.disabled = true; 
    btn.textContent = "Memverifikasi..."; 
    err.style.display = 'none';

    try {
        const { data, error } = await db.auth.signInWithPassword({ email, password });
        
        if (error) throw error;
        
        // Redirect sukses
        window.location.href = 'admin.html';
    } catch (error) {
        // Keamanan: Berikan pesan kesalahan yang umum, jangan beritahu apakah email atau pass yang salah
        err.textContent = "Email atau Password salah. Silakan coba lagi.";
        err.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = "Masuk ke Sistem";
    }
});