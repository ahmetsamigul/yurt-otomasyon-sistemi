<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) { header("Location: ../index.php"); exit; }

$mesaj = ""; $bugun = date("Y-m-d");
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yoklama_kaydet'])) {
    if (isset($_POST['durum']) && is_array($_POST['durum'])) {
        try {
            $pdo->beginTransaction();
            foreach ($_POST['durum'] as $sid => $status) {
                $var_mi = $pdo->prepare("SELECT attendance_id FROM attendance WHERE student_id = ? AND date = ?");
                $var_mi->execute([$sid, $bugun]); $kayit = $var_mi->fetch();
                if ($kayit) { $pdo->prepare("UPDATE attendance SET status = ?, created_by = ? WHERE attendance_id = ?")->execute([$status, $_SESSION['user_id'], $kayit->attendance_id]); }
                else { $pdo->prepare("INSERT INTO attendance (student_id, date, status, created_by) VALUES (?, ?, ?, ?)")->execute([$sid, $bugun, $status, $_SESSION['user_id']]); }
            }
            $pdo->commit(); $mesaj = "<div class='alert alert-success'>Yoklama kaydedildi!</div>";
        } catch (Exception $e) { $pdo->rollBack(); $mesaj = "<div class='alert alert-danger'>Hata.</div>"; }
    }
}
$sql = "SELECT s.student_id, u.full_name, r.room_number, a.status as bugunku_durum FROM students s JOIN users u ON s.user_id=u.user_id LEFT JOIN rooms r ON s.room_id=r.room_id LEFT JOIN attendance a ON s.student_id=a.student_id AND a.date='$bugun' ORDER BY u.full_name ASC";
$ogrenciler = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yoklama Al</title>
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
                    <a href="yoklama_al.php" class="active"><i class="fas fa-clipboard-check me-2"></i> Yoklama Al</a>
                    <a href="ogrenci_listesi.php"><i class="fas fa-list me-2"></i> Öğrenci Listesi</a>
                    <a href="ariza_takip.php"><i class="fas fa-tools me-2"></i> Arıza Takip</a>
                    <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Günlük Yoklama (<?php echo date("d.m.Y"); ?>)</h2>
            <?php echo $mesaj; ?>
            <form method="POST">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead><tr><th>Öğrenci</th><th>Oda</th><th class="text-center">Durum</th></tr></thead>
                            <tbody>
                                <?php foreach ($ogrenciler as $o): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($o->full_name); ?></td><td><?php echo $o->room_number; ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="durum[<?php echo $o->student_id; ?>]" id="v<?php echo $o->student_id; ?>" value="var" <?php echo ($o->bugunku_durum=='var'||!$o->bugunku_durum)?'checked':''; ?>><label class="btn btn-outline-success" for="v<?php echo $o->student_id; ?>">Var</label>
                                            <input type="radio" class="btn-check" name="durum[<?php echo $o->student_id; ?>]" id="y<?php echo $o->student_id; ?>" value="yok" <?php echo ($o->bugunku_durum=='yok')?'checked':''; ?>><label class="btn btn-outline-danger" for="y<?php echo $o->student_id; ?>">Yok</label>
                                            <input type="radio" class="btn-check" name="durum[<?php echo $o->student_id; ?>]" id="i<?php echo $o->student_id; ?>" value="izinli" <?php echo ($o->bugunku_durum=='izinli')?'checked':''; ?>><label class="btn btn-outline-warning" for="i<?php echo $o->student_id; ?>">İzinli</label>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white text-end"><button type="submit" name="yoklama_kaydet" class="btn btn-primary btn-lg">Kaydet</button></div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>