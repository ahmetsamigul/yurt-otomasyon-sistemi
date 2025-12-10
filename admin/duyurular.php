<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) { header("Location: ../index.php"); exit; }

$mesaj = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['duyuru_ekle'])) {
    $stmt = $pdo->prepare("INSERT INTO announcements (title, content, created_by) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['title'], $_POST['content'], $_SESSION['user_id']]);
    $mesaj = "<div class='alert alert-success'>Duyuru yayınlandı!</div>";
}
if (isset($_GET['sil_id'])) {
    $pdo->prepare("DELETE FROM announcements WHERE announce_id = ?")->execute([$_GET['sil_id']]);
    header("Location: duyurular.php"); exit;
}
$duyurular = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyurular</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark d-md-none p-3 mb-3"><div class="container-fluid"><span class="navbar-brand">Yurt v1.0</span><button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"><span class="navbar-toggler-icon"></span></button></div></nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0 offcanvas-md offcanvas-start" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header d-md-none border-bottom border-secondary"><h5 class="offcanvas-title text-white">Menü</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button></div>
            <div class="offcanvas-body d-block p-0">
                <h4 class="text-center py-4 border-bottom border-secondary d-none d-md-block">Yurt v1.0</h4>
                <div class="d-flex flex-column mt-2">
                    <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                    <a href="personel_islemleri.php"><i class="fas fa-user-tie me-2"></i> Personel İşlemleri</a>
                    <a href="ogrenci_islemleri.php"><i class="fas fa-user-graduate me-2"></i> Öğrenci İşlemleri</a>
                    <a href="oda_islemleri.php"><i class="fas fa-bed me-2"></i> Oda Yönetimi</a>
                    <a href="duyurular.php" class="active"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                    <a href="yoklama_raporu.php"><i class="fas fa-calendar-check me-2"></i> Yoklama Raporu</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Duyuru Paneli</h2>
            <?php echo $mesaj; ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-info text-white"><i class="fas fa-pen"></i> Yeni Duyuru</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3"><label>Başlık</label><input type="text" name="title" class="form-control" required></div>
                        <div class="mb-3"><label>İçerik</label><textarea name="content" class="form-control" rows="3" required></textarea></div>
                        <button type="submit" name="duyuru_ekle" class="btn btn-primary">Yayınla</button>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5>Yayınlanan Duyurular</h5></div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($duyurular as $d): ?>
                        <div class="list-group-item">
                            <h5><?php echo htmlspecialchars($d->title); ?></h5>
                            <p><?php echo nl2br(htmlspecialchars($d->content)); ?></p>
                            <a href="?sil_id=<?php echo $d->announce_id; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Silinsin mi?');">Sil</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>