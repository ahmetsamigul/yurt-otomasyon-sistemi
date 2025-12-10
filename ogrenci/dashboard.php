<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) { header("Location: ../index.php"); exit; }
$isim = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/ogrenci.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark d-md-none p-3 mb-3"><div class="container-fluid"><span class="navbar-brand">Öğrenci Paneli</span><button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"><span class="navbar-toggler-icon"></span></button></div></nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0 offcanvas-md offcanvas-start" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header d-md-none border-bottom border-secondary"><h5 class="offcanvas-title text-white">Menü</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button></div>
            <div class="offcanvas-body d-block p-0">
                <h4 class="text-center py-4 border-bottom border-secondary d-none d-md-block">Öğrenci</h4>
                <div class="d-flex flex-column mt-2">
                    <a href="dashboard.php" class="active"><i class="fas fa-home me-2"></i> Anasayfa</a>
                    <a href="profilim.php"><i class="fas fa-user me-2"></i> Profilim & Yoklama</a>
                    <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                    <a href="ariza_bildir.php"><i class="fas fa-tools me-2"></i> Arıza Bildir</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <div class="alert alert-info"><h4>Hoş Geldin, <?php echo htmlspecialchars($isim); ?> 👋</h4><p>Yurt sistemine hoş geldin.</p></div>
            <div class="row">
                <div class="col-md-6 col-sm-12"><div class="card-box bg-info-custom"><h3><i class="fas fa-user"></i> Profilim</h3><p>Oda ve Kişisel Bilgiler</p><a href="profilim.php" class="btn btn-sm btn-light text-dark">Görüntüle</a></div></div>
                <div class="col-md-6 col-sm-12"><div class="card-box bg-warning-custom"><h3><i class="fas fa-tools"></i> Destek</h3><p>Arıza bildirim</p><a href="ariza_bildir.php" class="btn btn-sm btn-light text-dark">Bildir</a></div></div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>