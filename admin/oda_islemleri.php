<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) { header("Location: ../index.php"); exit; }

$mesaj = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['oda_ekle'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO rooms (room_number, capacity, current_count) VALUES (?, ?, 0)");
        $stmt->execute([$_POST['room_number'], $_POST['capacity']]);
        $mesaj = "<div class='alert alert-success'>Oda eklendi!</div>";
    } catch (PDOException $e) { $mesaj = "<div class='alert alert-danger'>Hata oluştu.</div>"; }
}
if (isset($_GET['sil_id'])) {
    try { $pdo->prepare("DELETE FROM rooms WHERE room_id = ?")->execute([$_GET['sil_id']]); header("Location: oda_islemleri.php"); exit; }
    catch (PDOException $e) { $mesaj = "<div class='alert alert-danger'>Önce odayı boşaltın.</div>"; }
}
$odalar = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oda Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark d-md-none p-3 mb-3"><div class="container-fluid"><span class="navbar-brand">Yurt v1.0</span><button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"><span class="navbar-toggler-icon"></span></button></div></nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0 offcanvas-md offcanvas-start" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header d-md-none border-bottom border-secondary"><h5 class="offcanvas-title text-white">Menü</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button></div>
            <div class="offcanvas-body d-block p-0">
                <h4 class="text-center py-4 border-bottom border-secondary d-none d-md-block">Yurt v1.0</h4>
                <div class="d-flex flex-column mt-2">
                    <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                    <a href="personel_islemleri.php"><i class="fas fa-user-tie me-2"></i> Personel İşlemleri</a>
                    <a href="ogrenci_islemleri.php"><i class="fas fa-user-graduate me-2"></i> Öğrenci İşlemleri</a>
                    <a href="oda_islemleri.php" class="active"><i class="fas fa-bed me-2"></i> Oda Yönetimi</a>
                    <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                    <a href="yoklama_raporu.php"><i class="fas fa-calendar-check me-2"></i> Yoklama Raporu</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Oda Yönetimi</h2>
            <?php echo $mesaj; ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white"><i class="fas fa-plus-circle"></i> Yeni Oda</div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-5"><label>Oda No</label><input type="text" name="room_number" class="form-control" required></div>
                        <div class="col-md-5"><label>Kapasite</label><input type="number" name="capacity" class="form-control" required></div>
                        <div class="col-md-2 d-flex align-items-end"><button type="submit" name="oda_ekle" class="btn btn-primary w-100">Oluştur</button></div>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered text-center">
                        <thead class="table-light"><tr><th>Oda No</th><th>Kapasite</th><th>Doluluk</th><th>Durum</th><th>İşlem</th></tr></thead>
                        <tbody>
                            <?php foreach ($odalar as $o): $doluluk = ($o->current_count/$o->capacity)*100; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o->room_number); ?></td>
                                <td><?php echo $o->capacity; ?></td>
                                <td><?php echo $o->current_count; ?> / <?php echo $o->capacity; ?></td>
                                <td><div class="progress" style="height:20px;"><div class="progress-bar bg-success" style="width:<?php echo $doluluk; ?>%">%<?php echo round($doluluk); ?></div></div></td>
                                <td><a href="?sil_id=<?php echo $o->room_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Silinsin mi?');">Sil</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>