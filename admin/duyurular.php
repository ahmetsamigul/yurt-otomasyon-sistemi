<?php
session_start();
require_once '../config/db.php';

// 1. Güvenlik Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: ../index.php");
    exit;
}

$mesaj = "";

// 2. DUYURU EKLEME (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['duyuru_ekle'])) {
    $baslik = trim($_POST['title']);
    $icerik = trim($_POST['content']);

    if (!empty($baslik) && !empty($icerik)) {
        try {
            // created_by alanına şu an giriş yapmış adminin ID'sini yazıyoruz
            $sql = "INSERT INTO announcements (title, content, created_by) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$baslik, $icerik, $_SESSION['user_id']]);
            $mesaj = "<div class='alert alert-success'>Duyuru başarıyla yayınlandı!</div>";
        } catch (PDOException $e) {
            $mesaj = "<div class='alert alert-danger'>Hata: " . $e->getMessage() . "</div>";
        }
    } else {
        $mesaj = "<div class='alert alert-warning'>Başlık ve içerik boş olamaz.</div>";
    }
}

// 3. DUYURU SİLME (GET)
if (isset($_GET['sil_id'])) {
    $sil_id = $_GET['sil_id'];
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE announce_id = ?");
    $stmt->execute([$sil_id]);
    header("Location: duyurular.php");
    exit;
}

// 4. DUYURULARI LİSTELEME
$duyurular = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Duyuru Yönetimi - Yurt Otomasyonu</title>
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
                <a href="personel_islemleri.php"><i class="fas fa-user-tie me-2"></i> Personel İşlemleri</a>
                <a href="ogrenci_islemleri.php"><i class="fas fa-user-graduate me-2"></i> Öğrenci İşlemleri</a>
                <a href="oda_islemleri.php"><i class="fas fa-bed me-2"></i> Oda Yönetimi</a>
                <a href="duyurular.php" class="active"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                <a href="yoklama_raporu.php"><i class="fas fa-calendar-check me-2"></i> Yoklama Raporu</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Duyuru Paneli</h2>
            
            <?php echo $mesaj; ?>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-pen"></i> Yeni Duyuru Yaz
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label>Duyuru Başlığı</label>
                            <input type="text" name="title" class="form-control" required placeholder="Örn: Yemekhane Saatleri Değişti">
                        </div>
                        <div class="mb-3">
                            <label>İçerik</label>
                            <textarea name="content" class="form-control" rows="4" required placeholder="Duyuru detaylarını buraya yazın..."></textarea>
                        </div>
                        <button type="submit" name="duyuru_ekle" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Yayınla
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Yayınlanan Duyurular</h5>
                </div>
                <div class="card-body">
                    <?php if (count($duyurular) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($duyurular as $d): ?>
                                <div class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($d->title); ?></h5>
                                        <small class="text-muted"><?php echo date("d.m.Y H:i", strtotime($d->created_at)); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($d->content)); ?></p>
                                    <a href="?sil_id=<?php echo $d->announce_id; ?>" 
                                       class="btn btn-sm btn-outline-danger mt-2"
                                       onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?');">
                                        <i class="fas fa-trash"></i> Sil
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Henüz yayınlanmış bir duyuru yok.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>