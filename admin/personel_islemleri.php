<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) { header("Location: ../index.php"); exit; }

$mesaj = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['personel_ekle'])) {
    $ad = trim($_POST['full_name']); $email = trim($_POST['email']); $sifre = trim($_POST['password']);
    if (!empty($ad) && !empty($email) && !empty($sifre)) {
        $hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, 2)");
            $stmt->execute([$ad, $email, $hashli_sifre]);
            $mesaj = "<div class='alert alert-success'>Personel eklendi!</div>";
        } catch (PDOException $e) { $mesaj = "<div class='alert alert-danger'>Hata: E-posta kayıtlı olabilir.</div>"; }
    }
}
if (isset($_GET['sil_id']) && $_GET['sil_id'] != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role_id = 2");
    $stmt->execute([$_GET['sil_id']]);
    header("Location: personel_islemleri.php"); exit;
}
$personeller = $pdo->query("SELECT * FROM users WHERE role_id = 2 ORDER BY user_id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel İşlemleri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark d-md-none p-3 mb-3">
    <div class="container-fluid"><span class="navbar-brand">Yurt v1.0</span><button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"><span class="navbar-toggler-icon"></span></button></div>
</nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0 offcanvas-md offcanvas-start" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header d-md-none border-bottom border-secondary"><h5 class="offcanvas-title text-white">Menü</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button></div>
            <div class="offcanvas-body d-block p-0">
                <h4 class="text-center py-4 border-bottom border-secondary d-none d-md-block">Yurt v1.0</h4>
                <div class="d-flex flex-column mt-2">
                    <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                    <a href="personel_islemleri.php" class="active"><i class="fas fa-user-tie me-2"></i> Personel İşlemleri</a>
                    <a href="ogrenci_islemleri.php"><i class="fas fa-user-graduate me-2"></i> Öğrenci İşlemleri</a>
                    <a href="oda_islemleri.php"><i class="fas fa-bed me-2"></i> Oda Yönetimi</a>
                    <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                    <a href="yoklama_raporu.php"><i class="fas fa-calendar-check me-2"></i> Yoklama Raporu</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Personel Yönetimi</h2>
            <?php echo $mesaj; ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white"><i class="fas fa-plus-circle"></i> Yeni Personel</div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-4"><label>Ad Soyad</label><input type="text" name="full_name" class="form-control" required></div>
                        <div class="col-md-4"><label>E-Posta</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-3"><label>Şifre</label><input type="text" name="password" class="form-control" placeholder="123456" required></div>
                        <div class="col-md-1 d-flex align-items-end"><button type="submit" name="personel_ekle" class="btn btn-success w-100">Ekle</button></div>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light"><tr><th>ID</th><th>Ad Soyad</th><th>E-Posta</th><th>İşlem</th></tr></thead>
                        <tbody>
                            <?php foreach ($personeller as $p): ?>
                                <tr>
                                    <td><?php echo $p->user_id; ?></td><td><?php echo htmlspecialchars($p->full_name); ?></td><td><?php echo htmlspecialchars($p->email); ?></td>
                                    <td>
                                        <a href="personel_duzenle.php?id=<?php echo $p->user_id; ?>" class="btn btn-sm btn-warning text-white me-1"><i class="fas fa-edit"></i> Düzenle</a>
                                        <a href="?sil_id=<?php echo $p->user_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Silinsin mi?');"><i class="fas fa-trash"></i> Sil</a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json'
                }
            });
        });
    </script>
</body>
</html>