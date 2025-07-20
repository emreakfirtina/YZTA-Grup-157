<?php
ob_start(); 
include("connect.php"); 
include '../connection.php';
if($_SESSION['name']==''){
	header("location:deliverylogin.php");
}
$name=$_SESSION['name'];
$city=$_SESSION['city'];
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,"http://ip-api.com/json");
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
$result=curl_exec($ch);
$result=json_decode($result);

$id=$_SESSION['Did'];

// Süt tiplerini Türkçe'ye çeviren fonksiyon
function translateMilkType($milk_type) {
    $translations = array(
        'cow' => 'İnek Sütü',
        'goat' => 'Keçi Sütü',
        'sheep' => 'Koyun Sütü'
    );
    
    return isset($translations[strtolower($milk_type)]) ? $translations[strtolower($milk_type)] : $milk_type;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Kurye Dashboard - Balkan Süt</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        /* Sol Sidebar Menü */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background-color: #253078;
            color: white;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #6BBE45;
            text-align: center;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            display: block;
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            padding: 15px 25px;
            border-radius: 0 25px 25px 0;
            margin-right: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: #1a2357;
            color: #6BBE45;
            transform: translateX(10px);
        }

        .sidebar-menu a.active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 30px;
            background-color: #6BBE45;
            border-radius: 2px;
        }

        /* Ana içerik alanı */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .dashboard-header {
            background-color: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 10px;
        }

        .dashboard-subtitle {
            color: #666;
            font-size: 16px;
        }

        /* Bildirim mesajları */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        /* Tablo Bölümü */
        .table-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }

        .table-header {
            font-size: 24px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 20px;
            border-bottom: 2px solid #6BBE45;
            padding-bottom: 10px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #253078;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .btn-take {
            background-color: #6BBE45;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-right: 5px;
        }

        .btn-take:hover {
            background-color: #5aa83a;
        }

        .btn-complete {
            background-color: #007bff;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-complete:hover {
            background-color: #0056b3;
        }

        .status-assigned {
            color: green;
            font-weight: bold;
        }

        .status-other {
            color: orange;
            font-weight: bold;
        }

        .status-in-progress {
            color: #007bff;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .milk-type {
            background-color: #e8f5e8;
            color: #2d5016;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .quantity {
            color: #253078;
            font-weight: bold;
        }

        .delivery-timer {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        /* Responsive düzenlemeler */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .dashboard-header {
                padding: 20px;
            }

            .table-section {
                padding: 20px;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>

    <!-- Sol Sidebar Menü -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">KURYE PANELİ</div>
        </div>
        <nav class="sidebar-menu">
            <ul>
                <li><a href="#" class="active"><i class="fa fa-home"></i> Ana Sayfa</a></li>
                <li><a href="openmap.php"><i class="fa fa-map"></i> Harita</a></li>
                <li><a href="deliverymyord.php"><i class="fa fa-truck"></i> Siparişlerim</a></li>
                <li><a href="../logout.php"><i class="fa fa-sign-out"></i> Çıkış</a></li>
            </ul>
        </nav>
    </div>

    <!-- Ana İçerik -->
    <div class="main-content">
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="dashboard-title">Balkan Süt Kurye Sistemi</div>
            <div class="dashboard-subtitle">Hoş geldiniz <?php echo $name; ?> - Mevcut Süt Toplama Talepleri</div>
        </div>

        <?php
        $message = '';
        $message_type = '';

        // Siparişi Al işlemi
        if (isset($_POST['take_order']) && isset($_POST['delivery_person_id'])) {
            $order_id = $_POST['order_id'];
            $delivery_person_id = $_POST['delivery_person_id'];
            
            // Önce siparişin durumunu kontrol et
            $sql_check = "SELECT * FROM food_donations WHERE Fid = $order_id AND delivery_by IS NOT NULL";
            $result_check = mysqli_query($connection, $sql_check);

            if (mysqli_num_rows($result_check) > 0) {
                $message = "Bu sipariş zaten başka bir kuryeye atanmış!";
                $message_type = 'error';
            } else {
                // Siparişi al ve başlangıç zamanını kaydet
                $current_time = date('Y-m-d H:i:s');
                $sql_update = "UPDATE food_donations SET 
                              delivery_by = $delivery_person_id, 
                              delivery_start_time = '$current_time' 
                              WHERE Fid = $order_id";
                $result_update = mysqli_query($connection, $sql_update);

                if ($result_update) {
                    $message = "Sipariş başarıyla alındı! Teslimat zamanı kayıt altına alındı.";
                    $message_type = 'success';
                } else {
                    $message = "Sipariş atama hatası: " . mysqli_error($connection);
                    $message_type = 'error';
                }
            }
        }

        // Teslim İşlemi
        if (isset($_POST['complete_delivery'])) {
            $order_id = $_POST['order_id'];
            $current_time = date('Y-m-d H:i:s');
            
            // Başlangıç zamanını al
            $sql_get_start = "SELECT delivery_start_time FROM food_donations WHERE Fid = $order_id";
            $result_start = mysqli_query($connection, $sql_get_start);
            $start_data = mysqli_fetch_assoc($result_start);
            
            if ($start_data && $start_data['delivery_start_time']) {
                // Süreyi hesapla (dakika cinsinden)
                $start_time = new DateTime($start_data['delivery_start_time']);
                $end_time = new DateTime($current_time);
                $duration_minutes = $end_time->diff($start_time)->i + ($end_time->diff($start_time)->h * 60);
                
                // Teslim bilgilerini güncelle
                $sql_complete = "UPDATE food_donations SET 
                                delivery_end_time = '$current_time',
                                delivery_duration_minutes = $duration_minutes
                                WHERE Fid = $order_id AND delivery_by = $id";
                
                $result_complete = mysqli_query($connection, $sql_complete);
                
                if ($result_complete) {
                    $message = "Teslimat başarıyla tamamlandı! Süre: $duration_minutes dakika";
                    $message_type = 'success';
                } else {
                    $message = "Teslimat tamamlama hatası: " . mysqli_error($connection);
                    $message_type = 'error';
                }
            } else {
                $message = "Başlangıç zamanı bulunamadı!";
                $message_type = 'error';
            }
        }

        // Mesajı göster
        if ($message) {
            echo "<div class='alert alert-$message_type'>$message</div>";
        }

        // Aktif siparişleri getir (teslim edilmemiş olanlar)
        $sql = "SELECT fd.Fid AS Fid, fd.location as cure, fd.name, fd.phoneno, fd.date, fd.delivery_by, 
               fd.address as From_address, fd.quantity, fd.milk_type, fd.delivery_start_time,
               ad.name AS delivery_person_name, ad.address AS To_address
        FROM food_donations fd
        LEFT JOIN admin ad ON fd.assigned_to = ad.Aid 
        WHERE assigned_to IS NOT NULL 
        AND delivery_end_time IS NULL
        AND (delivery_by IS NULL OR delivery_by = $id)";

        $result = mysqli_query($connection, $sql);

        if (!$result) {
            die("Veritabanı hatası: " . mysqli_error($connection));
        }

        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        // Süre hesaplama fonksiyonu
        function calculateElapsedTime($start_time) {
            if (!$start_time) return null;
            
            $start = new DateTime($start_time);
            $now = new DateTime();
            $diff = $now->diff($start);
            
            if ($diff->h > 0) {
                return $diff->h . "s " . $diff->i . "dk";
            } else {
                return $diff->i . " dakika";
            }
        }
        ?>

        <!-- Süt Toplama Talepleri Tablosu -->
        <div class="table-section">
            <div class="table-header">
                <i class="fa fa-list"></i> Aktif Süt Toplama Talepleri
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>İsim</th>
                            <th>Telefon</th>
                            <th>Tarih/Saat</th>
                            <th>Adres</th>
                            <th>Miktar</th>
                            <th>Süt Tipi</th>
                            <th>Durum</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['phoneno']) ?></td>
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['From_address']) ?></td>
                                <td><span class="quantity"><?= htmlspecialchars($row['quantity']) ?> L</span></td>
                                <td><span class="milk-type"><?= translateMilkType($row['milk_type'] ?? 'Belirtilmemiş') ?></span></td>
                                <td>
                                    <?php if ($row['delivery_by'] == null) { ?>
                                        <span style="color: #666;">Bekliyor</span>
                                    <?php } else if ($row['delivery_by'] == $id) { ?>
                                        <div class="status-in-progress">
                                            <span>Teslimatta</span>
                                            <?php if ($row['delivery_start_time']) { ?>
                                                <span class="delivery-timer">
                                                    <?= calculateElapsedTime($row['delivery_start_time']) ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    <?php } else { ?>
                                        <span class="status-other">Başka Kuryede</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($row['delivery_by'] == null) { ?>
                                        <form method="post" action="" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?= $row['Fid'] ?>">
                                            <input type="hidden" name="delivery_person_id" value="<?= $id ?>">
                                            <button type="submit" name="take_order" class="btn-take">
                                                <i class="fa fa-hand-paper-o"></i> Siparişi Al
                                            </button>
                                        </form>
                                    <?php } else if ($row['delivery_by'] == $id) { ?>
                                        <form method="post" action="" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?= $row['Fid'] ?>">
                                            <button type="submit" name="complete_delivery" class="btn-complete">
                                                <i class="fa fa-check"></i> Teslim Edildi
                                            </button>
                                        </form>
                                    <?php } else { ?>
                                        <span class="status-other">Müsait Değil</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        
                        <?php if (empty($data)) { ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #666; padding: 20px;">
                                    Şu anda aktif süt toplama talebi bulunmamaktadır.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Sayfayı her 30 saniyede bir yenile (süre güncellemesi için)
        setInterval(function() {
            // Sadece aktif teslimat varsa yenile
            const hasActiveDelivery = document.querySelector('.status-in-progress');
            if (hasActiveDelivery) {
                location.reload();
            }
        }, 30000);
    </script>

</body>
</html>