
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
    <!-- Leaflet CSS ve JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <!-- Leaflet Heatmap Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.heat/0.2.0/leaflet-heat.min.js"></script>
    <title>Analytics - Balkan Süt</title>
    
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

        .time-selector select {
            padding: 8px 15px;
            border: 2px solid #6BBE45;
            border-radius: 5px;
            background-color: white;
            color: #253078;
            font-weight: bold;
            cursor: pointer;
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

        /* Harita için özel stil */
        #heatMap {
            height: 400px;
            width: 100%;
            border-radius: 10px;
            border: 2px solid #6BBE45;
        }

        .map-legend {
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            margin-top: 10px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin: 5px 0;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border-radius: 3px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .chart-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-boxes {
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
                <li><a href="#" class="active"><i class="fa fa-bar-chart"></i> Analizler</a></li>
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
            <div class="dashboard-title">Süt Toplama Analizleri</div>
            <div class="dashboard-subtitle">Detaylı analiz ve raporlar</div>
        </div>

        <?php
        // İstatistik verilerini hesapla
        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('-7 days'));
        $month_start = date('Y-m-d', strtotime('-30 days'));

        // Günlük toplam
        $query_daily = "SELECT COALESCE(SUM(quantity), 0) as total FROM food_donations WHERE DATE(date) = '$today'";
        $result_daily = mysqli_query($connection, $query_daily);
        $daily_total = mysqli_fetch_assoc($result_daily)['total'];

        // Haftalık toplam
        $query_weekly = "SELECT COALESCE(SUM(quantity), 0) as total FROM food_donations WHERE DATE(date) >= '$week_start'";
        $result_weekly = mysqli_query($connection, $query_weekly);
        $weekly_total = mysqli_fetch_assoc($result_weekly)['total'];

        // Aylık toplam
        $query_monthly = "SELECT COALESCE(SUM(quantity), 0) as total FROM food_donations WHERE DATE(date) >= '$month_start'";
        $result_monthly = mysqli_query($connection, $query_monthly);
        $monthly_total = mysqli_fetch_assoc($result_monthly)['total'];

        // Aktif üretici sayısı
        $query_producers = "SELECT COUNT(DISTINCT name) as count FROM food_donations WHERE DATE(date) >= '$month_start'";
        $result_producers = mysqli_query($connection, $query_producers);
        $active_producers = mysqli_fetch_assoc($result_producers)['count'];
        ?>

        <!-- İstatistik Kutuları -->
        <div class="stats-boxes">
            <div class="stat-box">
                <div class="stat-number"><?= $daily_total ?></div>
                <div class="stat-label">Bugün Toplanan Süt (L)</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $weekly_total ?></div>
                <div class="stat-label">Haftalık Toplam (L)</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $monthly_total ?></div>
                <div class="stat-label">Aylık Toplam (L)</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $active_producers ?></div>
                <div class="stat-label">Aktif Üretici</div>
            </div>
        </div>

        <!-- Süt Toplama Grafiği -->
        <div class="chart-section">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fa fa-line-chart"></i> Süt Toplama Miktarları
                </div>
                <div class="time-selector">
                    <select id="timeRange" onchange="updateChart()">
                        <option value="daily">Son 7 Gün</option>
                        <option value="weekly">Son 4 Hafta</option>
                        <option value="monthly">Son 6 Ay</option>
                    </select>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="milkCollectionChart"></canvas>
            </div>
        </div>

        <!-- Alt Grafikler -->
        <div class="chart-grid">
            <!-- Üretici Performans Analizi -->
            <div class="chart-section">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fa fa-users"></i> Top 5 Üretici Performansı
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="producerPerformanceChart"></canvas>
                </div>
            </div>

            <!-- ISI HARİTASI - Konum Bazlı Analiz -->
            <div class="chart-section">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="fa fa-map-marker"></i> Süt Toplama Isı Haritası
                    </div>
                </div>
                <div class="chart-container">
                    <div id="heatMap"></div>
                    <div class="map-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ff0000;"></div>
                            <span>Yüksek Üretim (>50L)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ffaa00;"></div>
                            <span>Orta Üretim (25-50L)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ffff00;"></div>
                            <span>Düşük Üretim (<25L)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Süt Tipi Analizi -->
        <div class="chart-section">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fa fa-pie-chart"></i> Süt Tipi Dağılımı
                </div>
            </div>
            <div class="chart-container">
                <canvas id="milkTypeChart"></canvas>
            </div>
        </div>

    </div>

    <script>
        <?php
        // PHP verilerini JavaScript'e aktarma

        // Son 7 günlük veriler
        $daily_data = [];
        $daily_labels = [];
        for($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $query = "SELECT COALESCE(SUM(quantity), 0) as total FROM food_donations WHERE DATE(date) = '$date'";
            $result = mysqli_query($connection, $query);
            $total = mysqli_fetch_assoc($result)['total'];
            $daily_data[] = $total;
            $daily_labels[] = date('d/m', strtotime($date));
        }

        // Son 4 haftalık veriler
        $weekly_data = [];
        $weekly_labels = [];
        for($i = 3; $i >= 0; $i--) {
            $start_date = date('Y-m-d', strtotime("-" . (($i+1)*7) . " days"));
            $end_date = date('Y-m-d', strtotime("-" . ($i*7) . " days"));
            $query = "SELECT COALESCE(SUM(quantity), 0) as total FROM food_donations WHERE DATE(date) BETWEEN '$start_date' AND '$end_date'";
            $result = mysqli_query($connection, $query);
            $total = mysqli_fetch_assoc($result)['total'];
            $weekly_data[] = $total;
            $weekly_labels[] = date('d/m', strtotime($start_date)) . ' - ' . date('d/m', strtotime($end_date));
        }

        // Son 6 aylık veriler
        $monthly_data = [];
        $monthly_labels = [];
        for($i = 5; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-$i months"));
            $query = "SELECT COALESCE(SUM(quantity), 0) as total FROM food_donations WHERE DATE_FORMAT(date, '%Y-%m') = '" . date('Y-m', strtotime($date)) . "'";
            $result = mysqli_query($connection, $query);
            $total = mysqli_fetch_assoc($result)['total'];
            $monthly_data[] = $total;
            $monthly_labels[] = date('M Y', strtotime($date));
        }

        // Top 5 üretici performansı
        $query_producers = "SELECT name, COUNT(*) as frequency, SUM(quantity) as total_quantity 
                           FROM food_donations 
                           WHERE DATE(date) >= '$month_start'
                           GROUP BY name 
                           ORDER BY total_quantity DESC 
                           LIMIT 5";
        $result_producers = mysqli_query($connection, $query_producers);
        $producer_names = [];
        $producer_quantities = [];
        while($row = mysqli_fetch_assoc($result_producers)) {
            $producer_names[] = $row['name'];
            $producer_quantities[] = $row['total_quantity'];
        }

        // Harita için konum bazlı veriler - Geliştirilmiş
        $query_locations = "SELECT address, SUM(quantity) as total_quantity, COUNT(*) as delivery_count 
                           FROM food_donations 
                           WHERE address IS NOT NULL AND address != '' 
                           GROUP BY address 
                           ORDER BY total_quantity DESC";
        $result_locations = mysqli_query($connection, $query_locations);
        $location_data = [];
        while($row = mysqli_fetch_assoc($result_locations)) {
            $location_data[] = [
                'address' => $row['address'],
                'total_quantity' => $row['total_quantity'],
                'delivery_count' => $row['delivery_count']
            ];
        }

        // Süt tipi analizi - login tablosundan
        $milk_types = ['cow' => 0, 'goat' => 0, 'sheep' => 0];
        $query_milk_types = "SELECT milk_types FROM login WHERE milk_types IS NOT NULL AND milk_types != ''";
        $result_milk_types = mysqli_query($connection, $query_milk_types);
        while($row = mysqli_fetch_assoc($result_milk_types)) {
            $types = explode(',', $row['milk_types']);
            foreach($types as $type) {
                $type = trim(strtolower($type));
                if($type == 'cow') $milk_types['cow']++;
                elseif($type == 'goat') $milk_types['goat']++;  
                elseif($type == 'sheep') $milk_types['sheep']++;
            }
        }
        ?>

        // Chart.js verileri
        const chartData = {
            daily: {
                labels: <?= json_encode($daily_labels) ?>,
                data: <?= json_encode($daily_data) ?>
            },
            weekly: {
                labels: <?= json_encode($weekly_labels) ?>,
                data: <?= json_encode($weekly_data) ?>
            },
            monthly: {
                labels: <?= json_encode($monthly_labels) ?>,
                data: <?= json_encode($monthly_data) ?>
            }
        };

        // Harita verileri
        const locationData = <?= json_encode($location_data) ?>;

        // Ana süt toplama grafiği
        let milkChart;
        
        function initChart() {
            const ctx = document.getElementById('milkCollectionChart').getContext('2d');
            milkChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.daily.labels,
                    datasets: [{
                        label: 'Süt Miktarı (Litre)',
                        data: chartData.daily.data,
                        borderColor: '#6BBE45',
                        backgroundColor: 'rgba(107, 190, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + ' L';
                                }
                            }
                        }
                    }
                }
            });
        }

        function updateChart() {
            const timeRange = document.getElementById('timeRange').value;
            milkChart.data.labels = chartData[timeRange].labels;
            milkChart.data.datasets[0].data = chartData[timeRange].data;
            milkChart.update();
        }

        // Üretici performans grafiği
        const producerCtx = document.getElementById('producerPerformanceChart').getContext('2d');
        new Chart(producerCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($producer_names) ?>,
                datasets: [{
                    label: 'Toplam Süt (L)',
                    data: <?= json_encode($producer_quantities) ?>,
                    backgroundColor: '#6BBE45',
                    borderColor: '#5aa83a',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // GELİŞTİRİLMİŞ ISI HARİTASI FONKSİYONU
        function initHeatMap() {
            const aydinCenter = [37.8400, 27.8200]; // Biraz daha güneybatıya kaydırıldı
            
            // Harita oluştur
            const map = L.map('heatMap').setView(aydinCenter, 11);
            
            // Harita katmanı
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Aydın köyleri koordinatları - Genişletilmiş liste
            const locationCoords = {
                'armutlu': [37.7981,27.974], // Armutlu Mahallesi - Efeler/Aydın
                'kozalaklı': [37.804,27.99], // Kozalaklı Köyü - Efeler
                'mesutlu': [37.801, 27.954], // Mesutlu Köyü - Efeler
                'karahayıt': [37.795, 28.007], // Karahayıt Köyü - Efeler
                'dereköy': [37.8250, 27.7980], // Dereköy - Efeler 
                'dalama': [37.8750, 27.8120], // Dalama Köyü - Efeler
                'kırıklar': [37.8380, 27.7850], // Kırıklar Köyü - Efeler
                'gödrenli': [37.8580, 27.7650], // Gödrenli Köyü - Efeler
                'hamzabali': [37.8820, 27.8980], // Hamzabali Köyü - Efeler
                'çulhan': [37.8450, 27.7750], // Çulhan Köyü - Efeler
                'alhan': [37.8350, 27.7950], // Alhan Köyü - Efeler
                'şahnalı': [37.788, 27.943], // Şahnalı Köyü - Efeler
                'gölhisar': [37.8780, 27.8450], // Gölhisar Köyü - Efeler
                'baltaköy': [37.8280, 27.8250], // Baltaköy - Efeler
                'çiftlikköy': [37.8480, 27.8650], // Çiftlikköy - Efeler
                'tepeköy': [37.8180, 27.8150], // Tepeköy - Efeler
                'efeler': [37.8444, 27.8458], // Efeler İlçe Merkezi
                'merkez': [37.8444, 27.8458], // Aydın Merkez
                'center': [37.8444, 27.8458] // Center
                
            };

            const heatData = [];
            const markers = [];

            console.log('Toplam konum verisi:', locationData.length);

            // Her bir konum için işlem yap
            locationData.forEach(location => {
                const address = location.address.toLowerCase();
                let coords = null;
                let locationName = '';

                // Koordinat eşleştirme - daha hassas
                for (let place in locationCoords) {
                    if (address.includes(place)) {
                        coords = locationCoords[place];
                        locationName = place;
                        break;
                    }
                }

                if (coords) {
                    // Isı haritası değeri hesapla (0.1 - 1.0 arası)
                    const intensity = Math.min(1.0, Math.max(0.1, location.total_quantity / 100));
                    
                    // Isı haritası verisi ekle
                    heatData.push([coords[0], coords[1], intensity]);
                    
                    // Marker rengi belirleme
                    let markerColor = '#ffff00'; // Sarı - düşük
                    if (location.total_quantity > 50) markerColor = '#ff0000'; // Kırmızı - yüksek
                    else if (location.total_quantity > 25) markerColor = '#ffaa00'; // Turuncu - orta

                    // Marker ekle
                    const marker = L.circleMarker(coords, {
                        radius: Math.max(5, Math.min(15, location.total_quantity / 10)),
                        fillColor: markerColor,
                        color: '#ffffff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).addTo(map);
                    
                    // Popup ekle
                    marker.bindPopup(`
                        <div style="text-align: center; font-family: Arial;">
                            <h4 style="color: #253078; margin: 5px 0;">${location.address}</h4>
                            <p style="color: #6BBE45; font-weight: bold; margin: 5px 0;">
                                Toplam: ${location.total_quantity} L
                            </p>
                            <p style="color: #666; margin: 5px 0;">
                                Teslimat Sayısı: ${location.delivery_count}
                            </p>
                        </div>
                    `);

                    console.log(`✓ Eşleşti: ${locationName} → ${location.address} (${location.total_quantity}L)`);
                } else {
                    console.log(`✗ Koordinat bulunamadı: ${location.address}`);
                }
            });

            // Isı haritası katmanı ekle
            if (heatData.length > 0 && typeof L.heatLayer === 'function') {
                const heatLayer = L.heatLayer(heatData, {
                    radius: 25,
                    blur: 15,
                    maxZoom: 17,
                    max: 1.0,
                    gradient: {
                        0.1: '#0000ff',
                        0.3: '#00ffff', 
                        0.5: '#00ff00',
                        0.7: '#ffff00',
                        0.9: '#ff8000',
                        1.0: '#ff0000'
                    }
                }).addTo(map);
                
                console.log(`✓ Isı haritası oluşturuldu: ${heatData.length} veri noktası`);
            } else {
                console.log('⚠ Isı haritası oluşturulamadı - Sadece marker\'lar gösterilecek');
                
                if (heatData.length === 0) {
                    L.popup()
                        .setLatLng(aydinCenter)
                        .setContent('<b>Bilgi:</b><br>Henüz harita verisi mevcut değil.')
                        .openOn(map);
                }
            }

            // Harita boyutunu ayarla
            setTimeout(() => {
                map.invalidateSize();
            }, 100);
        }

        // Süt tipi grafiği
        const milkTypeCtx = document.getElementById('milkTypeChart').getContext('2d');
        new Chart(milkTypeCtx, {
            type: 'pie',
            data: {
                labels: ['İnek Sütü', 'Keçi Sütü', 'Koyun Sütü'],
                datasets: [{
                    data: [<?= $milk_types['cow'] ?>, <?= $milk_types['goat'] ?>, <?= $milk_types['sheep'] ?>],
                    backgroundColor: [
                        '#6BBE45',
                        '#ff6b6b',
                        '#4ecdc4'
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

        // Sayfa yüklendiğinde başlat
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
            setTimeout(initHeatMap, 500);
        });
    </script>

</body>
</html>