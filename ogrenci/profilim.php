<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. ÖĞRENCİ VE ODA BİLGİLERİNİ ÇEK
$sql = "SELECT u.full_name, u.email, s.department, s.phone, r.room_number, s.student_id 
        FROM users u 
        JOIN students s ON u.user_id = s.user_id 
        LEFT JOIN rooms r ON s.room_id = r.room_id 
        WHERE u.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$ogrenci = $stmt->fetch();

// 2. YOKLAMA GEÇMİŞİNİ ÇEK
$yoklamalar = [];
if ($ogrenci) {
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? ORDER BY date DESC");
    $stmt->execute([$ogrenci->student_id]);
    $yoklamalar = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Profilim - Yurt Otomasyonu</title>
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
                <a href="profilim.php" class="active"><i class="fas fa-user me-2"></i> Profilim & Yoklama</a>
                <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                <a href="ariza_bildir.php"><i class="fas fa-tools me-2"></i> Arıza Bildir</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Profil Bilgilerim</h2>

            <div class="row">
                <div class="col-md-5">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-id-card"></i> Kişisel Bilgiler
                        </div>
                        <div class="card-body">
                            <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($ogrenci->full_name); ?></p>
                            <p><strong>E-Posta:</strong> <?php echo htmlspecialchars($ogrenci->email); ?></p>
                            <p><strong>Bölüm:</strong> <?php echo htmlspecialchars($ogrenci->department); ?></p>
                            <p><strong>Telefon:</strong> <?php echo htmlspecialchars($ogrenci->phone); ?></p>
                            <hr>
                            <h5 class="text-primary"><i class="fas fa-bed"></i> Oda: <?php echo $ogrenci->room_number ?? 'Atanmamış'; ?></h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-history"></i> Yoklama Geçmişi</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($yoklamalar) > 0): ?>
                                        <?php foreach ($yoklamalar as $y): ?>
                                            <tr>
                                                <td><?php echo date("d.m.Y", strtotime($y->date)); ?></td>
                                                <td>
                                                    <?php 
                                                    if($y->status == 'var') echo '<span class="badge bg-success">Var</span>';
                                                    elseif($y->status == 'yok') echo '<span class="badge bg-danger">Yok</span>';
                                                    else echo '<span class="badge bg-warning text-dark">İzinli</span>';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="2" class="text-muted">Henüz yoklama kaydı yok.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>