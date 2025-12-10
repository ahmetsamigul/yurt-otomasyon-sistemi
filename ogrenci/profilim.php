<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) { header("Location: ../index.php"); exit; }

$stmt = $pdo->prepare("SELECT u.full_name, u.email, s.department, s.phone, r.room_number, s.student_id FROM users u JOIN students s ON u.user_id=s.user_id LEFT JOIN rooms r ON s.room_id=r.room_id WHERE u.user_id=?");
$stmt->execute([$_SESSION['user_id']]); $ogr = $stmt->fetch();
$yoklamalar = [];
if ($ogr) {
    $yoklamalar = $pdo->prepare("SELECT * FROM attendance WHERE student_id=? ORDER BY date DESC");
    $yoklamalar->execute([$ogr->student_id]); $yoklamalar=$yoklamalar->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim</title>
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
                    <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                    <a href="profilim.php" class="active"><i class="fas fa-user me-2"></i> Profilim & Yoklama</a>
                    <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                    <a href="ariza_bildir.php"><i class="fas fa-tools me-2"></i> Arıza Bildir</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Profilim</h2>
            <div class="row">
                <div class="col-md-5">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-dark"><i class="fas fa-id-card"></i> Bilgiler</div>
                        <div class="card-body">
                            <p><strong>Ad:</strong> <?php echo htmlspecialchars($ogr->full_name); ?></p>
                            <p><strong>Bölüm:</strong> <?php echo htmlspecialchars($ogr->department); ?></p>
                            <p><strong>Oda:</strong> <?php echo $ogr->room_number ?? 'Yok'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white"><h5>Yoklama Geçmişi</h5></div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead><tr><th>Tarih</th><th>Durum</th></tr></thead>
                                <tbody>
                                    <?php foreach ($yoklamalar as $y): ?>
                                    <tr><td><?php echo date("d.m.Y", strtotime($y->date)); ?></td><td><?php echo $y->status; ?></td></tr>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>