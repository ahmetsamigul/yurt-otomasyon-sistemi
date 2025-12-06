<?php
session_start();
require_once '../config/db.php';

// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header("Location: ../index.php");
    exit;
}

$isim = $_SESSION['full_name'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğrenci Paneli - Yurt Otomasyonu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; color: white; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; color: white; border-left: 4px solid #f1c40f; }
        .card-box { border-radius: 10px; color: white; padding: 20px; margin-bottom: 20px; }
        .bg-info-custom { background-color: #3498db; }
        .bg-warning-custom { background-color: #f39c12; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <h4 class="text-center py-4 border-bottom border-secondary">Öğrenci Paneli</h4>
            <div class="d-flex flex-column">
                <a href="dashboard.php" class="active"><i class="fas fa-home me-2"></i> Anasayfa</a>
                <a href="profilim.php"><i class="fas fa-user me-2"></i> Profilim & Yoklama</a>
                <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                <a href="ariza_bildir.php"><i class="fas fa-tools me-2"></i> Arıza Bildir</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <div class="alert alert-info">
                <h4>Hoş Geldin, <?php echo $isim; ?> 👋</h4>
                <p>Yurt yönetim sistemine hoş geldin.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card-box bg-info-custom">
                        <h3><i class="fas fa-user"></i> Profilim</h3>
                        <p>Oda ve Kişisel Bilgiler</p>
                        <a href="profilim.php" class="btn btn-sm btn-light text-dark">Görüntüle</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-box bg-warning-custom">
                        <h3><i class="fas fa-tools"></i> Destek</h3>
                        <p>Bir sorun mu var? Arıza bildirimi yap.</p>
                        <a href="ariza_bildir.php" class="btn btn-sm btn-light text-dark">Bildir</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>