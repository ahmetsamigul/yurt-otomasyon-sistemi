<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header("Location: ../index.php");
    exit;
}

$mesaj = "";

// 1. ARIZA KAYDI OLUŞTURMA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ariza_ekle'])) {
    $baslik = trim($_POST['title']);
    $aciklama = trim($_POST['description']);
    
    if (!empty($baslik) && !empty($aciklama)) {
        $stmt = $pdo->prepare("INSERT INTO complaints (user_id, title, description) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $baslik, $aciklama]);
        $mesaj = "<div class='alert alert-success'>Arıza bildiriminiz iletildi.</div>";
    } else {
        $mesaj = "<div class='alert alert-warning'>Lütfen alanları doldurun.</div>";
    }
}

// 2. KENDİ ARIZALARINI LİSTELEME
$stmt = $pdo->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$arizalar = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Arıza Bildir - Öğrenci Paneli</title>
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
                <a href="profilim.php"><i class="fas fa-user me-2"></i> Profilim</a>
                <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                <a href="ariza_bildir.php" class="active"><i class="fas fa-tools me-2"></i> Arıza Bildir</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Arıza ve Şikayet Bildirimi</h2>
            <?php echo $mesaj; ?>

            <div class="row">
                <div class="col-md-5">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white"><i class="fas fa-plus"></i> Yeni Kayıt</div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label>Konu (Örn: Musluk Damlatıyor)</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Açıklama</label>
                                    <textarea name="description" class="form-control" rows="4" required></textarea>
                                </div>
                                <button type="submit" name="ariza_ekle" class="btn btn-success w-100">Gönder</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><i class="fas fa-list"></i> Bildirimlerim</div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead><tr><th>Konu</th><th>Tarih</th><th>Durum</th></tr></thead>
                                <tbody>
                                    <?php foreach ($arizalar as $a): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($a->title); ?></td>
                                        <td><?php echo date("d.m.Y", strtotime($a->created_at)); ?></td>
                                        <td>
                                            <?php 
                                            if($a->status == 'Bekliyor') echo '<span class="badge bg-danger">Bekliyor</span>';
                                            elseif($a->status == 'İşleme Alındı') echo '<span class="badge bg-warning text-dark">İnceleniyor</span>';
                                            else echo '<span class="badge bg-success">Çözüldü</span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>