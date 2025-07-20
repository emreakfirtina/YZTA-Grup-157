<?php
ob_start(); 
include '../connection.php';
include("connect.php"); 
if($_SESSION['name']==''){
	header("location:deliverylogin.php");
}
$name=$_SESSION['name'];
$id=$_SESSION['Did'];

// Sabit ücretler
$SABIT_UCRET = 25;
$LITRE_BASINA_UCRET = 2;

// Süt tipi çeviri fonksiyonu
function getSutTipi($milk_type) {
    switch(strtolower($milk_type)) {
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

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <title>Siparişlerim - Balkan Süt</title>
    
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

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 16px;
        }

        .stat-card.earnings {
            background: linear-gradient(135deg, #6BBE45, #5aa83a);
            color: white;
        }

        .stat-card.earnings .stat-number,
        .stat-card.earnings .stat-label {
            color: white;
        }

        /* Grafik Bölümü */
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }

        .chart-title {
            font-size: 18px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 20px;
            text-align: center;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

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

        .earnings {
            color: #6BBE45;
            font-weight: bold;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .charts-container {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">KURYE PANELİ</div>
        </div>
        <nav class="sidebar-menu">
            <ul>
                <li><a href="delivery.php"><i class="fa fa-home"></i> Ana Sayfa</a></li>
                <li><a href="openmap.php"><i class="fa fa-map"></i> Harita</a></li>
                <li><a href="deliverymyord.php" class="active"><i class="fa fa-truck"></i> Siparişlerim</a></li>
                <li><a href="../logout.php"><i class="fa fa-sign-out"></i> Çıkış</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        
        <div class="dashboard-header">
            <div class="dashboard-title">Siparişlerim</div>
            <div class="dashboard-subtitle">Merhaba <?php echo $name; ?> - Tamamladığınız ve devam eden siparişleriniz</div>
        </div>

        <?php
        // Kurye siparişlerini getir
        $sql = "SELECT fd.Fid AS Fid, fd.name, fd.phoneno, fd.date, fd.delivery_by, 
                       fd.address as From_address, fd.quantity, fd.milk_type,
                       fd.delivery_start_time, fd.delivery_end_time, fd.delivery_duration_minutes,
                       ad.name AS delivery_person_name, ad.address AS To_address
                FROM food_donations fd
                LEFT JOIN admin ad ON fd.assigned_to = ad.Aid 
                WHERE delivery_by='$id'
                ORDER BY fd.date DESC";

        $result = mysqli_query($connection, $sql);

        if (!$result) {
            die("Veritabanı hatası: " . mysqli_error($connection));
        }

        $data = array();
        $total_earnings = 0;
        $total_orders = 0;
        $total_liters = 0;
        $total_duration = 0;
        $monthly_data = array();
        $duration_data = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $quantity = floatval($row['quantity'] ?? 0);
            $order_earning = $SABIT_UCRET + ($quantity * $LITRE_BASINA_UCRET);
            $row['earning'] = $order_earning;
            
            $total_earnings += $order_earning;
            $total_orders++;
            $total_liters += $quantity;
            
            // Süre hesaplama
            if($row['delivery_duration_minutes']) {
                $total_duration += intval($row['delivery_duration_minutes']);
                $duration_data[] = intval($row['delivery_duration_minutes']);
            }
            
            // Aylık veri gruplaması
            $month = date('Y-m', strtotime($row['date']));
            if(!isset($monthly_data[$month])) {
                $monthly_data[$month] = ['orders' => 0, 'earnings' => 0];
            }
            $monthly_data[$month]['orders']++;
            $monthly_data[$month]['earnings'] += $order_earning;
            
            $data[] = $row;
        }

        $avg_duration = $total_orders > 0 ? round($total_duration / $total_orders) : 0;

        // Son 6 ay için veri hazırlama
        $chart_months = array();
        $chart_orders = array();
        $chart_earnings = array();
        
        for($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $month_name = date('M Y', strtotime("-$i months"));
            $chart_months[] = $month_name;
            $chart_orders[] = isset($monthly_data[$month]) ? $monthly_data[$month]['orders'] : 0;
            $chart_earnings[] = isset($monthly_data[$month]) ? $monthly_data[$month]['earnings'] : 0;
        }
        ?>

        <!-- İstatistik Kartları -->
        <div class="stats-container">
            <div class="stat-card earnings">
                <div class="stat-number"><?php echo number_format($total_earnings, 2); ?> ₺</div>
                <div class="stat-label">Toplam Kazanç</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Tamamlanan Sipariş</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($total_liters, 1); ?> L</div>
                <div class="stat-label">Toplanan Süt</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $avg_duration; ?> dk</div>
                <div class="stat-label">Ortalama Süre</div>
            </div>
        </div>

        <!-- Grafik Bölümü -->
        <div class="charts-container">
            <div class="chart-card">
                <div class="chart-title">Aylık Sipariş Performansı</div>
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Teslimat Süre Dağılımı</div>
                <div class="chart-container">
                    <canvas id="durationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Siparişler Tablosu -->
        <div class="table-section">
            <div class="table-header">
                <i class="fa fa-list"></i> Sipariş Geçmişi
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>İsim</th>
                            <th>Telefon</th>
                            <th>Tarih/Saat</th>
                            <th>Miktar</th>
                            <th>Süt Tipi</th>
                            <th>Süre</th>
                            <th>Kazanç</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['phoneno']) ?></td>
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><span class="quantity"><?= htmlspecialchars($row['quantity'] ?? '0') ?> L</span></td>
                                <td><span class="milk-type"><?= getSutTipi($row['milk_type'] ?? '') ?></span></td>
                                <td><?= $row['delivery_duration_minutes'] ? $row['delivery_duration_minutes'] . ' dk' : '-' ?></td>
                                <td><span class="earnings"><?= number_format($row['earning'], 2) ?> ₺</span></td>
                            </tr>
                        <?php } ?>
                        
                        <?php if (empty($data)) { ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #666; padding: 20px;">
                                    Henüz tamamlanmış siparişiniz bulunmamaktadır.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Aylık Performans Grafiği
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_months); ?>,
                datasets: [{
                    label: 'Sipariş Sayısı',
                    data: <?php echo json_encode($chart_orders); ?>,
                    borderColor: '#6BBE45',
                    backgroundColor: 'rgba(107, 190, 69, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Kazanç (₺)',
                    data: <?php echo json_encode($chart_earnings); ?>,
                    borderColor: '#253078',
                    backgroundColor: 'rgba(37, 48, 120, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Süre Dağılım Grafiği
        const durationCtx = document.getElementById('durationChart').getContext('2d');
        const durationData = <?php echo json_encode($duration_data); ?>;
        
        // Süreleri gruplara ayır
        const ranges = ['0-15 dk', '16-30 dk', '31-45 dk', '46-60 dk', '60+ dk'];
        const rangeCounts = [0, 0, 0, 0, 0];
        
        durationData.forEach(duration => {
            if (duration <= 15) rangeCounts[0]++;
            else if (duration <= 30) rangeCounts[1]++;
            else if (duration <= 45) rangeCounts[2]++;
            else if (duration <= 60) rangeCounts[3]++;
            else rangeCounts[4]++;
        });

        new Chart(durationCtx, {
            type: 'doughnut',
            data: {
                labels: ranges,
                datasets: [{
                    data: rangeCounts,
                    backgroundColor: [
                        '#6BBE45',
                        '#253078',
                        '#FFA500',
                        '#FF6B6B',
                        '#4ECDC4'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

</body>
</html>