<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) { header("Location: ../index.php"); exit; }

$isim = $_SESSION['full_name'];
$bugun = date("d.m.Y");
$toplam_ogrenci = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 3")->fetchColumn();
try { $bekleyen_ariza = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Bekliyor'")->fetchColumn(); } catch(Exception $e){ $bekleyen_ariza=0; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/personel.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark d-md-none p-3 mb-3"><div class="container-fluid"><span class="navbar-brand">Personel Paneli</span><button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"><span class="navbar-toggler-icon"></span></button></div></nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0 offcanvas-md offcanvas-start" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header d-md-none border-bottom border-secondary"><h5 class="offcanvas-title text-white">Menü</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button></div>
            <div class="offcanvas-body d-block p-0">
                <h4 class="text-center py-4 border-bottom border-secondary d-none d-md-block">Personel</h4>
                <div class="d-flex flex-column mt-2">
                    <a href="dashboard.php" class="active"><i class="fas fa-home me-2"></i> Anasayfa</a>
                    <a href="yoklama_al.php"><i class="fas fa-clipboard-check me-2"></i> Yoklama Al</a>
                    <a href="ogrenci_listesi.php"><i class="fas fa-list me-2"></i> Öğrenci Listesi</a>
                    <a href="ariza_takip.php"><i class="fas fa-tools me-2"></i> Arıza Takip</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4"><h2>Merhaba, <?php echo htmlspecialchars($isim); ?> 👋</h2><span class="badge bg-secondary"><?php echo $bugun; ?></span></div>
            <div class="row">
                <div class="col-md-4 col-sm-6"><div class="card-box bg-students"><h3><i class="fas fa-user-graduate"></i> <?php echo $toplam_ogrenci; ?></h3><p>Kayıtlı Öğrenci</p></div></div>
                <div class="col-md-4 col-sm-6"><div class="card-box bg-attendance"><h3><i class="fas fa-check-circle"></i> Yoklama</h3><p>Bugünkü yoklama ekranı</p><a href="yoklama_al.php" class="btn btn-sm btn-light text-dark">Git</a></div></div>
                <div class="col-md-4 col-sm-12"><div class="card-box bg-danger-custom"><h3><i class="fas fa-exclamation-triangle"></i> <?php echo $bekleyen_ariza; ?></h3><p>Bekleyen Arıza</p><a href="ariza_takip.php" class="btn btn-sm btn-light text-dark">İncele</a></div></div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>