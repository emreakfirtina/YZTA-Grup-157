<?php
include("login.php"); 

if($_SESSION['name']==''){
    header("location: signup.php");
}

// Destek talebi gönderme işlemi
if(isset($_POST['send_feedback'])) {
    $name = $_SESSION['name'];
    $email = $_SESSION['email'];
    $message = mysqli_real_escape_string($connection, $_POST['message']);
    
    // user_feedback tablosuna kaydet
    $feedback_query = "INSERT INTO user_feedback (name, email, message, status) VALUES ('$name', '$email', '$message', 0)";
    
    if(mysqli_query($connection, $feedback_query)) {
        // Son eklenen feedback_id'yi al
        $feedback_id = mysqli_insert_id($connection);
        
        // feed_login tablosuna bağlantı kaydet
        $relation_query = "INSERT INTO feed_login (feedback_id, email) VALUES ('$feedback_id', '$email')";
        
        if(mysqli_query($connection, $relation_query)) {
            $success_message = "Destek talebiniz başarıyla gönderildi!";
        } else {
            $error_message = "Bağlantı kaydedilirken hata oluştu!";
        }
    } else {
        $error_message = "Destek talebi gönderilirken bir hata oluştu!";
    }
}

// Süt türlerini çeviren fonksiyon (virgülle ayrılmış türler için)
function translateMilkTypes($milk_types_string) {
    $translations = [
        'cow' => 'İnek Sütü',
        'goat' => 'Keçi Sütü', 
        'sheep' => 'Koyun Sütü',
        'buffalo' => 'Manda Sütü'
    ];
    
    if(empty($milk_types_string)) {
        return 'Belirtilmemiş';
    }
    
    // Virgülle ayrılmış türleri diziye çevir
    $milk_types_array = explode(',', $milk_types_string);
    $translated_types = [];
    
    foreach($milk_types_array as $type) {
        $type = trim(strtolower($type)); // Boşlukları ve büyük harfleri temizle
        if(isset($translations[$type])) {
            $translated_types[] = $translations[$type];
        }
    }
    
    // Türkçe türleri virgülle birleştir
    return !empty($translated_types) ? implode(', ', $translated_types) : 'Belirtilmemiş';
}

// Status çevirisi fonksiyonu
function getStatusText($status) {
    switch($status) {
        case 0: return 'Bekliyor';
        case 1: return 'İnceleniyor';
        case 2: return 'Çözüldü';
        default: return 'Bilinmiyor';
    }
}

// Kullanıcının geçmiş destek taleplerini getir
$user_email = $_SESSION['email'];
$history_query = "SELECT uf.feedback_id, uf.message, uf.status, uf.admin_note, uf.response_message, uf.created_date 
                  FROM user_feedback uf 
                  INNER JOIN feed_login fl ON uf.feedback_id = fl.feedback_id 
                  WHERE fl.email = '$user_email' 
                  ORDER BY uf.created_date DESC";

$history_result = mysqli_query($connection, $history_query);
?> 

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Balkan Süt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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

        /* Hamburger menü (mobil için) */
        .hamburger {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
            background-color: #253078;
            padding: 10px;
            border-radius: 5px;
        }

        .hamburger .line {
            width: 25px;
            height: 3px;
            background-color: white;
            transition: 0.3s;
        }

        /* Ana içerik alanı */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .profile-container {
            max-width: 1000px;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }

        .profile-header {
            font-size: 28px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 30px;
            border-bottom: 2px solid #6BBE45;
            padding-bottom: 10px;
        }

        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #6BBE45;
        }

        .info-label {
            font-weight: bold;
            color: #253078;
            margin-bottom: 5px;
        }

        .info-value {
            color: #666;
            font-size: 16px;
        }

        .logout-btn {
            background-color: #6BBE45;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-bottom: 30px;
        }

        .logout-btn:hover {
            background-color: #5aa83a;
        }

        /* Destek Talebi Formu */
        .support-section {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .support-title {
            font-size: 20px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            color: #253078;
            margin-bottom: 8px;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            background-color: #253078;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #1a2357;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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

        /* Geçmiş Talepler */
        .history-section {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #ddd;
        }

        .history-item {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #6BBE45;
        }

        .history-item:last-child {
            margin-bottom: 0;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .status-waiting {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-reviewing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-solved {
            background-color: #d4edda;
            color: #155724;
        }

        .history-message {
            color: #666;
            margin-bottom: 10px;
        }

        .admin-response {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .admin-response strong {
            color: #253078;
        }

        /* Responsive düzenlemeler */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding-top: 70px;
                padding: 70px 15px 20px;
            }

            .profile-info {
                grid-template-columns: 1fr;
            }

            .profile-container {
                margin: 20px 10px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Hamburger menü (mobil için) -->
    <div class="hamburger">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>

    <!-- Sol Sidebar Menü -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">ÜRETİCİ</div>
        </div>
        <nav class="sidebar-menu">
            <ul>
                <li><a href="home.php">Ana Sayfa</a></li>
                <li><a href="fooddonateform.php">Süt Teslim</a></li>
                <li><a href="contact.html">Yapay Zeka Yardımı</a></li>
                <li><a href="profile.php" class="active">Profil</a></li>
            </ul>
        </nav>
    </div>

    <!-- Ana İçerik -->
    <div class="main-content">
        <div class="profile-container">
            <!-- Profil Başlığı -->
            <div class="profile-header">
                <i class="fa fa-user" style="margin-right: 10px;"></i>
                Profil Bilgileri
            </div>

            <!-- Başarı/Hata Mesajları -->
            <?php if(isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if(isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fa fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Profil Bilgileri -->
            <div class="profile-info">
                <div class="info-item">
                    <div class="info-label">Ad Soyad</div>
                    <div class="info-value"><?php echo $_SESSION['name']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">E-posta</div>
                    <div class="info-value"><?php echo $_SESSION['email']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Süt Türü</div>
                    <div class="info-value">
                        <?php 
                        $email = $_SESSION['email'];
                        $query = "SELECT milk_types FROM login WHERE email='$email'";
                        $result = mysqli_query($connection, $query);
                        if($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            // Güncellenmiş translateMilkTypes fonksiyonunu kullanıyoruz
                            echo translateMilkTypes($row['milk_types']);
                        } else {
                            echo 'Belirtilmemiş';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Çıkış Yap Butonu -->
            <a href="logout.php" class="logout-btn">
                <i class="fa fa-sign-out"></i> Çıkış Yap
            </a>

            <!-- Destek Talebi Bölümü -->
            <div class="support-section">
                <div class="support-title">
                    <i class="fa fa-support" style="margin-right: 10px;"></i>
                    Destek Talebi Gönder
                </div>
                <p style="color: #666; margin-bottom: 20px;">
                    Herhangi bir sorunuz veya öneriniz varsa, admin ile iletişime geçmek için aşağıdaki formu kullanabilirsiniz.
                </p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="message">Mesajınız:</label>
                        <textarea 
                            id="message" 
                            name="message" 
                            placeholder="Lütfen sorunuzu veya önerinizi detaylı bir şekilde yazın..."
                            required
                        ></textarea>
                    </div>
                    <button type="submit" name="send_feedback" class="submit-btn">
                        <i class="fa fa-send"></i> Destek Talebi Gönder
                    </button>
                </form>
            </div>

            <!-- Geçmiş Destek Talepleri -->
            <div class="history-section">
                <div class="support-title">
                    <i class="fa fa-history" style="margin-right: 10px;"></i>
                    Geçmiş Destek Taleplerim
                </div>
                
                <?php if($history_result && mysqli_num_rows($history_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($history_result)): ?>
                        <div class="history-item">
                            <div class="status-badge status-<?php echo $row['status'] == 0 ? 'waiting' : ($row['status'] == 1 ? 'reviewing' : 'solved'); ?>">
                                <?php echo getStatusText($row['status']); ?>
                            </div>
                            
                            <div class="history-message">
                                <strong>Mesajınız:</strong><br>
                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                            </div>
                            
                            <?php if($row['response_message']): ?>
                                <div class="admin-response">
                                    <strong>Yanıt:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($row['response_message'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($row['admin_note']): ?>
                                <div class="admin-response" style="margin-top: 5px;">
                                    <strong>Admin Notu:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($row['admin_note'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <small style="color: #999; margin-top: 10px; display: block;">
                                Tarih: <?php echo date('d.m.Y H:i', strtotime($row['created_date'])); ?>
                            </small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #666; text-align: center; padding: 20px;">
                        Henüz destek talebi bulunmamaktadır.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Hamburger menü toggle (mobil için)
        document.querySelector(".hamburger").onclick = function() {
            document.querySelector(".sidebar").classList.toggle("active");
        }
    </script>
</body>
</html>