<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) { header("Location: ../index.php"); exit; }

// DURUM GÜNCELLEME
if (isset($_POST['durum_guncelle'])) {
    $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE complaint_id = ?");
    $stmt->execute([$_POST['yeni_durum'], $_POST['id']]);
}

// LİSTELEME
$sql = "SELECT c.*, u.full_name, r.room_number FROM complaints c 
        JOIN users u ON c.user_id = u.user_id 
        LEFT JOIN students s ON u.user_id = s.user_id
        LEFT JOIN rooms r ON s.room_id = r.room_id
        ORDER BY c.created_at DESC";
$arizalar = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Arıza Takip - Personel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; color: white; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; color: white; border-left: 4px solid #1abc9c; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <h4 class="text-center py-4 border-bottom border-secondary">Personel</h4>
            <div class="d-flex flex-column">
                <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                <a href="yoklama_al.php"><i class="fas fa-clipboard-check me-2"></i> Yoklama Al</a>
                <a href="ogrenci_listesi.php"><i class="fas fa-list me-2"></i> Öğrenci Listesi</a>
                <a href="ariza_takip.php" class="active"><i class="fas fa-tools me-2"></i> Arıza Takip</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Arıza Takip Ekranı</h2>
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-hover">
                        <thead class="table-dark"><tr><th>Öğrenci</th><th>Oda</th><th>Konu</th><th>Açıklama</th><th>Durum</th><th>İşlem</th></tr></thead>
                        <tbody>
                            <?php foreach ($arizalar as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a->full_name); ?></td>
                                <td><span class="badge bg-secondary"><?php echo $a->room_number ?? '-'; ?></span></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($a->title); ?></td>
                                <td><?php echo htmlspecialchars($a->description); ?></td>
                                <td>
                                    <?php 
                                    if($a->status == 'Bekliyor') echo '<span class="badge bg-danger">Bekliyor</span>';
                                    elseif($a->status == 'İşleme Alındı') echo '<span class="badge bg-warning text-dark">İnceleniyor</span>';
                                    else echo '<span class="badge bg-success">Çözüldü</span>';
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-flex">
                                        <input type="hidden" name="id" value="<?php echo $a->complaint_id; ?>">
                                        <select name="yeni_durum" class="form-select form-select-sm me-2" style="width: 130px;">
                                            <option value="Bekliyor">Bekliyor</option>
                                            <option value="İşleme Alındı">İşleme Al</option>
                                            <option value="Çözüldü">Çözüldü</option>
                                        </select>
                                        <button type="submit" name="durum_guncelle" class="btn btn-sm btn-primary">Güncelle</button>
                                    </form>
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
</body>
</html>