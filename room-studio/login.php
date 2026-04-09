<?php
require_once 'config.php';

if (isAdminLoggedIn()) {
    header('Location: admin.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Username atau password tidak valid.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login Admin | Room Studio</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg-app: #fdf8f9;
  --bg-sidebar: #0a0a0a; /* Sidebar hitam pekat */
  --text-main: #2c2a29;
  --text-muted: #7a7571;
  --border: #e8e4dc;
  
  --primary: #e6a8b1; /* Pink pastel/Rose gold */
  --primary-hover: #d68f9a;
  --primary-soft: rgba(230, 168, 177, 0.15);
  
  --white: #ffffff;
  --radius: 12px;
  --shadow: 0 10px 30px rgba(0,0,0,0.04);
}
body{
  font-family: var(--sans);
  background-color: var(--bg-app);
  color: var(--text-main);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  font-weight: 300;
}
.login-container{
  background: var(--white);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  border: 1px solid var(--border);
  width: 100%;
  max-width: 400px;
  padding: 3.5rem 2.5rem;
  text-align: center;
}
.logo-area{
  margin-bottom: 2rem;
}
.logo-circle{
  width: 65px;
  height: 65px;
  background: var(--primary);
  color: var(--white);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.2rem;
  font-size: 2rem;
  font-family: var(--serif);
  font-style: italic;
  font-weight: 500;
  box-shadow: 0 4px 15px var(--primary-soft);
}
.login-title{
  font-family: var(--serif);
  font-size: 1.8rem;
  font-weight: 500;
  color: var(--text-main);
  margin-bottom: 0.3rem;
}
.login-subtitle{
  font-size: 0.95rem;
  color: var(--text-muted);
  margin-bottom: 2.5rem;
}
.form-group{
  margin-bottom: 1.5rem;
  text-align: left;
}
.form-group label{
  display: block;
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--text-muted);
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.form-group input{
  width: 100%;
  padding: 0.9rem 1.2rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  font-family: inherit;
  font-size: 0.95rem;
  color: var(--text-main);
  background: var(--bg-app);
  transition: all 0.3s;
}
.form-group input:focus{
  outline: none;
  background: var(--white);
  border-color: var(--primary);
  box-shadow: 0 0 0 4px var(--primary-soft);
}
.form-group input::placeholder{
  color: #a8a39e;
}
.error-msg{
  background: #fdf5f5;
  color: #d9534f;
  padding: 0.8rem;
  border-radius: 8px;
  font-size: 0.85rem;
  margin-bottom: 1.5rem;
  border: 1px solid #fadcdc;
  text-align: left;
}
.btn-login{
  width: 100%;
  padding: 0.9rem;
  background: var(--primary);
  color: var(--white);
  border: none;
  border-radius: 30px;
  font-family: var(--sans);
  font-size: 1rem;
  font-weight: 500;
  letter-spacing: 1px;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: 1rem;
}
.btn-login:hover{
  background: var(--primary-hover);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px var(--primary-soft);
}
.back-link{
  display: inline-block;
  margin-top: 2.5rem;
  color: var(--text-muted);
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.3s;
}
.back-link:hover{
  color: var(--primary);
}
</style>
</head>
<body>
<div class="login-container">
  <div class="logo-area">
  <div class="logo-circle" style="background: transparent; overflow: hidden; border-radius: 50%;">
    <img src="image/logo.jpeg" alt="Room Studio Logo" style="width: 100%; height: 100%; object-fit: cover;" />
  </div>
    <h1 class="login-title">Administrator</h1>
    <p class="login-subtitle">Masuk ke sistem manajemen Room Studio</p>
  </div>
  
  <?php if ($error): ?>
  <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  
  <form method="POST" action="">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" required placeholder="Masukkan username admin" autofocus autocomplete="off"/>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required placeholder="••••••••"/>
    </div>
    <button type="submit" class="btn-login">Masuk ke Sistem</button>
  </form>
  
  <a href="index.php" class="back-link">&larr; Kembali ke Halaman Utama</a>
</div>
</body>
</html>