
<?php
ob_start(); 
include("connect.php"); 
if($_SESSION['name']==''){
    header("location:signin.php");
}

// Durum güncelleme işlemi
if(isset($_POST['update_status'])) {
    $feedback_id = $_POST['feedback_id'];
    $new_status = $_POST['new_status'];
    $admin_note = $_POST['admin_note'];
    $response_message = $_POST['response_message'];
    
    $resolved_date = ($new_status == 2) ? "NOW()" : "NULL";
    $resolved_by = ($new_status == 2) ? "1" : "NULL"; // Tek admin olduğu için 1
    
    $update_query = "UPDATE user_feedback SET 
                     status = $new_status, 
                     admin_note = '$admin_note',
                     response_message = '$response_message',
                     resolved_date = $resolved_date,
                     resolved_by = $resolved_by
                     WHERE feedback_id = $feedback_id";
    
    mysqli_query($connection, $update_query);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Öncelik güncelleme
if(isset($_POST['update_priority'])) {
    $feedback_id = $_POST['feedback_id'];
    $new_priority = $_POST['new_priority'];
    
    $priority_query = "UPDATE user_feedback SET priority = $new_priority WHERE feedback_id = $feedback_id";
    mysqli_query($connection, $priority_query);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Filtreleme
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_conditions = array();
if($status_filter != 'all') {
    $where_conditions[] = "status = $status_filter";
}
if($priority_filter != 'all') {
    $where_conditions[] = "priority = $priority_filter";
}
if(!empty($search)) {
    $where_conditions[] = "(name LIKE '%$search%' OR email LIKE '%$search%' OR message LIKE '%$search%')";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Destek Talepleri - Balkan Süt</title>
    
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
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: #1a2357;
            color: #6BBE45;
            transform: translateX(10px);
        }

        /* Ana içerik alanı */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
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

        /* İstatistik Kutuları */
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
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-pending { color: #ff6b6b; }
        .stat-progress { color: #4ecdc4; }
        .stat-resolved { color: #6BBE45; }
        .stat-high { color: #e74c3c; }

        /* Filtre Bölümü */
        .filters {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        select, input[type="text"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-filter {
            background-color: #6BBE45;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-filter:hover {
            background-color: #5aa83a;
        }

        /* Tablo Bölümü */
        .table-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
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
            vertical-align: top;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #253078;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        /* Durum Badge'leri */
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }

        .status-pending {
            background-color: #ffebee;
            color: #c62828;
        }

        .status-progress {
            background-color: #e0f2f1;
            color: #00695c;
        }

        .status-resolved {
            background-color: #e8f5e8;
            color: #2e7d32;
        }

        /* Öncelik Badge'leri */
        .priority-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }

        .priority-low {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .priority-medium {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .priority-high {
            background-color: #ffebee;
            color: #d32f2f;
        }

        /* Butonlar */
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
            transition: all 0.3s ease;
        }

        .btn-success {
            background-color: #6BBE45;
            color: white;
        }

        .btn-warning {
            background-color: #f39c12;
            color: white;
        }

        .btn-info {
            background-color: #3498db;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }

        /* Modal Stili */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #253078;
        }

        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
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
                <li><a href="analytics.php"><i class="fa fa-bar-chart"></i> Analizler</a></li>
                <li><a href="donate.php"><i class="fa fa-truck"></i> Süt Teslimatları</a></li>
                <li><a href="delivery.php"><i class="fa fa-users"></i> Kurye Performansı</a></li>
                <li><a href="invoice.php"><i class="fa fa-file-text"></i> Fatura Sistemi</a></li>
                <li><a href="feedback.php" class="active"><i class="fa fa-comments"></i> Destek Talepleri</a></li>
                
                
                <li><a href="../logout.php"><i class="fa fa-sign-out"></i> Çıkış</a></li>
            </ul>
        </nav>
    </div>

    <!-- Ana İçerik -->
    <div class="main-content">
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="dashboard-title">Destek Talepleri Yönetimi</div>
            <div class="dashboard-subtitle">Üretici destek taleplerini görüntüle ve yönet</div>
        </div>

        <!-- İstatistik Kutuları -->
        <div class="stats-boxes">
            <?php
            $stats_query = "SELECT 
                COUNT(CASE WHEN status = 0 THEN 1 END) as pending,
                COUNT(CASE WHEN status = 1 THEN 1 END) as progress,
                COUNT(CASE WHEN status = 2 THEN 1 END) as resolved,
                COUNT(CASE WHEN priority = 3 THEN 1 END) as high_count
                FROM user_feedback";
            $stats_result = mysqli_query($connection, $stats_query);
            $stats = mysqli_fetch_assoc($stats_result);
            ?>
            
            <div class="stat-box">
                <div class="stat-number stat-pending"><?= $stats['pending'] ?></div>
                <div>Bekleyen Talepler</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number stat-progress"><?= $stats['progress'] ?></div>
                <div>İncelenen Talepler</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number stat-resolved"><?= $stats['resolved'] ?></div>
                <div>Çözülen Talepler</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number stat-high"><?= $stats['high_count'] ?></div>
                <div>Yüksek Öncelik</div>
            </div>
        </div>

        <!-- Filtre Bölümü -->
        <div class="filters">
            <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; width: 100%;">
                <div class="filter-group">
                    <label for="status">Durum:</label>
                    <select name="status" id="status">
                        <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Tümü</option>
                        <option value="0" <?= $status_filter == '0' ? 'selected' : '' ?>>Bekliyor</option>
                        <option value="1" <?= $status_filter == '1' ? 'selected' : '' ?>>İnceleniyor</option>
                        <option value="2" <?= $status_filter == '2' ? 'selected' : '' ?>>Çözüldü</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="priority">Öncelik:</label>
                    <select name="priority" id="priority">
                        <option value="all" <?= $priority_filter == 'all' ? 'selected' : '' ?>>Tümü</option>
                        <option value="1" <?= $priority_filter == '1' ? 'selected' : '' ?>>Düşük</option>
                        <option value="2" <?= $priority_filter == '2' ? 'selected' : '' ?>>Orta</option>
                        <option value="3" <?= $priority_filter == '3' ? 'selected' : '' ?>>Yüksek</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search">Arama:</label>
                    <input type="text" name="search" id="search" placeholder="İsim, email veya mesajda ara..." value="<?= $search ?>" style="width: 250px;">
                </div>
                
                <button type="submit" class="btn-filter">Filtrele</button>
                <a href="feedback.php" class="btn-filter" style="background-color: #6c757d; text-decoration: none;">Temizle</a>
            </form>
        </div>

        <!-- Tablo Bölümü -->
        <div class="table-section">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>İsim</th>
                            <th>Email</th>
                            <th>Mesaj</th>
                            <th>Durum</th>
                            <th>Öncelik</th>
                            <th>Tarih</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM user_feedback $where_clause ORDER BY 
                                 CASE WHEN status = 0 THEN 1 WHEN status = 1 THEN 2 ELSE 3 END,
                                 priority DESC, created_date DESC";
                        $result = mysqli_query($connection, $query);
                        
                        if($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                // Durum badge
                                $status_class = '';
                                $status_text = '';
                                switch($row['status']) {
                                    case 0: $status_class = 'status-pending'; $status_text = 'Bekliyor'; break;
                                    case 1: $status_class = 'status-progress'; $status_text = 'İnceleniyor'; break;
                                    case 2: $status_class = 'status-resolved'; $status_text = 'Çözüldü'; break;
                                }
                                
                                // Öncelik badge
                                $priority_class = '';
                                $priority_text = '';
                                switch($row['priority']) {
                                    case 1: $priority_class = 'priority-low'; $priority_text = 'Düşük'; break;
                                    case 2: $priority_class = 'priority-medium'; $priority_text = 'Orta'; break;
                                    case 3: $priority_class = 'priority-high'; $priority_text = 'Yüksek'; break;
                                }
                                
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars(substr($row['message'], 0, 100)) . "..." . "</td>";
                                echo "<td><span class='status-badge $status_class'>$status_text</span></td>";
                                echo "<td><span class='priority-badge $priority_class'>$priority_text</span></td>";
                                echo "<td>" . date('d.m.Y H:i', strtotime($row['created_date'])) . "</td>";
                                echo "<td>";
                                
                                if($row['status'] != 2) {
                                    echo "<button class='btn btn-info' onclick='openModal(" . $row['feedback_id'] . ")'>Yönet</button>";
                                }
                                
                                echo "<button class='btn btn-warning' onclick='setPriority(" . $row['feedback_id'] . ", " . $row['priority'] . ")'>Öncelik</button>";
                                echo "<button class='btn btn-success' onclick='viewDetails(" . $row['feedback_id'] . ")'>Detay</button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align: center; padding: 30px;'>Herhangi bir destek talebi bulunamadı.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Yönetim Modali -->
    <div id="manageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="color: #253078; margin-bottom: 20px;">Destek Talebi Yönetimi</h2>
            <form method="POST" action="">
                <input type="hidden" id="feedback_id" name="feedback_id">
                
                <div class="form-group">
                    <label for="new_status">Durum:</label>
                    <select name="new_status" id="new_status" required>
                        <option value="0">Bekliyor</option>
                        <option value="1">İnceleniyor</option>
                        <option value="2">Çözüldü</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="admin_note">Admin Notu:</label>
                    <textarea name="admin_note" id="admin_note" placeholder="İç not..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="response_message">Kullanıcıya Yanıt:</label>
                    <textarea name="response_message" id="response_message" placeholder="Kullanıcıya gönderilecek yanıt mesajı..."></textarea>
                </div>
                
                <button type="submit" name="update_status" class="btn-filter" style="width: 100%; padding: 15px;">Güncelle</button>
            </form>
        </div>
    </div>

    <!-- Öncelik Modali -->
    <div id="priorityModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePriorityModal()">&times;</span>
            <h2 style="color: #253078; margin-bottom: 20px;">Öncelik Belirleme</h2>
            <form method="POST" action="">
                <input type="hidden" id="priority_feedback_id" name="feedback_id">
                
                <div class="form-group">
                    <label for="new_priority">Öncelik Seviyesi:</label>
                    <select name="new_priority" id="new_priority" required>
                        <option value="1">Düşük</option>
                        <option value="2">Orta</option>
                        <option value="3">Yüksek</option>
                    </select>
                </div>
                
                <button type="submit" name="update_priority" class="btn-filter" style="width: 100%; padding: 15px;">Güncelle</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(feedbackId) {
            document.getElementById('feedback_id').value = feedbackId;
            document.getElementById('manageModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('manageModal').style.display = 'none';
        }

        function setPriority(feedbackId, currentPriority) {
            document.getElementById('priority_feedback_id').value = feedbackId;
            document.getElementById('new_priority').value = currentPriority;
            document.getElementById('priorityModal').style.display = 'block';
        }

        function closePriorityModal() {
            document.getElementById('priorityModal').style.display = 'none';
        }

        function viewDetails(feedbackId) {
            // Detay görüntüleme fonksiyonu - isteğe bağlı geliştirilebilir
            alert('Detay görüntüleme özelliği geliştirilebilir. ID: ' + feedbackId);
        }

        // Modal dışına tıklandığında kapatma
        window.onclick = function(event) {
            const manageModal = document.getElementById('manageModal');
            const priorityModal = document.getElementById('priorityModal');
            
            if (event.target === manageModal) {
                manageModal.style.display = 'none';
            }
            if (event.target === priorityModal) {
                priorityModal.style.display = 'none';
            }
        }
    </script>

</body>
</html>