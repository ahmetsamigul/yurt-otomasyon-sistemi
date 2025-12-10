<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) { header("Location: ../index.php"); exit; }

$mesaj = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ariza_ekle'])) {
    $stmt = $pdo->prepare("INSERT INTO complaints (user_id, title, description) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['title'], $_POST['description']]);
    $mesaj = "<div class='alert alert-success'>Bildirildi.</div>";
}
$stmt = $pdo->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]); $arizalar = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arıza Bildir</title>
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
                    <a href="profilim.php"><i class="fas fa-user me-2"></i> Profilim & Yoklama</a>
                    <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                    <a href="ariza_bildir.php" class="active"><i class="fas fa-tools me-2"></i> Arıza Bildir</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Arıza Bildir</h2>
            <?php echo $mesaj; ?>
            <div class="row">
                <div class="col-md-5">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">Yeni Kayıt</div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3"><label>Konu</label><input type="text" name="title" class="form-control" required></div>
                                <div class="mb-3"><label>Açıklama</label><textarea name="description" class="form-control" rows="4" required></textarea></div>
                                <button type="submit" name="ariza_ekle" class="btn btn-success w-100">Gönder</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card shadow-sm"><div class="card-body"><table class="table table-striped">
                        <thead><tr><th>Konu</th><th>Durum</th></tr></thead>
                        <tbody>
                            <?php foreach ($arizalar as $a): ?>
                            <tr><td><?php echo htmlspecialchars($a->title); ?></td><td><span class="badge <?php echo ($a->status=='Çözüldü')?'bg-success':'bg-warning'; ?>"><?php echo $a->status; ?></span></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>