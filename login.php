<?php
/**
 * FS Access Portal - Login.php
 * Használja az 'igenylo' táblát az adatbázisból.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!file_exists('config.php')) {
    die("HIBA: Nincs meg a config.php");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            // Lekérdezés az igenylo_nev alapján
            $stmt = $pdo->prepare("SELECT igenylo_ID, igenylo_nev, igenylo_password FROM igenylo WHERE igenylo_nev = :uname LIMIT 1");
            $stmt->execute(['uname' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Jelszó ellenőrzése (titkosított jelszó esetén)
            if ($user && password_verify($password, $user['igenylo_password'])) {
                $_SESSION['user_id'] = $user['igenylo_ID'];
                $_SESSION['username'] = $user['igenylo_nev'];
                $_SESSION['logged_in'] = true;

                header("Location: index.php");
                exit;
            } else {
                $error = "Helytelen felhasználónév vagy jelszó!";
            }
        } catch (PDOException $e) {
            $error = "Adatbázis hiba történt.";
            error_log("Login error: " . $e->getMessage());
        }
    } else {
        $error = "Kérjük, töltsön ki minden mezőt!";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FS Access Portal - Login</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #2c3e50; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-card { background: white; padding: 45px; border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); width: 100%; max-width: 420px; text-align: center; }
        .logo-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; height: 60px; }
        .logo-wrapper { flex: 1; display: flex; justify-content: center; }
        .logo-pm { height: 38px; width: auto; }
        .logo-do { height: 46px; width: auto; }
        h2 { color: #003C71; margin: 0 0 5px 0; font-weight: 600; }
        .sub-txt { color: #7f8c8d; font-size: 14px; margin-bottom: 30px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 16px; background: #f9f9f9; }
        input:focus { border-color: #003C71; outline: none; background: #fff; box-shadow: 0 0 0 3px rgba(0,60,113,0.1); }
        button { width: 100%; padding: 14px; background: #003C71; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 15px; font-size: 16px; transition: 0.3s; }
        button:hover { background: #002a50; }
        .error-msg { background: #fdf2f2; color: #e74c3c; padding: 12px; border-radius: 6px; border: 1px solid #f9d6d6; font-size: 14px; margin-bottom: 20px; text-align: left; }
        .footer-link { margin-top: 25px; font-size: 14px; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 20px; }
        .footer-link a { color: #003C71; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="logo-container">
        <div class="logo-wrapper"><img src="pm_logo.png" class="logo-pm" alt="PM"></div>
        <div class="logo-wrapper"><img src="do_logo.jpg" class="logo-do" alt="DO"></div>
    </div>
    <h2>FS Access Portal</h2>
    <div class="sub-txt">Bejelentkezés az ügyfélkapuba</div>

    <?php if ($error): ?>
        <div class="error-msg"><strong>Hiba:</strong> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Felhasználónév" required autofocus>
        <input type="password" name="password" placeholder="Jelszó" required>
        <button type="submit">Bejelentkezés</button>
    </form>

    <div class="footer-link">
        Még nincs fiókja? <a href="register.php">Regisztráció itt</a>
    </div>
</div>
</body>
</html>
</html>