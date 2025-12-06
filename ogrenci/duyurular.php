<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header("Location: ../index.php");
    exit;
}

// Duyuruları tarihe göre çek
$duyurular = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Duyurular - Yurt Otomasyonu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; color: white; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; color: white; border-left: 4px solid #f1c40f; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <h4 class="text-center py-4 border-bottom border-secondary">Öğrenci</h4>
            <div class="d-flex flex-column">
                <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                <a href="profilim.php"><i class="fas fa-user me-2"></i> Profilim & Yoklama</a>
                <a href="duyurular.php" class="active"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                <a href="ariza_bildir.php"><i class="fas fa-tools me-2"></i> Arıza Bildir</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Duyurular</h2>
            
            <?php if (count($duyurular) > 0): ?>
                <div class="list-group">
                    <?php foreach ($duyurular as $d): ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start mb-3 shadow-sm border-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1 text-primary"><?php echo htmlspecialchars($d->title); ?></h5>
                                <small class="text-muted"><?php echo date("d.m.Y H:i", strtotime($d->created_at)); ?></small>
                            </div>
                            <p class="mb-1 mt-2"><?php echo nl2br(htmlspecialchars($d->content)); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">Henüz yayınlanmış bir duyuru bulunmuyor.</div>
            <?php endif; ?>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>