<?php
ob_start(); 
include("connect.php"); 

// Session kontrolü
if(!isset($_SESSION['name']) || empty($_SESSION['name'])){
    header("location:signin.php");
    exit();
}

// Süt türü fiyatları
function getMilkPrice($milk_type) {
    $prices = [
        'inek' => 17.15,
        'keçi' => 25.50,
        'koyun' => 32.80,
        'cow' => 17.15,
        'goat' => 25.50,
        'sheep' => 32.80
    ];
    return isset($prices[strtolower($milk_type)]) ? $prices[strtolower($milk_type)] : 17.15;
}

// Süt türü görüntüleme
function getMilkTypeDisplay($milk_type) {
    switch(strtolower($milk_type)) {
        case 'inek':
        case 'cow':
            return 'İnek Sütü';
        case 'keçi':
        case 'goat':
            return 'Keçi Sütü';
        case 'koyun':
        case 'sheep':
            return 'Koyun Sütü';
        default:
            return ucfirst($milk_type) . ' Sütü';
    }
}

$error_message = '';
$invoice_data = null;
$producer_info = null;

// Fatura görüntüleme işlemi - FID İLE ARAMA
if (isset($_GET['invoice_id'])) {
    $invoice_id = mysqli_real_escape_string($connection, $_GET['invoice_id']);
    
    // Fatura bilgilerini al - FID ile arama yapıyoruz
    $invoice_query = "SELECT * FROM food_donations WHERE Fid='$invoice_id'";
    $invoice_result = mysqli_query($connection, $invoice_query);
    
    if ($invoice_result && mysqli_num_rows($invoice_result) > 0) {
        $invoice_data = mysqli_fetch_assoc($invoice_result);
        
        // Üretici bilgilerini al
        $producer_email = mysqli_real_escape_string($connection, $invoice_data['email']);
        $producer_query = "SELECT * FROM login WHERE email='$producer_email'";
        $producer_result = mysqli_query($connection, $producer_query);
        
        if($producer_result && mysqli_num_rows($producer_result) > 0) {
            $producer_info = mysqli_fetch_assoc($producer_result);
        } else {
            $error_message = "Üretici bilgileri bulunamadı.";
        }
        
        // Fiyat ve tutar hesapla
        if($producer_info) {
            $unit_price = getMilkPrice($invoice_data['milk_type']);
            $total_amount = $invoice_data['quantity'] * $unit_price;
        }
    } else {
        $error_message = "Fatura bulunamadı. ID: $invoice_id";
    }
}

// Tüm süt teslimatlarını listele - FID kullanarak
$all_donations_query = "SELECT fd.*, l.name as producer_name 
                       FROM food_donations fd 
                       INNER JOIN login l ON fd.email = l.email 
                       ORDER BY fd.date DESC, fd.Fid DESC";
$all_donations_result = mysqli_query($connection, $all_donations_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>E-İrsaliye/Fatura - Balkan Süt</title>
    
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

        /* Error Message */
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Teslimat Listesi */
        .donations-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }

        .section-header {
            font-size: 24px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 20px;
            border-bottom: 2px solid #6BBE45;
            padding-bottom: 10px;
        }

        .donations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .donations-table th,
        .donations-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .donations-table th {
            background-color: #253078;
            color: white;
            font-weight: bold;
        }

        .donations-table tr:hover {
            background-color: #f8f9fa;
        }

        .btn-invoice {
            background-color: #6BBE45;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .btn-invoice:hover {
            background-color: #5aa83a;
            color: white;
        }

        /* Fatura Bölümü */
        .invoice-section {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            margin-bottom: 30px;
            display: none;
        }

        .invoice-section.show {
            display: block;
        }

        .invoice-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #6BBE45;
        }

        .company-info h2 {
            color: #253078;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-number {
            font-size: 20px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 10px;
        }

        .invoice-date {
            color: #666;
        }

        .invoice-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .party-info {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #6BBE45;
        }

        .party-info h3 {
            color: #253078;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .party-info p {
            margin-bottom: 5px;
            color: #555;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .invoice-table th {
            background-color: #253078;
            color: white;
            font-weight: bold;
        }

        .invoice-table .text-right {
            text-align: right;
        }

        .invoice-table .text-center {
            text-align: center;
        }

        .invoice-total {
            text-align: right;
            margin-bottom: 30px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .total-row.final {
            font-size: 20px;
            font-weight: bold;
            color: #253078;
            border-bottom: 3px solid #6BBE45;
        }

        .invoice-footer {
            text-align: center;
            padding-top: 30px;
            border-top: 2px solid #6BBE45;
            color: #666;
        }

        .btn-download {
            background-color: #253078;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-right: 10px;
            transition: background-color 0.3s ease;
        }

        .btn-download:hover {
            background-color: #1a2357;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .btn-back:hover {
            background-color: #5a6268;
            color: white;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        /* Print Styles */
        @media print {
            .sidebar,
            .dashboard-header,
            .donations-section,
            .btn-download,
            .btn-back {
                display: none !important;
            }

            .main-content {
                margin-left: 0;
                padding: 0;
            }

            .invoice-section {
                box-shadow: none;
                border: 1px solid #ddd;
            }
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

            .invoice-header,
            .invoice-parties {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .donations-table {
                font-size: 14px;
            }

            .donations-table th,
            .donations-table td {
                padding: 8px;
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
                <li><a href="#" class="active"><i class="fa fa-file-text"></i>Fatura Sistemi</a></li>
                <li><a href="feedback.php"><i class="fa fa-comments"></i> Destek Talepleri</a></li>
                
                
                <li><a href="../logout.php"><i class="fa fa-sign-out"></i> Çıkış</a></li>
            </ul>
        </nav>
    </div>

    <!-- Ana İçerik -->
    <div class="main-content">
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="dashboard-title">E-İrsaliye / Fatura Sistemi</div>
            <div class="dashboard-subtitle">Her süt teslimi için otomatik fatura oluşturma sistemi</div>
        </div>

        <?php if($error_message && isset($_GET['invoice_id'])): ?>
        <div class="error-message">
            <i class="fa fa-exclamation-triangle"></i> 
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <?php if (!isset($_GET['invoice_id'])): ?>
        <!-- Süt Teslimatları Listesi -->
        <div class="donations-section">
            <div class="section-header">
                <i class="fa fa-list"></i> Süt Teslimatları
            </div>
            
            <table class="donations-table">
                <thead>
                    <tr>
                        <th>Fatura No</th>
                        <th>Üretici</th>
                        <th>Süt Türü</th>
                        <th>Tarih</th>
                        <th>Miktar (Lt)</th>
                        <th>Birim Fiyat (TL)</th>
                        <th>Toplam Tutar (TL)</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if($all_donations_result && mysqli_num_rows($all_donations_result) > 0) {
                        while($row = mysqli_fetch_assoc($all_donations_result)) {
                            $unit_price = getMilkPrice($row['milk_type']);
                            $total_amount = $row['quantity'] * $unit_price;
                            $invoice_no = 'BLK' . date('Ymd', strtotime($row['date'])) . str_pad($row['Fid'], 4, '0', STR_PAD_LEFT);
                            
                            echo "<tr>";
                            echo "<td><strong>".$invoice_no."</strong></td>";
                            echo "<td>".$row['producer_name']."</td>";
                            echo "<td>".getMilkTypeDisplay($row['milk_type'])."</td>";
                            echo "<td>".date('d.m.Y', strtotime($row['date']))."</td>";
                            echo "<td>".$row['quantity']."</td>";
                            echo "<td>".number_format($unit_price, 2)."</td>";
                            echo "<td><strong>".number_format($total_amount, 2)." TL</strong></td>";
                            echo "<td><span class='status-badge status-completed'>Tamamlandı</span></td>";
                            echo "<td><a href='?invoice_id=".$row['Fid']."' class='btn-invoice'><i class='fa fa-file-text'></i> Fatura Gör</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' style='text-align: center; color: #666;'>Henüz süt teslim kaydı bulunmuyor.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($invoice_data && $producer_info): ?>
        <!-- Fatura Gösterimi -->
        <div class="invoice-section show" id="invoice-section">
            <div style="text-align: right; margin-bottom: 20px;">
                <a href="?" class="btn-back">
                    <i class="fa fa-arrow-left"></i> Listeye Dön
                </a>
                <button onclick="downloadInvoice()" class="btn-download">
                    <i class="fa fa-download"></i> PDF İndir
                </button>
                <button onclick="printInvoice()" class="btn-download">
                    <i class="fa fa-print"></i> Yazdır
                </button>
            </div>

            <div class="invoice-header">
                <div class="company-info">
                    <h2>BALKAN SÜT A.Ş.</h2>
                    <p><strong>Adres:</strong> Efeler, Aydın Merkez, Aydın</p>
                    <p><strong>Telefon:</strong> +90 256 123 45 67</p>
                    <p><strong>Email:</strong> info@balkansut.com</p>
                    <p><strong>Vergi No:</strong> 1234567890</p>
                </div>
                <div class="invoice-details">
                    <div class="invoice-number">E-İRSALİYE #<?php echo 'BLK' . date('Ymd', strtotime($invoice_data['date'])) . str_pad($invoice_data['Fid'], 4, '0', STR_PAD_LEFT); ?></div>
                    <div class="invoice-date">
                        <strong>Fatura Tarihi:</strong> <?php echo date('d.m.Y'); ?><br>
                        <strong>Teslim Tarihi:</strong> <?php echo date('d.m.Y', strtotime($invoice_data['date'])); ?><br>
                        <strong>Saat:</strong> <?php echo date('H:i'); ?>
                    </div>
                </div>
            </div>

            <div class="invoice-parties">
                <div class="party-info">
                    <h3>GÖNDEREN (Üretici)</h3>
                    <p><strong>Ad Soyad:</strong> <?php echo $producer_info['name']; ?></p>
                    <p><strong>Email:</strong> <?php echo $producer_info['email']; ?></p>
                    <p><strong>Telefon:</strong> <?php echo isset($producer_info['phoneno']) ? $producer_info['phoneno'] : 'Belirtilmemiş'; ?></p>
                    <p><strong>Konum:</strong> <?php echo isset($producer_info['location']) ? $producer_info['location'] : 'Aydın'; ?></p>
                </div>
                <div class="party-info">
                    <h3>ALAN (Balkan Süt)</h3>
                    <p><strong>Firma:</strong> Balkan Süt A.Ş.</p>
                    <p><strong>Adres:</strong> Efeler, Aydın Merkez, Aydın</p>
                    <p><strong>Telefon:</strong> +90 256 123 45 67</p>
                    <p><strong>Vergi No:</strong> 1234567890</p>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th class="text-center">Sıra</th>
                        <th>Ürün Açıklaması</th>
                        <th class="text-center">Miktar</th>
                        <th class="text-center">Birim</th>
                        <th class="text-right">Birim Fiyat (TL)</th>
                        <th class="text-right">Tutar (TL)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            <strong><?php echo getMilkTypeDisplay($invoice_data['milk_type']); ?></strong><br>
                            <small style="color: #666;">Taze, pastörize edilmemiş süt</small>
                        </td>
                        <td class="text-center"><?php echo $invoice_data['quantity']; ?></td>
                        <td class="text-center">Litre</td>
                        <td class="text-right"><?php echo number_format($unit_price, 2); ?></td>
                        <td class="text-right"><strong><?php echo number_format($total_amount, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="invoice-total">
                <div class="total-row">
                    <span>Ara Toplam:</span>
                    <span><?php echo number_format($total_amount, 2); ?> TL</span>
                </div>
                <div class="total-row">
                    <span>KDV (%0 - Tarımsal Ürün):</span>
                    <span>0,00 TL</span>
                </div>
                <div class="total-row">
                    <span>Stopaj (%0):</span>
                    <span>0,00 TL</span>
                </div>
                <div class="total-row final">
                    <span>GENEL TOPLAM:</span>
                    <span><?php echo number_format($total_amount, 2); ?> TL</span>
                </div>
            </div>

            <div class="invoice-footer">
                <p><strong>Ödeme Bilgileri:</strong></p>
                <p>Bu fatura tutarı üreticinizin hesabına <?php echo date('d.m.Y', strtotime($invoice_data['date'] . ' +3 days')); ?> tarihinde havale edilecektir.</p>
                
                <p style="margin-top: 20px;"><strong>Not:</strong> Bu e-irsaliye, Balkan Süt A.Ş. tarafından elektronik ortamda düzenlenmiştir.</p>
                <p>Süt kalite kontrolü: Standartlara uygun • Teslim durumu: Onaylandı</p>
                <p style="margin-top: 20px; font-style: italic;">Bu belge bilgisayar ortamında düzenlenmiş olup imza gerektirmez.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function printInvoice() {
            window.print();
        }

        function downloadInvoice() {
            // PDF indirme için basit bir çözüm
            var printContents = document.getElementById('invoice-section').innerHTML;
            var originalContents = document.body.innerHTML;
            
            document.body.innerHTML = '<div style="padding: 20px;">' + printContents + '</div>';
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        // Tablo satırlarında hover efekti
        document.querySelectorAll('.donations-table tbody tr').forEach(function(row) {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#e3f2fd';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    </script>

</body>
</html>