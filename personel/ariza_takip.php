<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) { header("Location: ../index.php"); exit; }

if (isset($_POST['durum_guncelle'])) {
    $pdo->prepare("UPDATE complaints SET status = ? WHERE complaint_id = ?")->execute([$_POST['yeni_durum'], $_POST['id']]);
}
$arizalar = $pdo->query("SELECT c.*, u.full_name, r.room_number FROM complaints c JOIN users u ON c.user_id=u.user_id LEFT JOIN students s ON u.user_id=s.user_id LEFT JOIN rooms r ON s.room_id=r.room_id ORDER BY c.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arıza Takip</title>
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
                    <a href="ogrenci_listesi.php"><i class="fas fa-list me-2"></i> Öğrenci Listesi</a>
                    <a href="ariza_takip.php" class="active"><i class="fas fa-tools me-2"></i> Arıza Takip</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Arıza Takip</h2>
            <div class="card shadow-sm"><div class="card-body"><table class="table table-hover">
                <thead class="table-dark"><tr><th>Öğrenci</th><th>Oda</th><th>Konu</th><th>Açıklama</th><th>Durum</th><th>İşlem</th></tr></thead>
                <tbody>
                    <?php foreach ($arizalar as $a): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a->full_name); ?></td><td><?php echo $a->room_number; ?></td><td><?php echo htmlspecialchars($a->title); ?></td><td><?php echo htmlspecialchars($a->description); ?></td>
                        <td><span class="badge <?php echo ($a->status=='Çözüldü')?'bg-success':(($a->status=='Bekliyor')?'bg-danger':'bg-warning'); ?>"><?php echo $a->status; ?></span></td>
                        <td>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="id" value="<?php echo $a->complaint_id; ?>">
                                <select name="yeni_durum" class="form-select form-select-sm me-2"><option value="Bekliyor">Bekliyor</option><option value="İşleme Alındı">İşlemde</option><option value="Çözüldü">Çözüldü</option></select>
                                <button type="submit" name="durum_guncelle" class="btn btn-sm btn-primary">OK</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>