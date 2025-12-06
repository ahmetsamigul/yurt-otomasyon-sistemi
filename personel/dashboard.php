<?php
session_start();
require_once '../config/db.php';

// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header("Location: ../index.php");
    exit;
}

$isim = $_SESSION['full_name'];
$bugun = date("d.m.Y");

// İSTATİSTİKLER
$toplam_ogrenci = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 3")->fetchColumn();
// Bekleyen (Henüz çözülmemiş) arızaları say
$bekleyen_ariza = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Bekliyor'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Personel Paneli - Yurt Otomasyonu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; color: white; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; color: white; border-left: 4px solid #1abc9c; }
        .card-box { border-radius: 10px; color: white; padding: 20px; margin-bottom: 20px; }
        .bg-attendance { background-color: #1abc9c; }
        .bg-students { background-color: #3498db; }
        .bg-danger-custom { background-color: #e74c3c; } /* Arıza için kırmızı */
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <h4 class="text-center py-4 border-bottom border-secondary">Personel</h4>
            <div class="d-flex flex-column">
                <a href="dashboard.php" class="active"><i class="fas fa-home me-2"></i> Anasayfa</a>
                <a href="yoklama_al.php"><i class="fas fa-clipboard-check me-2"></i> Yoklama Al</a>
                <a href="ogrenci_listesi.php"><i class="fas fa-list me-2"></i> Öğrenci Listesi</a>
                <a href="ariza_takip.php"><i class="fas fa-tools me-2"></i> Arıza Takip</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Merhaba, <?php echo $isim; ?> 👋</h2>
                <span class="badge bg-secondary"><?php echo $bugun; ?></span>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card-box bg-students">
                        <h3><i class="fas fa-user-graduate"></i> <?php echo $toplam_ogrenci; ?></h3>
                        <p>Kayıtlı Öğrenci</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-box bg-attendance">
                        <h3><i class="fas fa-check-circle"></i> Yoklama</h3>
                        <p>Bugünkü yoklama ekranı</p>
                        <a href="yoklama_al.php" class="btn btn-sm btn-light text-dark">Git</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-box bg-danger-custom">
                        <h3><i class="fas fa-exclamation-triangle"></i> <?php echo $bekleyen_ariza; ?></h3>
                        <p>Bekleyen Arıza Bildirimi</p>
                        <a href="ariza_takip.php" class="btn btn-sm btn-light text-dark">İncele</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>