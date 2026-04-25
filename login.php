<?php
session_start();

// ─── Redirect if already logged in ───────────────────────────────────────────
if (isset($_SESSION['user_role'])) {
    header("Location: faculty_allocation_system.php");
    exit;
}

$error = '';

// ─── Allowed BITS Pilani email domains ───────────────────────────────────────
$allowed_domains = ['pilani.bits-pilani.ac.in', 'goa.bits-pilani.ac.in', 'hyderabad.bits-pilani.ac.in', 'dubai.bits-pilani.ac.in'];

// ─── Role credentials ─────────────────────────────────────────────────────────
// In production store these hashed in DB; for this project they're inline.
// Passwords are: admin123 | edit456 | view789 (matching the SQL user accounts)
$users = [
    'admin@goa.bits-pilani.ac.in'   => ['password' => 'admin123', 'role' => 'admin',  'name' => 'Admin DBA',    'db_user' => 'admin_dba'],
    'editor@goa.bits-pilani.ac.in'  => ['password' => 'edit456',  'role' => 'editor', 'name' => 'Editor User',  'db_user' => 'editor_user'],
    'viewer@goa.bits-pilani.ac.in'  => ['password' => 'view789',  'role' => 'viewer', 'name' => 'Viewer User',  'db_user' => 'viewer_user'],
];

// ─── Process login ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Check domain
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        if (!in_array($domain, $allowed_domains)) {
            $error = 'Access restricted to BITS Pilani email addresses only.';
        } elseif (!isset($users[$email])) {
            $error = 'No account found for this email address.';
        } elseif ($users[$email]['password'] !== $password) {
            $error = 'Incorrect password. Please try again.';
        } else {
            // ✅ Login successful
            session_regenerate_id(true);
            $_SESSION['user_email']  = $email;
            $_SESSION['user_role']   = $users[$email]['role'];
            $_SESSION['user_name']   = $users[$email]['name'];
            $_SESSION['db_user']     = $users[$email]['db_user'];
            header("Location: faculty_allocation_system.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login — Faculty Allocation System</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
:root {
  --ink: #1a1814;
  --ink2: #6b6860;
  --ink3: #a8a59e;
  --accent: #2a6ef5;
  --accent-bg: #eef3ff;
  --border: #e2dfd8;
  --bg: #f4f2ee;
  --surface: #ffffff;
  --red: #b84a18;
  --red-bg: #fdf0ea;
  --green: #1d6b4e;
  --green-bg: #e8f6f0;
  --amber: #92600a;
  --radius: 12px;
  --radius-sm: 8px;
  --ff-h: 'Syne', sans-serif;
  --ff-b: 'Outfit', sans-serif;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: var(--ff-b);
  background: var(--bg);
  color: var(--ink);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

/* Background grid pattern */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image:
    linear-gradient(var(--border) 1px, transparent 1px),
    linear-gradient(90deg, var(--border) 1px, transparent 1px);
  background-size: 40px 40px;
  opacity: 0.5;
  pointer-events: none;
}

/* Decorative blobs */
.blob {
  position: fixed;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.15;
  pointer-events: none;
}
.blob-1 { width: 400px; height: 400px; background: var(--accent); top: -100px; right: -100px; }
.blob-2 { width: 300px; height: 300px; background: #6a3db8; bottom: -80px; left: -80px; }

/* Card */
.login-wrap {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 440px;
  padding: 16px;
  animation: fadeUp 0.5s ease both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

.login-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 40px 36px 36px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.04);
}

/* Logo + header */
.logo-row {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 28px;
}
.logo-box {
  width: 46px; height: 46px;
  background: var(--ink);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--ff-h);
  font-weight: 800; font-size: 16px;
  color: #fff;
  flex-shrink: 0;
}
.logo-text-main {
  font-family: var(--ff-h);
  font-size: 15px; font-weight: 700;
  line-height: 1.2;
}
.logo-text-sub {
  font-size: 11px; color: var(--ink3); margin-top: 2px;
}

.bits-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--ink);
  color: #fff;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  font-family: var(--ff-h);
  letter-spacing: 0.03em;
  margin-bottom: 20px;
}
.bits-dot {
  width: 6px; height: 6px;
  background: #4ade80;
  border-radius: 50%;
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

h1 {
  font-family: var(--ff-h);
  font-size: 24px; font-weight: 700;
  margin-bottom: 6px;
}
.subtitle { font-size: 13px; color: var(--ink2); margin-bottom: 28px; line-height: 1.5; }

/* Form */
.field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.field label {
  font-size: 11px; font-weight: 600;
  color: var(--ink2);
  text-transform: uppercase; letter-spacing: 0.07em;
}
.field input {
  padding: 11px 14px;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-sm);
  font-family: var(--ff-b);
  font-size: 13.5px; color: var(--ink);
  background: var(--surface);
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  width: 100%;
}
.field input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(42,110,245,0.08);
}
.field input::placeholder { color: var(--ink3); }

/* Role pills */
.roles-hint {
  background: var(--bg);
  border-radius: var(--radius-sm);
  padding: 14px;
  margin-bottom: 20px;
  border: 1px solid var(--border);
}
.roles-title { font-size: 10px; font-weight: 600; color: var(--ink3); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }
.roles-grid { display: flex; flex-direction: column; gap: 7px; }
.role-row { display: flex; align-items: center; gap: 8px; }
.role-pill {
  font-size: 10px; font-weight: 700; font-family: var(--ff-h);
  padding: 2px 9px; border-radius: 10px;
  text-transform: uppercase; letter-spacing: 0.04em;
  flex-shrink: 0; min-width: 52px; text-align: center;
}
.pill-admin  { background: #fdf0ea; color: #b84a18; }
.pill-editor { background: #eef3ff; color: #2a6ef5; }
.pill-viewer { background: #e8f6f0; color: #1d6b4e; }
.role-perms  { font-size: 11px; color: var(--ink2); }

/* Error */
.error-box {
  background: var(--red-bg);
  border: 1px solid #f5c6b0;
  border-left: 3px solid var(--red);
  border-radius: var(--radius-sm);
  padding: 11px 14px;
  font-size: 12.5px;
  color: var(--red);
  margin-bottom: 16px;
  animation: shake 0.3s ease;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

/* Submit button */
.btn-login {
  width: 100%;
  padding: 13px;
  background: var(--ink);
  color: #fff;
  border: none;
  border-radius: var(--radius-sm);
  font-family: var(--ff-h);
  font-size: 14px; font-weight: 700;
  cursor: pointer;
  transition: background 0.15s, transform 0.1s;
  letter-spacing: 0.02em;
  margin-top: 4px;
}
.btn-login:hover { background: var(--accent); }
.btn-login:active { transform: scale(0.98); }

/* Footer note */
.footer-note {
  text-align: center;
  font-size: 11px; color: var(--ink3);
  margin-top: 20px;
  line-height: 1.6;
}
</style>
</head>
<body>

<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="login-wrap">
  <div class="login-card">

    <div class="logo-row">
      <div class="logo-box">FA</div>
      <div>
        <div class="logo-text-main">Faculty Course Allocation System</div>
        <div class="logo-text-sub">DBMS Group Project — 7 Modules</div>
      </div>
    </div>

    <div class="bits-badge">
      <span class="bits-dot"></span>
      BITS Pilani — Goa Campus
    </div>

    <h1>Welcome back</h1>
    <p class="subtitle">Sign in with your BITS Pilani email to access the system.</p>

    <?php if ($error): ?>
    <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="field">
        <label>BITS Pilani Email</label>
        <input type="email" name="email" placeholder="yourname@goa.bits-pilani.ac.in" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus/>
      </div>
      <div class="field">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required/>
      </div>

      <div class="roles-hint">
        <div class="roles-title">Access Levels</div>
        <div class="roles-grid">
          <div class="role-row">
            <span class="role-pill pill-admin">Admin</span>
            <span class="role-perms">SELECT · INSERT · UPDATE · CREATE TABLE · DROP TABLE</span>
          </div>
          <div class="role-row">
            <span class="role-pill pill-editor">Editor</span>
            <span class="role-perms">SELECT · INSERT · UPDATE</span>
          </div>
          <div class="role-row">
            <span class="role-pill pill-viewer">Viewer</span>
            <span class="role-perms">SELECT only — read-only access</span>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-login">Sign In →</button>
    </form>

    <div class="footer-note">
      Access restricted to <strong>bits-pilani.ac.in</strong> email addresses.<br/>
      Contact your DBA if you don't have login credentials.
    </div>

  </div>
</div>

</body>
</html>
