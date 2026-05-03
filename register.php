<?php
/**
 * FS Access Portal - Register.php
 * Regisztráció az 'igenylo' táblába, alapértelmezett 'user' jogosultsággal.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!file_exists('config.php')) {
    die("HIBA: Nincs meg a config.php");
}

require 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!empty($username) && !empty($password) && !empty($email)) {
        if ($password !== $password_confirm) {
            $error = "A két jelszó nem egyezik!";
        } else {
            try {
                // Ellenőrizzük, hogy létezik-e már a név vagy email
                $stmt = $pdo->prepare("SELECT igenylo_ID FROM igenylo WHERE igenylo_nev = :uname OR igenylo_email = :email");
                $stmt->execute(['uname' => $username, 'email' => $email]);

                if ($stmt->fetch()) {
                    $error = "Ez a felhasználónév vagy email már foglalt!";
                } else {
                    // Jelszó hashelése a biztonságos tároláshoz
                    $hashedPass = password_hash($password, PASSWORD_DEFAULT);

                    // JAVÍTÁS: Itt adjuk meg, hogy az 'igenylo_jog' alapból 'user' legyen
                    $insert = $pdo->prepare("INSERT INTO igenylo (igenylo_nev, igenylo_email, igenylo_password, igenylo_jog) VALUES (:nev, :email, :pass, 'user')");

                    if ($insert->execute([
                            'nev'   => $username,
                            'email' => $email,
                            'pass'  => $hashedPass
                    ])) {
                        $message = "Sikeres regisztráció! Most már bejelentkezhet.";
                    } else {
                        $error = "Hiba történt a mentés során.";
                    }
                }
            } catch (PDOException $e) {
                $error = "Adatbázis hiba: " . $e->getMessage();
            }
        }
    } else {
        $error = "Minden mezőt ki kell tölteni!";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció - FS Access Portal</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #2c3e50; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center; }
        h2 { color: #003C71; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 14px; background: #27ae60; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 10px; font-size: 16px; transition: 0.3s;}
        button:hover { background: #219653; }
        .error { color: #e74c3c; background: #fdf2f2; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; border: 1px solid #f9d6d6;}
        .success { color: #27ae60; background: #f2fdf5; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; border: 1px solid #d4edda;}
        .back-link { display: block; text-align: center; margin-top: 20px; color: #34495e; text-decoration: none; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <h2>Regisztráció</h2>

    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($message): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Felhasználónév" required>
        <input type="email" name="email" placeholder="E-mail cím" required>
        <input type="password" name="password" placeholder="Jelszó" required>
        <input type="password" name="password_confirm" placeholder="Jelszó ismétlése" required>
        <button type="submit">Fiók létrehozása</button>
    </form>
    <a href="login.php" class="back-link">« Vissza a bejelentkezéshez</a>
</div>
</body>
</html>