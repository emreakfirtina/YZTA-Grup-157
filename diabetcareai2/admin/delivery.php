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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Kurye Performans Analizi - Balkan Süt</title>
    
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

        /* Chart Container */
        .chart-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #6BBE45;
            padding-bottom: 10px;
        }

        .chart-title {
            font-size: 24px;
            font-weight: bold;
            color: #253078;
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        /* Stats Boxes */
        .stats-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            text-align: center;
            border-left: 5px solid #6BBE45;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        /* Kurye Kartları */
        .courier-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .courier-card {
            background: linear-gradient(135deg, #253078 0%, #1a2357 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(37, 48, 120, 0.3);
            transition: transform 0.3s ease;
        }

        .courier-card:hover {
            transform: translateY(-5px);
        }

        .courier-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #6BBE45;
        }

        .courier-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .courier-stat {
            text-align: center;
        }

        .courier-stat-number {
            font-size: 24px;
            font-weight: bold;
            display: block;
        }

        .courier-stat-label {
            font-size: 12px;
            opacity: 0.8;
        }

        /* Performance Badge */
        .performance-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-excellent { background: #28a745; color: white; }
        .badge-good { background: #6BBE45; color: white; }
        .badge-average { background: #ffc107; color: #333; }
        .badge-poor { background: #dc3545; color: white; }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .chart-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-boxes, .courier-cards {
                grid-template-columns: 1fr;
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
                <li><a href="admin.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="analytics.php"><i class="fa fa-bar-chart"></i> Analizler</a></li>
                <li><a href="donate.php"><i class="fa fa-truck"></i> Süt Teslimatları</a></li>
                <li><a href="delivery.php" class="active"><i class="fa fa-users"></i> Kurye Performansı</a></li>
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
            <div class="dashboard-title">Kurye Performans Analizi</div>
            <div class="dashboard-subtitle">Kurye verimliliği ve teslimat analizleri</div>
        </div>

        <?php
        // Genel istatistikler
        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('-7 days'));
        $month_start = date('Y-m-d', strtotime('-30 days'));

        // Toplam kurye sayısı
        $query_total_couriers = "SELECT COUNT(*) as total FROM delivery_persons";
        $result_total_couriers = mysqli_query($connection, $query_total_couriers);
        $total_couriers = mysqli_fetch_assoc($result_total_couriers)['total'];

        // Aktif kurye sayısı (son 7 günde teslimat yapan)
        $query_active_couriers = "SELECT COUNT(DISTINCT delivery_by) as active FROM food_donations 
                                 WHERE delivery_by IS NOT NULL AND DATE(delivery_start_time) >= '$week_start'";
        $result_active_couriers = mysqli_query($connection, $query_active_couriers);
        $active_couriers = mysqli_fetch_assoc($result_active_couriers)['active'];

        // Bugün toplam teslimat
        $query_today_deliveries = "SELECT COUNT(*) as total FROM food_donations 
                                  WHERE DATE(delivery_start_time) = '$today' AND delivery_by IS NOT NULL";
        $result_today_deliveries = mysqli_query($connection, $query_today_deliveries);
        $today_deliveries = mysqli_fetch_assoc($result_today_deliveries)['total'];

        // Ortalama teslimat süresi
        $query_avg_duration = "SELECT AVG(delivery_duration_minutes) as avg_duration FROM food_donations 
                              WHERE delivery_duration_minutes IS NOT NULL AND delivery_duration_minutes > 0";
        $result_avg_duration = mysqli_query($connection, $query_avg_duration);
        $avg_duration = round(mysqli_fetch_assoc($result_avg_duration)['avg_duration'] ?? 0);

        // En hızlı teslimat
        $query_fastest = "SELECT MIN(delivery_duration_minutes) as fastest FROM food_donations 
                         WHERE delivery_duration_minutes IS NOT NULL AND delivery_duration_minutes > 0";
        $result_fastest = mysqli_query($connection, $query_fastest);
        $fastest_delivery = mysqli_fetch_assoc($result_fastest)['fastest'] ?? 0;
        ?>

        <!-- İstatistik Kutuları -->
        <div class="stats-boxes">
            <div class="stat-box">
                <div class="stat-number"><?= $total_couriers ?></div>
                <div class="stat-label">Toplam Kurye</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $active_couriers ?></div>
                <div class="stat-label">Aktif Kurye (7 Gün)</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $today_deliveries ?></div>
                <div class="stat-label">Bugün Teslimat</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $avg_duration ?> dk</div>
                <div class="stat-label">Ortalama Teslimat Süresi</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $fastest_delivery ?> dk</div>
                <div class="stat-label">En Hızlı Teslimat</div>
            </div>
        </div>

        <?php
        // Kurye performans verileri
        $query_courier_performance = "
            SELECT 
                dp.Did,
                dp.name,
                dp.email,
                COUNT(fd.Fid) as total_deliveries,
                SUM(fd.quantity) as total_milk_delivered,
                AVG(fd.delivery_duration_minutes) as avg_delivery_time,
                MIN(fd.delivery_duration_minutes) as fastest_delivery,
                MAX(fd.delivery_duration_minutes) as slowest_delivery,
                COUNT(CASE WHEN DATE(fd.delivery_start_time) >= '$week_start' THEN 1 END) as weekly_deliveries,
                COUNT(CASE WHEN DATE(fd.delivery_start_time) = '$today' THEN 1 END) as today_deliveries
            FROM delivery_persons dp
            LEFT JOIN food_donations fd ON dp.Did = fd.delivery_by 
            WHERE fd.delivery_by IS NOT NULL
            GROUP BY dp.Did, dp.name, dp.email
            ORDER BY total_deliveries DESC
        ";
        $result_courier_performance = mysqli_query($connection, $query_courier_performance);
        ?>

        <!-- Kurye Performans Kartları -->
        <div class="chart-section">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fa fa-users"></i> Kurye Performans Özeti
                </div>
            </div>
            <div class="courier-cards">
                <?php while($courier = mysqli_fetch_assoc($result_courier_performance)): 
                    // Performans skoru hesapla
                    $performance_score = 0;
                    if($courier['avg_delivery_time'] <= 30) $performance_score += 3;
                    elseif($courier['avg_delivery_time'] <= 45) $performance_score += 2;
                    else $performance_score += 1;
                    
                    if($courier['total_deliveries'] >= 50) $performance_score += 3;
                    elseif($courier['total_deliveries'] >= 20) $performance_score += 2;
                    else $performance_score += 1;
                    
                    // Badge belirleme
                    $badge_class = 'badge-poor';
                    $badge_text = 'Gelişmeli';
                    if($performance_score >= 5) { $badge_class = 'badge-excellent'; $badge_text = 'Mükemmel'; }
                    elseif($performance_score >= 4) { $badge_class = 'badge-good'; $badge_text = 'İyi'; }
                    elseif($performance_score >= 3) { $badge_class = 'badge-average'; $badge_text = 'Orta'; }
                ?>
                <div class="courier-card">
                    <div class="courier-name">
                        <?= htmlspecialchars($courier['name']) ?>
                        <span class="performance-badge <?= $badge_class ?>"><?= $badge_text ?></span>
                    </div>
                    <div class="courier-stats">
                        <div class="courier-stat">
                            <span class="courier-stat-number"><?= $courier['total_deliveries'] ?></span>
                            <span class="courier-stat-label">Toplam Teslimat</span>
                        </div>
                        <div class="courier-stat">
                            <span class="courier-stat-number"><?= round($courier['avg_delivery_time']) ?>dk</span>
                            <span class="courier-stat-label">Ort. Süre</span>
                        </div>
                        <div class="courier-stat">
                            <span class="courier-stat-number"><?= $courier['total_milk_delivered'] ?>L</span>
                            <span class="courier-stat-label">Toplam Süt</span>
                        </div>
                        <div class="courier-stat">
                            <span class="courier-stat-number"><?= $courier['weekly_deliveries'] ?></span>
                            <span class="courier-stat-label">Bu Hafta</span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Grafikler -->
        <div class="chart-grid">
            <!-- Kurye Teslimat Karşılaştırması -->
            <div class="chart-section">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fa fa-bar-chart"></i> Kurye Teslimat Karşılaştırması
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="courierComparisonChart"></canvas>
                </div>
            </div>

            <!-- Teslimat Süre Analizi -->
            <div class="chart-section">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fa fa-clock-o"></i> Ortalama Teslimat Süreleri
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="deliveryTimeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Haftalık Trend Analizi -->
        <div class="chart-section">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fa fa-line-chart"></i> Haftalık Teslimat Trend Analizi
                </div>
            </div>
            <div class="chart-container">
                <canvas id="weeklyTrendChart"></canvas>
            </div>
        </div>

        <!-- Verimlilik Skoru -->
        <div class="chart-grid">
            <div class="chart-section">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fa fa-trophy"></i> Kurye Verimlilik Skoru
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="efficiencyChart"></canvas>
                </div>
            </div>

            <!-- Günlük Aktivite Haritası -->
            <div class="chart-section">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fa fa-calendar"></i> Günlük Aktivite Dağılımı
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="dailyActivityChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <script>
        <?php
        // Kurye karşılaştırma verileri
        mysqli_data_seek($result_courier_performance, 0);
        $courier_names = [];
        $courier_deliveries = [];
        $courier_avg_times = [];
        $courier_efficiency = [];

        while($row = mysqli_fetch_assoc($result_courier_performance)) {
            $courier_names[] = $row['name'];
            $courier_deliveries[] = $row['total_deliveries'];
            $courier_avg_times[] = round($row['avg_delivery_time']);
            
            // Verimlilik skoru: (teslimat sayısı * 100) / ortalama süre
            $efficiency = $row['avg_delivery_time'] > 0 ? 
                round(($row['total_deliveries'] * 100) / $row['avg_delivery_time'], 1) : 0;
            $courier_efficiency[] = $efficiency;
        }

        // Haftalık trend verileri
        $weekly_trend_labels = [];
        $weekly_trend_data = [];
        for($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $query = "SELECT COUNT(*) as total FROM food_donations 
                     WHERE DATE(delivery_start_time) = '$date' AND delivery_by IS NOT NULL";
            $result = mysqli_query($connection, $query);
            $total = mysqli_fetch_assoc($result)['total'];
            
            $weekly_trend_labels[] = date('d/m', strtotime($date));
            $weekly_trend_data[] = $total;
        }

        // Günlük aktivite verileri (saat bazında)
        $hourly_activity = array_fill(0, 24, 0);
        $query_hourly = "SELECT HOUR(delivery_start_time) as hour, COUNT(*) as count 
                        FROM food_donations 
                        WHERE delivery_start_time IS NOT NULL 
                        GROUP BY HOUR(delivery_start_time)";
        $result_hourly = mysqli_query($connection, $query_hourly);
        while($row = mysqli_fetch_assoc($result_hourly)) {
            $hourly_activity[$row['hour']] = $row['count'];
        }
        ?>

        // Kurye karşılaştırma grafiği
        const courierCtx = document.getElementById('courierComparisonChart').getContext('2d');
        new Chart(courierCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($courier_names) ?>,
                datasets: [{
                    label: 'Toplam Teslimat',
                    data: <?= json_encode($courier_deliveries) ?>,
                    backgroundColor: '#6BBE45',
                    borderColor: '#5aa83a',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Teslimat süre grafiği - Sütun grafik olarak değiştirildi
        const timeCtx = document.getElementById('deliveryTimeChart').getContext('2d');
        new Chart(timeCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($courier_names) ?>,
                datasets: [{
                    label: 'Ortalama Teslimat Süresi (dakika)',
                    data: <?= json_encode($courier_avg_times) ?>,
                    backgroundColor: [
                        '#6BBE45', '#253078', '#ff6b6b', '#4ecdc4', 
                        '#45b7d1', '#f9ca24', '#f0932b', '#eb4d4b'
                    ],
                    borderColor: [
                        '#5aa83a', '#1a2357', '#ff5252', '#26d0ce', 
                        '#2196f3', '#f39c12', '#e67e22', '#c0392b'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' dakika';
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Süre (Dakika)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Kuryeler'
                        }
                    }
                }
            }
        });

        // Haftalık trend grafiği
        const trendCtx = document.getElementById('weeklyTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($weekly_trend_labels) ?>,
                datasets: [{
                    label: 'Günlük Teslimat',
                    data: <?= json_encode($weekly_trend_data) ?>,
                    borderColor: '#253078',
                    backgroundColor: 'rgba(37, 48, 120, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Verimlilik skoru grafiği
        const efficiencyCtx = document.getElementById('efficiencyChart').getContext('2d');
        new Chart(efficiencyCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($courier_names) ?>,
                datasets: [{
                    data: <?= json_encode($courier_efficiency) ?>,
                    backgroundColor: [
                        '#6BBE45', '#ff6b6b', '#4ecdc4', '#45b7d1', 
                        '#f9ca24', '#f0932b', '#eb4d4b', '#6c5ce7'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Günlük aktivite grafiği
        const activityCtx = document.getElementById('dailyActivityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: 'Teslimat Sayısı',
                    data: <?= json_encode($hourly_activity) ?>,
                    backgroundColor: '#6BBE45',
                    borderColor: '#5aa83a',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true },
                    x: { 
                        title: {
                            display: true,
                            text: 'Saat'
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>