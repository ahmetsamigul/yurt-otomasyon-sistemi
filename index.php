<?php
session_start();
require_once 'config/db.php';

// Kullanıcı zaten giriş yapmışsa, rolüne uygun panele yönlendir
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 1) header("Location: admin/dashboard.php");
    elseif ($_SESSION['role'] == 2) header("Location: personel/dashboard.php");
    elseif ($_SESSION['role'] == 3) header("Location: ogrenci/dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Lütfen tüm alanları doldurun.";
    } else {
        // Kullanıcıyı e-posta ile veritabanından çek
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Şifre doğrulama (Girilen şifre ile DB'deki hash eşleşiyor mu?)
            if (password_verify($password, $user->password)) {
                // Giriş Başarılı: Oturum bilgilerini sakla
                $_SESSION['user_id'] = $user->user_id;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['role'] = $user->role_id;

                // Yönlendirme
                if ($user->role_id == 1) {
                    header("Location: admin/dashboard.php");
                } elseif ($user->role_id == 2) {
                    header("Location: personel/dashboard.php");
                } elseif ($user->role_id == 3) {
                    header("Location: ogrenci/dashboard.php");
                }
                exit;
            } else {
                $error = "Hatalı şifre!";
            }
        } else {
            $error = "Bu e-posta ile kayıtlı kullanıcı bulunamadı.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Yurt Otomasyonu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>

<div class="login-card">
    <h3 class="text-center mb-4">Yurt Sistemi Giriş</h3>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">E-Posta Adresi</label>
            <input type="email" name="email" class="form-control" required value="admin@yurt.com">
        </div>
        <div class="mb-3">
            <label class="form-label">Şifre</label>
            <input type="password" name="password" class="form-control" required placeholder="******">
        </div>
        <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
    </form>
</div>

</body>
</html>