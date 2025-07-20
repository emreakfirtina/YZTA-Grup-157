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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Süt Teslimatları - Balkan Süt</title>
    
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

        .filter-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }

        .filter-form {
            display: flex;
            gap: 20px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .filter-group label {
            font-weight: bold;
            color: #253078;
            margin-bottom: 8px;
        }

        .filter-group select {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .filter-group select:focus {
            border-color: #6BBE45;
            outline: none;
        }

        .filter-btn {
            background-color: #6BBE45;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .filter-btn:hover {
            background-color: #5aa83a;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }

        .chart-title {
            font-size: 20px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 20px;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #6BBE45, #5aa83a);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(107, 190, 69, 0.3);
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 16px;
            opacity: 0.9;
        }

        .table-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }

        .table-header {
            font-size: 24px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 20px;
            border-bottom: 2px solid #6BBE45;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .search-box input:focus {
            border-color: #6BBE45;
            outline: none;
        }

        .search-box .fa-search {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
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

        .milk-type-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin: 2px;
        }

        .cow-milk { background-color: #e3f2fd; color: #1976d2; }
        .goat-milk { background-color: #f3e5f5; color: #7b1fa2; }
        .sheep-milk { background-color: #fff3e0; color: #f57c00; }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .table-header {
                flex-direction: column;
                gap: 15px;
            }

            .search-box {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">ADMİN PANELİ</div>
        </div>
        <nav class="sidebar-menu">
            <ul>
                <li><a href="admin.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="analytics.php"><i class="fa fa-bar-chart"></i> Analizler</a></li>
                <li><a href="donate.php" class="active"><i class="fa fa-truck"></i> Süt Teslimatları</a></li>
                <li><a href="delivery.php"><i class="fa fa-users"></i> Kurye Performansı</a></li>
                <li><a href="invoice.php"><i class="fa fa-file-text"></i> Fatura Sistemi</a></li>
                <li><a href="feedback.php"><i class="fa fa-comments"></i> Destek Talepleri</a></li>
                <li><a href="../logout.php"><i class="fa fa-sign-out"></i> Çıkış</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        
        <div class="dashboard-header">
            <div class="dashboard-title">Bölge Bazlı Süt Üretim Analizleri</div>
            <div class="dashboard-subtitle">Detaylı performans ve trend analizleri</div>
        </div>

        <div class="filter-section">
            <form method="post" class="filter-form">
                <div class="filter-group">
                    <label for="village">Köy Seçin:</label>
                    <select id="village" name="village">
                        <option value="">Tüm Köyler</option>
                        <?php
                        $village_query = "SELECT koy_id, koy_ad FROM koyler ORDER BY koy_ad";
                        $village_result = mysqli_query($connection, $village_query);
                        while($village_row = mysqli_fetch_assoc($village_result)) {
                            $selected = (isset($_POST['village']) && $_POST['village'] == $village_row['koy_id']) ? 'selected' : '';
                            echo "<option value='".$village_row['koy_id']."' $selected>".$village_row['koy_ad']."</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="milk_type">Süt Tipi:</label>
                    <select id="milk_type" name="milk_type">
                        <option value="">Tüm Süt Tipleri</option>
                        <option value="cow" <?= (isset($_POST['milk_type']) && $_POST['milk_type'] == 'cow') ? 'selected' : '' ?>>İnek Sütü</option>
                        <option value="goat" <?= (isset($_POST['milk_type']) && $_POST['milk_type'] == 'goat') ? 'selected' : '' ?>>Keçi Sütü</option>
                        <option value="sheep" <?= (isset($_POST['milk_type']) && $_POST['milk_type'] == 'sheep') ? 'selected' : '' ?>>Koyun Sütü</option>
                    </select>
                </div>
                
                <button type="submit" class="filter-btn">
                    <i class="fa fa-search"></i> Analiz Yap
                </button>
            </form>
        </div>

        <?php
        // Süt tipi çeviri fonksiyonu
        function translateMilkType($type) {
            $translations = [
                'cow' => 'İnek Sütü',
                'goat' => 'Keçi Sütü', 
                'sheep' => 'Koyun Sütü'
            ];
            return $translations[$type] ?? $type;
        }

        // Filtre koşullarını oluştur
        $where_conditions = array();
        $selected_village = '';
        $selected_milk_type = '';

        if (isset($_POST['village']) && !empty($_POST['village'])) {
            $village_id = mysqli_real_escape_string($connection, $_POST['village']);
            $where_conditions[] = "l.koy_id = '$village_id'";
            
            $village_name_query = "SELECT koy_ad FROM koyler WHERE koy_id = '$village_id'";
            $village_name_result = mysqli_query($connection, $village_name_query);
            $village_row = mysqli_fetch_assoc($village_name_result);
            $selected_village = $village_row ? $village_row['koy_ad'] : '';
        }

        if (isset($_POST['milk_type']) && !empty($_POST['milk_type'])) {
            $milk_type = mysqli_real_escape_string($connection, $_POST['milk_type']);
            $where_conditions[] = "l.milk_types LIKE '%$milk_type%'";
            $selected_milk_type = translateMilkType($milk_type);
        }

        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

        // İstatistik sorguları
        $total_producers_query = "SELECT COUNT(DISTINCT l.email) as count FROM login l $where_clause";
        $total_producers_result = mysqli_query($connection, $total_producers_query);
        $total_producers = mysqli_fetch_assoc($total_producers_result)['count'];

        $total_milk_query = "
            SELECT COALESCE(SUM(fd.quantity), 0) as total 
            FROM login l
            LEFT JOIN food_login fl ON l.email = fl.email
            LEFT JOIN food_donations fd ON fl.Fid = fd.Fid
            $where_clause
        ";
        $total_milk_result = mysqli_query($connection, $total_milk_query);
        $total_milk = mysqli_fetch_assoc($total_milk_result)['total'];

        $avg_milk_query = "
            SELECT COALESCE(AVG(fd.quantity), 0) as avg 
            FROM login l
            LEFT JOIN food_login fl ON l.email = fl.email
            LEFT JOIN food_donations fd ON fl.Fid = fd.Fid
            $where_clause AND fd.quantity IS NOT NULL
        ";
        $avg_milk_result = mysqli_query($connection, $avg_milk_query);
        $avg_milk = round(mysqli_fetch_assoc($avg_milk_result)['avg'], 1);

        $active_villages_query = "SELECT COUNT(DISTINCT l.koy_id) as count FROM login l $where_clause";
        $active_villages_result = mysqli_query($connection, $active_villages_query);
        $active_villages = mysqli_fetch_assoc($active_villages_result)['count'];
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_producers ?></div>
                <div class="stat-label">Toplam Üretici</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($total_milk, 1) ?></div>
                <div class="stat-label">Toplam Süt (Litre)</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $avg_milk ?></div>
                <div class="stat-label">Ortalama Teslimat (Litre)</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $active_villages ?></div>
                <div class="stat-label">Aktif Köy</div>
            </div>
        </div>

        <div class="analytics-grid">
            <div class="chart-container">
                <div class="chart-title">Köy Bazlı Süt Üretimi</div>
                <canvas id="villageChart" width="400" height="300"></canvas>
            </div>

            <div class="chart-container">
                <div class="chart-title">Süt Tipi Dağılımı</div>
                <canvas id="milkTypeChart" width="400" height="300"></canvas>
            </div>

            <div class="chart-container">
                <div class="chart-title">Aylık Üretim Trendi</div>
                <canvas id="trendChart" width="400" height="300"></canvas>
            </div>
        </div>

        <div class="table-section">
            <div class="table-header">
                <div>
                    <i class="fa fa-list"></i> Detaylı Üretici Bilgileri
                    <?= $selected_village ? " - " . $selected_village : "" ?>
                    <?= $selected_milk_type ? " - " . $selected_milk_type : "" ?>
                </div>
                <div class="search-box">
                    <input type="text" id="producerSearch" placeholder="Üretici ara...">
                    <i class="fa fa-search"></i>
                </div>
            </div>
            
            <div class="table-container">
                <table id="producersTable">
                    <thead>
                        <tr>
                            <th>Üretici Adı</th>
                            <th>Köy</th>
                            <th>Süt Tipleri</th>
                            <th>Son Teslimat</th>
                            <th>Ortalama Miktar (Litre)</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $detail_query = "
                            SELECT l.name, l.email, l.milk_types, k.koy_ad, 
                            MAX(fd.date) as last_delivery, 
                            AVG(fd.quantity) as avg_quantity
                            FROM login l 
                            LEFT JOIN koyler k ON l.koy_id = k.koy_id 
                            LEFT JOIN food_login fl ON l.email = fl.email 
                            LEFT JOIN food_donations fd ON fl.Fid = fd.Fid 
                            $where_clause
                            GROUP BY l.email, l.name, l.milk_types, k.koy_ad
                            ORDER BY last_delivery DESC
                        ";
                        $detail_result = mysqli_query($connection, $detail_query);
                        
                        while($row = mysqli_fetch_assoc($detail_result)) {
                            echo "<tr>";
                            echo "<td><strong>".$row['name']."</strong></td>";
                            echo "<td>".($row['koy_ad'] ?? 'Bilinmiyor')."</td>";
                            echo "<td>";
                            
                            if($row['milk_types']) {
                                $milk_types = explode(',', $row['milk_types']);
                                foreach($milk_types as $type) {
                                    $type = trim($type);
                                    $turkish_type = translateMilkType($type);
                                    $class = '';
                                    switch($type) {
                                        case 'cow': $class = 'cow-milk'; break;
                                        case 'goat': $class = 'goat-milk'; break;
                                        case 'sheep': $class = 'sheep-milk'; break;
                                    }
                                    echo "<span class='milk-type-badge $class'>$turkish_type</span>";
                                }
                            }
                            
                            echo "</td>";
                            echo "<td>".($row['last_delivery'] ? date('d.m.Y H:i', strtotime($row['last_delivery'])) : 'Henüz teslimat yok')."</td>";
                            echo "<td><strong>".round($row['avg_quantity'] ?? 0, 1)." L</strong></td>";
                            echo "<td>".$row['email']."</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Köy Bazlı Süt Üretimi Grafiği
        <?php
        $village_data_query = "
            SELECT k.koy_ad, COALESCE(SUM(fd.quantity), 0) as total 
            FROM login l
            LEFT JOIN koyler k ON l.koy_id = k.koy_id
            LEFT JOIN food_login fl ON l.email = fl.email
            LEFT JOIN food_donations fd ON fl.Fid = fd.Fid
            $where_clause
            GROUP BY k.koy_id, k.koy_ad 
            HAVING total > 0
            ORDER BY total DESC 
            LIMIT 10
        ";
        $village_data_result = mysqli_query($connection, $village_data_query);
        
        $villages = array();
        $village_totals = array();
        while($row = mysqli_fetch_assoc($village_data_result)) {
            $villages[] = $row['koy_ad'];
            $village_totals[] = floatval($row['total']);
        }
        ?>

        const villageCtx = document.getElementById('villageChart').getContext('2d');
        new Chart(villageCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($villages) ?>,
                datasets: [{
                    label: 'Süt Miktarı (Litre)',
                    data: <?= json_encode($village_totals) ?>,
                    backgroundColor: 'rgba(107, 190, 69, 0.7)',
                    borderColor: 'rgba(107, 190, 69, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Süt Tipi Dağılımı
        <?php
        $milk_type_counts = array('cow' => 0, 'goat' => 0, 'sheep' => 0);
        $milk_type_query = "SELECT milk_types FROM login l $where_clause";
        $milk_type_result = mysqli_query($connection, $milk_type_query);
        
        while($row = mysqli_fetch_assoc($milk_type_result)) {
            if($row['milk_types']) {
                $types = explode(',', $row['milk_types']);
                foreach($types as $type) {
                    $type = trim($type);
                    if(isset($milk_type_counts[$type])) {
                        $milk_type_counts[$type]++;
                    }
                }
            }
        }
        ?>

        const milkTypeCtx = document.getElementById('milkTypeChart').getContext('2d');
        new Chart(milkTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['İnek Sütü', 'Keçi Sütü', 'Koyun Sütü'],
                datasets: [{
                    data: [<?= $milk_type_counts['cow'] ?>, <?= $milk_type_counts['goat'] ?>, <?= $milk_type_counts['sheep'] ?>],
                    backgroundColor: ['#1976d2', '#7b1fa2', '#f57c00'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Aylık Trend Grafiği
        <?php
        $trend_query = "
            SELECT DATE_FORMAT(fd.date, '%Y-%m') as month, COALESCE(SUM(fd.quantity), 0) as total 
            FROM login l
            LEFT JOIN food_login fl ON l.email = fl.email
            LEFT JOIN food_donations fd ON fl.Fid = fd.Fid
            $where_clause
            GROUP BY DATE_FORMAT(fd.date, '%Y-%m') 
            HAVING total > 0
            ORDER BY month DESC 
            LIMIT 6
        ";
        $trend_result = mysqli_query($connection, $trend_query);
        
        $months = array();
        $monthly_totals = array();
        while($row = mysqli_fetch_assoc($trend_result)) {
            $months[] = $row['month'];
            $monthly_totals[] = floatval($row['total']);
        }
        
        $months = array_reverse($months);
        $monthly_totals = array_reverse($monthly_totals);
        ?>

        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Aylık Süt Üretimi (Litre)',
                    data: <?= json_encode($monthly_totals) ?>,
                    borderColor: '#6BBE45',
                    backgroundColor: 'rgba(107, 190, 69, 0.1)',
                    borderWidth: 3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Tablo arama fonksiyonu
        document.getElementById('producerSearch').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const table = document.getElementById('producersTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(searchText)) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        });
    </script>

</body>
</html>