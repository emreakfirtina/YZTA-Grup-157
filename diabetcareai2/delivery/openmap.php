<?php
ob_start(); 
include("connect.php"); 
include '../connection.php';
if($_SESSION['name']==''){
    header("location:deliverylogin.php");
}
$name=$_SESSION['name'];
$city=$_SESSION['city'];
$id=$_SESSION['Did'];

// Kurye'nin aldığı siparişleri getir
$sql = "SELECT fd.Fid, fd.name, fd.address, fd.phoneno, fd.quantity, fd.milk_type, fd.date 
        FROM food_donations fd 
        WHERE fd.delivery_by = '$id'";
$result = mysqli_query($connection, $sql);

$orders = array();
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurye Harita Sistemi - Balkan Süt</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
    integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI="
    crossorigin=""/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
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
            margin-bottom: 20px;
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

        /* Harita Container */
        .map-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }

        #map-container {
            width: 100%;
            height: 500px;
            border-radius: 10px;
            z-index: 1;
        }

        .location-info {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #6BBE45;
        }

        /* Sipariş Listesi */
        .orders-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }

        .orders-header {
            font-size: 20px;
            font-weight: bold;
            color: #253078;
            margin-bottom: 15px;
            border-bottom: 2px solid #6BBE45;
            padding-bottom: 10px;
        }

        .order-item {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #6BBE45;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .order-item:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }

        .order-item.selected {
            background-color: #d4edda;
            border-left-color: #28a745;
        }

        .order-name {
            font-weight: bold;
            color: #253078;
            margin-bottom: 5px;
        }

        .order-details {
            font-size: 14px;
            color: #666;
        }

        .btn-navigate {
            background-color: #6BBE45;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .btn-navigate:hover {
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

            #map-container {
                height: 350px;
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
                <li><a href="delivery.php"><i class="fa fa-home"></i> Ana Sayfa</a></li>
                <li><a href="#" class="active"><i class="fa fa-map"></i> Harita</a></li>
                <li><a href="deliverymyord.php"><i class="fa fa-truck"></i> Siparişlerim</a></li>
                <li><a href="../logout.php"><i class="fa fa-sign-out"></i> Çıkış</a></li>
            </ul>
        </nav>
    </div>

    <!-- Ana İçerik -->
    <div class="main-content">
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="dashboard-title">Kurye Navigasyon Sistemi</div>
            <div class="dashboard-subtitle">Mevcut konumunuz ve sipariş adresleriniz - <?php echo $name; ?></div>
        </div>

        <!-- Harita Bölümü -->
        <div class="map-section">
            <div id="map-container"></div>
            <div class="location-info">
                <div id="current-location"><i class="fa fa-map-marker"></i> Konum bilgisi yükleniyor...</div>
                <div id="selected-order" style="margin-top: 10px; display: none;">
                    <i class="fa fa-truck"></i> <strong>Seçili Sipariş:</strong> <span id="order-info"></span>
                </div>
            </div>
        </div>

        <!-- Siparişler Bölümü -->
        <div class="orders-section">
            <div class="orders-header">
                <i class="fa fa-list"></i> Aktif Siparişleriniz (<?php echo count($orders); ?> adet)
            </div>
            
            <?php if (empty($orders)) { ?>
                <div style="text-align: center; color: #666; padding: 20px;">
                    <i class="fa fa-info-circle"></i> Şu anda aktif siparişiniz bulunmamaktadır.
                </div>
            <?php } else { ?>
                <?php foreach ($orders as $index => $order) { ?>
                    <div class="order-item" onclick="showOrderOnMap('<?php echo htmlspecialchars($order['address']); ?>', '<?php echo htmlspecialchars($order['name']); ?>', '<?php echo $order['Fid']; ?>')">
                        <div class="order-name"><?php echo htmlspecialchars($order['name']); ?></div>
                        <div class="order-details">
                            <strong>Adres:</strong> <?php echo htmlspecialchars($order['address']); ?><br>
                            <strong>Telefon:</strong> <?php echo htmlspecialchars($order['phoneno']); ?><br>
                            <strong>Miktar:</strong> <?php echo htmlspecialchars($order['quantity']); ?> L - 
                            <strong>Tür:</strong> <?php echo htmlspecialchars($order['milk_type'] ?? 'Belirtilmemiş'); ?><br>
                            <strong>Tarih:</strong> <?php echo htmlspecialchars($order['date']); ?>
                        </div>
                        <button class="btn-navigate" onclick="event.stopPropagation(); navigateToOrder('<?php echo htmlspecialchars($order['address']); ?>')">
                            <i class="fa fa-location-arrow"></i> Yol Tarifi Al
                        </button>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <script>
        let map;
        let userMarker;
        let orderMarkers = [];
        let currentPosition = null;

        // Haritayı başlat
        function initMap() {
            navigator.geolocation.getCurrentPosition(function(position) {
                currentPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                // Haritayı oluştur
                map = L.map('map-container').setView(currentPosition, 13);

                // OpenStreetMap tile layer ekle
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
                    maxZoom: 18,
                }).addTo(map);

                // Kullanıcı konumu marker'ı
                userMarker = L.marker(currentPosition, {
                    icon: L.divIcon({
                        className: 'user-marker',
                        html: '<div style="background-color: #253078; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.3);"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    })
                }).addTo(map);
                userMarker.bindPopup("<b><i class='fa fa-user'></i> Mevcut Konumunuz</b>").openPopup();

                // Konum bilgisini güncelle
                updateLocationInfo();

                // Tüm siparişleri haritaya ekle
                addAllOrdersToMap();

            }, function() {
                alert("Konum erişimi reddedildi. Harita düzgün çalışmayabilir.");
            });
        }

        // Konum bilgisini güncelle
        function updateLocationInfo() {
            if (!currentPosition) return;

            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${currentPosition.lat}&lon=${currentPosition.lng}`;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const cityName = data.address.city || data.address.town || data.address.village || 'Bilinmeyen';
                    document.getElementById("current-location").innerHTML = 
                        `<i class="fa fa-map-marker"></i> Mevcut konumunuz: ${cityName} - ${data.display_name}`;
                })
                .catch(error => {
                    document.getElementById("current-location").innerHTML = 
                        `<i class="fa fa-map-marker"></i> Konum: ${currentPosition.lat.toFixed(6)}, ${currentPosition.lng.toFixed(6)}`;
                });
        }

        // Tüm siparişleri haritaya ekle
        function addAllOrdersToMap() {
            <?php foreach ($orders as $order) { ?>
                geocodeAddress('<?php echo htmlspecialchars($order['address']); ?>', '<?php echo htmlspecialchars($order['name']); ?>', <?php echo $order['Fid']; ?>);
            <?php } ?>
        }

        // Adres koordinatlarını bul ve haritaya ekle
        function geocodeAddress(address, customerName, orderId) {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const location = {
                            lat: parseFloat(data[0].lat),
                            lng: parseFloat(data[0].lon)
                        };

                        const marker = L.marker(location, {
                            icon: L.divIcon({
                                className: 'order-marker',
                                html: '<div style="background-color: #6BBE45; width: 15px; height: 15px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.3);"></div>',
                                iconSize: [15, 15],
                                iconAnchor: [7, 7]
                            })
                        }).addTo(map);

                        marker.bindPopup(`<b><i class='fa fa-home'></i> ${customerName}</b><br>${address}`);
                        orderMarkers.push({marker: marker, orderId: orderId, address: address, name: customerName});
                    }
                })
                .catch(error => {
                    console.log('Adres bulunamadı: ' + address);
                });
        }

        // Haritada siparişi göster
        function showOrderOnMap(address, customerName, orderId) {
            // Tüm sipariş item'larının seçili durumunu kaldır
            document.querySelectorAll('.order-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Tıklanan item'ı seçili yap
            event.currentTarget.classList.add('selected');

            // Seçili sipariş bilgisini göster
            document.getElementById('selected-order').style.display = 'block';
            document.getElementById('order-info').textContent = `${customerName} - ${address}`;

            // İlgili marker'ı bul ve popup'ını aç
            const orderMarker = orderMarkers.find(om => om.orderId == orderId);
            if (orderMarker) {
                map.setView(orderMarker.marker.getLatLng(), 16);
                orderMarker.marker.openPopup();
            }
        }

        // Yol tarifi al
        function navigateToOrder(address) {
            if (currentPosition) {
                const url = `https://www.google.com/maps/dir/${currentPosition.lat},${currentPosition.lng}/${encodeURIComponent(address)}`;
                window.open(url, '_blank');
            } else {
                alert('Mevcut konum bilgisi alınamadı!');
            }
        }

        // Sayfa yüklendiğinde haritayı başlat
        window.onload = function() {
            initMap();
        };
    </script>

</body>
</html>