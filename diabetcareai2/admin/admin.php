<?php
ob_start(); 
include("connect.php"); 
if($_SESSION['name']==''){
    header("location:signin.php");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Admin Dashboard - Balkan Süt</title>
    
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

        /* Dashboard Kutuları */
        .boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .box {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.3s ease;
        }

        .box:hover {
            transform: translateY(-5px);
        }

        .box1 {
            border-left: 5px solid #6BBE45;
        }

        .box2 {
            border-left: 5px solid #ff6b6b;
        }

        .box3 {
            border-left: 5px solid #4ecdc4;
        }

        .box i {
            font-size: 40px;
            margin-right: 20px;
        }

        .box1 i {
            color: #6BBE45;
        }

        .box2 i {
            color: #ff6b6b;
        }

        .box3 i {
            color: #4ecdc4;
        }

        .box-content {
            flex: 1;
        }

        .box-title {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }

        .box-number {
            font-size: 32px;
            font-weight: bold;
            color: #253078;
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

        .btn-approve {
            background-color: #6BBE45;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-approve:hover {
            background-color: #5aa83a;
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

            .boxes {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                padding: 20px;
            }

            .table-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Sol Sidebar Menü -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">ADMİN PANELİ</div>
        </div>
        <nav class="sidebar-menu">
            <ul>
                <li><a href="admin.php" class="active"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="analytics.php"><i class="fa fa-bar-chart"></i> Analizler</a></li>
                <li><a href="donate.php"><i class="fa fa-truck"></i> Süt Teslimatları</a></li>
                <li><a href="delivery.php"><i class="fa fa-users"></i> Kurye Performansı</a></li>
                <li><a href="invoice.php"><i class="fa fa-file-text"></i> Fatura Sistemi</a></li>
                <li><a href="feedback.php"><i class="fa fa-comments"></i> Destek Talepleri</a></li>
                
                <li><a href="../logout.php"><i class="fa fa-sign-out"></i> Çıkış</a></li>
            </ul>
        </nav>
    </div>

    <!-- Ana İçerik -->
    <div class="main-content">
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="dashboard-title">Balkan Süt Yönetim Sistemi</div>
            <div class="dashboard-subtitle">Admin Dashboard - Hoş geldiniz <?php echo $_SESSION['name']; ?></div>
        </div>

        <!-- Dashboard Kutuları -->
        <div class="boxes">
            <div class="box box1">
                <i class="fa fa-users"></i>
                <div class="box-content">
                    <div class="box-title">Toplam Kullanıcı</div>
                    <?php
                        $query = "SELECT count(*) as count FROM login";
                        $result = mysqli_query($connection, $query);
                        $row = mysqli_fetch_assoc($result);
                        echo "<div class=\"box-number\">".$row['count']."</div>";
                    ?>
                </div>
            </div>
            
            <div class="box box2">
                <i class="fa fa-support"></i>
                <div class="box-content">
                    <div class="box-title">Destek Talepleri</div>
                    <?php
                        $query = "SELECT count(*) as count FROM user_feedback";
                        $result = mysqli_query($connection, $query);
                        $row = mysqli_fetch_assoc($result);
                        echo "<div class=\"box-number\">".$row['count']."</div>";
                    ?>
                </div>
            </div>
            
            <div class="box box3">
                <i class="fa fa-tint"></i>
                <div class="box-content">
                    <div class="box-title">Ortalama Günlük Süt (Litre)</div>
                    <?php
                        $query = "SELECT AVG(quantity) as avg_quantity FROM food_donations";
                        $result = mysqli_query($connection, $query);
                        $row = mysqli_fetch_assoc($result);
                        $avg_quantity = round($row['avg_quantity'], 1);
                        echo "<div class=\"box-number\">".$avg_quantity."</div>";
                    ?>
                </div>
            </div>
        </div>

        <!-- Süt Teslim Talepleri Tablosu -->
        <div class="table-section">
            <div class="table-header">
                <i class="fa fa-list"></i> Süt Teslim Talepleri
            </div>
            
            <?php
            
            $sql = "SELECT * FROM food_donations WHERE assigned_to IS NULL";
            $result = mysqli_query($connection, $sql);
            $id = $_SESSION['Aid'];

            if (!$result) {
                die("Sorgu hatası: " . mysqli_error($connection));
            }

            $data = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }

            // Onay işlemi
            if (isset($_POST['approve']) && isset($_POST['delivery_person_id'])) {
                $order_id = $_POST['order_id'];
                $delivery_person_id = $_POST['delivery_person_id'];
                
                $sql = "SELECT * FROM food_donations WHERE Fid = $order_id AND assigned_to IS NOT NULL";
                $result = mysqli_query($connection, $sql);

                if (mysqli_num_rows($result) > 0) {
                    echo "<div style='color: red; margin-bottom: 15px;'>Bu talep zaten başka birine atanmış!</div>";
                } else {
                    $sql = "UPDATE food_donations SET assigned_to = $delivery_person_id WHERE Fid = $order_id";
                    $result = mysqli_query($connection, $sql);

                    if (!$result) {
                        die("Atama hatası: " . mysqli_error($connection));
                    }
                    
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    ob_end_flush();
                }
            }

            // Süt tipi çeviri fonksiyonu
            function getMilkTypeInTurkish($milkType) {
                switch(strtolower($milkType)) {
                    case 'cow':
                        return 'İnek Sütü';
                    case 'goat':
                        return 'Keçi Sütü';
                    case 'sheep':
                        return 'Koyun Sütü';
                    default:
                        return 'Belirtilmemiş';
                }
            }
            ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>İsim</th>
                            <th>Süt Tipi</th>
                            <th>Telefon</th>
                            <th>Tarih/Saat</th>
                            <th>Adres</th>
                            <th>Miktar (Litre)</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row) { ?>
                            <tr>
                                <td><?= $row['name'] ?></td>
                                <td><?= getMilkTypeInTurkish($row['milk_type']) ?></td>
                                <td><?= $row['phoneno'] ?></td>
                                <td><?= $row['date'] ?></td>
                                <td><?= $row['address'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td>
                                    <?php if ($row['assigned_to'] == null) { ?>
                                        <form method="post" action="" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?= $row['Fid'] ?>">
                                            <input type="hidden" name="delivery_person_id" value="<?= $id ?>">
                                            <button type="submit" name="approve" class="btn-approve">Onayla</button>
                                        </form>
                                    <?php } else if ($row['assigned_to'] == $id) { ?>
                                        <span style="color: green; font-weight: bold;">Size Atandı</span>
                                    <?php } else { ?>
                                        <span style="color: orange;">Başka Kuryeye Atandı</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
