<?php
session_start();
require_once '../config/db.php';

// 1. Güvenlik Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: ../index.php");
    exit;
}

// 2. Personel Ekleme İşlemi (POST)
$mesaj = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['personel_ekle'])) {
    $ad = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $sifre = trim($_POST['password']);

    if (!empty($ad) && !empty($email) && !empty($sifre)) {
        // Şifreyi güvenli hale getir (Hash)
        $hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT);
        
        try {
            // Role ID 2 = Personel
            $sql = "INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, 2)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ad, $email, $hashli_sifre]);
            $mesaj = "<div class='alert alert-success'>Personel başarıyla eklendi!</div>";
        } catch (PDOException $e) {
            $mesaj = "<div class='alert alert-danger'>Hata: E-posta zaten kayıtlı olabilir.</div>";
        }
    } else {
        $mesaj = "<div class='alert alert-warning'>Lütfen tüm alanları doldurun.</div>";
    }
}

// 3. Personel Silme İşlemi (GET)
if (isset($_GET['sil_id'])) {
    $sil_id = $_GET['sil_id'];
    // Kendini silmesini engelle (Her ihtimale karşı)
    if ($sil_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role_id = 2");
        $stmt->execute([$sil_id]);
        header("Location: personel_islemleri.php"); // Sayfayı yenile
        exit;
    }
}

// 4. Personelleri Listeleme (READ)
// Sadece rolü 2 (Personel) olanları çekiyoruz
$stmt = $pdo->query("SELECT * FROM users WHERE role_id = 2 ORDER BY user_id DESC");
$personeller = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Personel İşlemleri - Yurt Otomasyonu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #cfd2d6; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; color: white; border-left: 4px solid #0d6efd; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <h4 class="text-center py-4 border-bottom border-secondary">Yurt v1.0</h4>
            <div class="d-flex flex-column">
                <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                <a href="personel_islemleri.php" class="active"><i class="fas fa-user-tie me-2"></i> Personel İşlemleri</a>
                <a href="ogrenci_islemleri.php"><i class="fas fa-user-graduate me-2"></i> Öğrenci İşlemleri</a>
                <a href="oda_islemleri.php"><i class="fas fa-bed me-2"></i> Oda Yönetimi</a>
                <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                <a href="yoklama_raporu.php"><i class="fas fa-calendar-check me-2"></i> Yoklama Raporu</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Personel Yönetimi</h2>
            
            <?php echo $mesaj; ?>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-plus-circle"></i> Yeni Personel Ekle
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="row g-3">
                        <div class="col-md-4">
                            <label>Ad Soyad</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>E-Posta</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Şifre</label>
                            <input type="text" name="password" class="form-control" placeholder="123456" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" name="personel_ekle" class="btn btn-success w-100">Ekle</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Mevcut Personeller</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>E-Posta</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($personeller) > 0): ?>
                                <?php foreach ($personeller as $p): ?>
                                    <tr>
                                        <td><?php echo $p->user_id; ?></td>
                                        <td><?php echo htmlspecialchars($p->full_name); ?></td>
                                        <td><?php echo htmlspecialchars($p->email); ?></td>
                                        <td><?php echo date("d.m.Y", strtotime($p->created_at)); ?></td>
                                        <td>
                                            <a href="?sil_id=<?php echo $p->user_id; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
                                                <i class="fas fa-trash"></i> Sil
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Henüz kayıtlı personel yok.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>