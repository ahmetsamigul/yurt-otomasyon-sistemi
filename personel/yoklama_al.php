<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header("Location: ../index.php");
    exit;
}

$mesaj = "";
$bugun = date("Y-m-d");

// 1. YOKLAMA KAYDETME İŞLEMİ (Toplu Kayıt)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yoklama_kaydet'])) {
    if (isset($_POST['durum']) && is_array($_POST['durum'])) {
        try {
            $pdo->beginTransaction();
            
            // Seçilen her öğrenci için döngü
            foreach ($_POST['durum'] as $student_id => $status) {
                // Önce bu öğrenci için bugün zaten kayıt var mı bakalım?
                $kontrol = $pdo->prepare("SELECT attendance_id FROM attendance WHERE student_id = ? AND date = ?");
                $kontrol->execute([$student_id, $bugun]);
                $var_mi = $kontrol->fetch();

                if ($var_mi) {
                    // Varsa GÜNCELLE
                    $stmt = $pdo->prepare("UPDATE attendance SET status = ?, created_by = ? WHERE attendance_id = ?");
                    $stmt->execute([$status, $_SESSION['user_id'], $var_mi->attendance_id]);
                } else {
                    // Yoksa EKLE
                    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status, created_by) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$student_id, $bugun, $status, $_SESSION['user_id']]);
                }
            }
            
            $pdo->commit();
            $mesaj = "<div class='alert alert-success'>Bugünün ($bugun) yoklaması başarıyla kaydedildi!</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mesaj = "<div class='alert alert-danger'>Hata: " . $e->getMessage() . "</div>";
        }
    } else {
        $mesaj = "<div class='alert alert-warning'>Listede öğrenci bulunamadı.</div>";
    }
}

// 2. ÖĞRENCİ LİSTESİNİ VE BUGÜNKÜ DURUMLARINI ÇEK
// Left Join ile attendance tablosunu bağlıyoruz ki daha önce işaretlediysek seçili gelsin
$sql = "SELECT s.student_id, u.full_name, r.room_number, a.status as bugunku_durum 
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        LEFT JOIN rooms r ON s.room_id = r.room_id
        LEFT JOIN attendance a ON s.student_id = a.student_id AND a.date = '$bugun'
        ORDER BY u.full_name ASC";
$ogrenciler = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yoklama Al - Personel Paneli</title>
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
                <a href="yoklama_al.php" class="active"><i class="fas fa-clipboard-check me-2"></i> Yoklama Al</a>
                <a href="ogrenci_listesi.php"><i class="fas fa-list me-2"></i> Öğrenci Listesi</a>
                <a href="ariza_takip.php"><i class="fas fa-tools me-2"></i> Arıza Takip</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Günlük Yoklama Listesi (<?php echo date("d.m.Y"); ?>)</h2>
            
            <?php echo $mesaj; ?>

            <form method="POST" action="">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Öğrenci Adı</th>
                                    <th>Oda No</th>
                                    <th class="text-center">Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($ogrenciler) > 0): ?>
                                    <?php foreach ($ogrenciler as $ogr): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ogr->full_name); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $ogr->room_number ?? '-'; ?></span></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    
                                                    <input type="radio" class="btn-check" name="durum[<?php echo $ogr->student_id; ?>]" 
                                                           id="var_<?php echo $ogr->student_id; ?>" value="var" 
                                                           <?php echo ($ogr->bugunku_durum == 'var' || $ogr->bugunku_durum == null) ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-success" for="var_<?php echo $ogr->student_id; ?>">Var</label>

                                                    <input type="radio" class="btn-check" name="durum[<?php echo $ogr->student_id; ?>]" 
                                                           id="yok_<?php echo $ogr->student_id; ?>" value="yok" 
                                                           <?php echo ($ogr->bugunku_durum == 'yok') ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-danger" for="yok_<?php echo $ogr->student_id; ?>">Yok</label>

                                                    <input type="radio" class="btn-check" name="durum[<?php echo $ogr->student_id; ?>]" 
                                                           id="izin_<?php echo $ogr->student_id; ?>" value="izinli" 
                                                           <?php echo ($ogr->bugunku_durum == 'izinli') ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-warning" for="izin_<?php echo $ogr->student_id; ?>">İzinli</label>
                                                
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted">Kayıtlı öğrenci bulunamadı.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white text-end">
                        <button type="submit" name="yoklama_kaydet" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Yoklamayı Kaydet
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>