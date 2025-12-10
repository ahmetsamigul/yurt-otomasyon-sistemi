<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) { header("Location: ../index.php"); exit; }

$isim = $_SESSION['full_name'];
$bugun = date("Y-m-d");

$ogrenci_sayisi = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 3")->fetchColumn();
$oda_sayisi = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$bos_yatak = $pdo->query("SELECT SUM(capacity - current_count) FROM rooms")->fetchColumn();
if(!$bos_yatak) $bos_yatak = 0;
$yoklama_sayisi = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ?");
$yoklama_sayisi->execute([$bugun]);
$yoklama_sayisi = $yoklama_sayisi->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müdür Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark d-md-none p-3 mb-3">
    <div class="container-fluid">
        <span class="navbar-brand">Yurt v1.0</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"><span class="navbar-toggler-icon"></span></button>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0 offcanvas-md offcanvas-start" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header d-md-none border-bottom border-secondary">
                <h5 class="offcanvas-title text-white">Menü</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button>
            </div>
            <div class="offcanvas-body d-block p-0">
                <h4 class="text-center py-4 border-bottom border-secondary d-none d-md-block">Yurt v1.0</h4>
                <div class="d-flex flex-column mt-2">
                    <a href="dashboard.php" class="active"><i class="fas fa-home me-2"></i> Anasayfa</a>
                    <a href="personel_islemleri.php"><i class="fas fa-user-tie me-2"></i> Personel İşlemleri</a>
                    <a href="ogrenci_islemleri.php"><i class="fas fa-user-graduate me-2"></i> Öğrenci İşlemleri</a>
                    <a href="oda_islemleri.php"><i class="fas fa-bed me-2"></i> Oda Yönetimi</a>
                    <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                    <a href="yoklama_raporu.php"><i class="fas fa-calendar-check me-2"></i> Yoklama Raporu</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Hoş Geldin, <?php echo htmlspecialchars($isim); ?> 👋</h2>
                <span class="badge bg-secondary">Müdür Paneli</span>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6"><div class="card-box bg-users"><h3><i class="fas fa-users"></i> <?php echo $ogrenci_sayisi; ?></h3><p>Kayıtlı Öğrenci</p></div></div>
                <div class="col-md-3 col-sm-6"><div class="card-box bg-rooms"><h3><i class="fas fa-bed"></i> <?php echo $oda_sayisi; ?></h3><p>Toplam Oda</p></div></div>
                <div class="col-md-3 col-sm-6"><div class="card-box bg-money"><h3><i class="fas fa-door-open"></i> <?php echo $bos_yatak; ?></h3><p>Boş Yatak</p></div></div>
                <div class="col-md-3 col-sm-6"><div class="card-box bg-alert"><h3><i class="fas fa-clipboard-check"></i> <?php echo $yoklama_sayisi; ?></h3><p>Bugünkü Yoklama</p></div></div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>