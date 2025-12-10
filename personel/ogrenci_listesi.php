<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) { header("Location: ../index.php"); exit; }

$arama = ""; $params = [];
$sql = "SELECT u.full_name, u.email, s.department, s.phone, r.room_number FROM users u JOIN students s ON u.user_id=s.user_id LEFT JOIN rooms r ON s.room_id=r.room_id WHERE u.role_id=3";
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $arama = trim($_GET['q']);
    $sql .= " AND (u.full_name LIKE ? OR s.department LIKE ? OR r.room_number LIKE ?)";
    $params = ["%$arama%", "%$arama%", "%$arama%"];
}
$stmt = $pdo->prepare($sql . " ORDER BY u.full_name ASC"); $stmt->execute($params); $ogrenciler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Listesi</title>
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
                    <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                    <a href="yoklama_al.php"><i class="fas fa-clipboard-check me-2"></i> Yoklama Al</a>
                    <a href="ogrenci_listesi.php" class="active"><i class="fas fa-list me-2"></i> Öğrenci Listesi</a>
                    <a href="ariza_takip.php"><i class="fas fa-tools me-2"></i> Arıza Takip</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Öğrenci Listesi</h2>
            <form method="GET" class="d-flex mb-4 gap-2">
                <input type="text" name="q" class="form-control" placeholder="Ara..." value="<?php echo htmlspecialchars($arama); ?>">
                <button type="submit" class="btn btn-primary">Ara</button>
            </form>
            <div class="card shadow-sm"><div class="card-body"><table class="table table-hover">
                <thead class="table-dark"><tr><th>Ad Soyad</th><th>Bölüm</th><th>Telefon</th><th>Oda</th><th>E-Posta</th></tr></thead>
                <tbody>
                    <?php foreach ($ogrenciler as $o): ?>
                    <tr><td><?php echo htmlspecialchars($o->full_name); ?></td><td><?php echo htmlspecialchars($o->department); ?></td><td><?php echo htmlspecialchars($o->phone); ?></td><td><?php echo $o->room_number; ?></td><td><?php echo htmlspecialchars($o->email); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>