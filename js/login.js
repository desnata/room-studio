const db = window.supabase.createClient('https://vhsrmfmvblfqolhwgwbt.supabase.co', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZoc3JtZm12YmxmcW9saHdnd2J0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzU2ODQ4NjAsImV4cCI6MjA5MTI2MDg2MH0.jxVo-xAjpEyaqGiROMlWbdwmCga6GKNz_rnNrRYPpWQ');

// Cek apakah sudah login
async function checkSession() {
  const { data } = await db.auth.getSession();
  if (data.session) window.location.href = 'admin.html';
}
checkSession();

document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('btn-login');
  const err = document.getElementById('error-msg');
  btn.disabled = true; btn.textContent = "Memeriksa..."; err.style.display = 'none';

  const { data, error } = await db.auth.signInWithPassword({
    email: document.getElementById('email').value,
    password: document.getElementById('password').value
  });

  if (error) {
    err.textContent = "Gagal masuk: " + error.message;
    err.style.display = 'block';
    btn.disabled = false; btn.textContent = "Masuk ke Sistem";
  } else {
    window.location.href = 'admin.html';
  }
});