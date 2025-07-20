<?php
include("login.php"); 
if($_SESSION['name']==''){
	header("location: signin.php");
}
// include("login.php"); 
$emailid= $_SESSION['email'];
$connection=mysqli_connect("localhost","root","");
$db=mysqli_select_db($connection,'demo');
if(isset($_POST['submit']))
{
    $milk_type=mysqli_real_escape_string($connection, $_POST['milk_type']);
    $quantity=mysqli_real_escape_string($connection, $_POST['quantity']);
    $phoneno=mysqli_real_escape_string($connection, $_POST['phoneno']);
    $district=mysqli_real_escape_string($connection, $_POST['district']);
    $address=mysqli_real_escape_string($connection, $_POST['address']);
    $name=mysqli_real_escape_string($connection, $_POST['name']);
    $donation_type=mysqli_real_escape_string($connection, $_POST['donation_type']);
    $pickup_date = null;
    
    if($donation_type == "scheduled"){
        $pickup_date=mysqli_real_escape_string($connection, $_POST['pickup_date']);
    }

    // food_donations tablosuna veri ekleme
    $query="INSERT INTO food_donations(email,phoneno,location,address,name,milk_type,quantity,donation_type,pickup_date) VALUES('$emailid','$phoneno','$district','$address','$name','$milk_type','$quantity','$donation_type','$pickup_date')";
    $query_run= mysqli_query($connection, $query);
    
    if($query_run)
    {
        // Son eklenen kaydın Fid'sini al
        $last_fid = mysqli_insert_id($connection);
        
        // food_login tablosuna ilişki verisi ekleme
        $relation_query = "INSERT INTO food_login(email, Fid) VALUES('$emailid', '$last_fid')";
        $relation_query_run = mysqli_query($connection, $relation_query);
        
        if($relation_query_run)
        {
            echo '<script type="text/javascript">alert("Süt teslim bildirimi başarıyla kaydedildi!")</script>';
            header("location:delivery.html");
        }
        else
        {
            echo '<script type="text/javascript">alert("İlişki tablosuna kayıt eklenirken hata oluştu!")</script>';
        }
    }
    else{
        echo '<script type="text/javascript">alert("Veri kaydedilemedi!")</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Süt Teslim Bildirimi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: url('img/arka.jpeg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .regformf {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo {
            font-size: 32px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .logo b {
            color: #253078;
        }

        .input {
            margin-bottom: 20px;
        }

        .input label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .input input,
        .input select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            color: #333;
            background-color: white;
            transition: border-color 0.3s ease;
        }

        .input input:focus,
        .input select:focus {
            outline: none;
            border-color: #4DA3FF;
            box-shadow: 0 0 0 3px rgba(77, 163, 255, 0.1);
        }

        .input input:required:valid {
            border-color: #28a745;
        }

        .radio {
            margin-bottom: 20px;
        }

        .radio label {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: block;
        }

        .radio input[type="radio"] {
            margin-right: 8px;
            transform: scale(1.2);
        }

        .radio input[type="radio"] + label {
            font-weight: 500;
            cursor: pointer;
            display: inline;
            margin-bottom: 0;
        }

        .pickup-date {
            display: none;
            margin-top: 15px;
        }

        .pickup-date.show {
            display: block;
        }

        .contact-header {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 30px 0 20px 0;
            padding: 15px 0;
            border-top: 2px solid #f0f0f0;
            border-bottom: 2px solid #f0f0f0;
        }

        .input-group {
            display: flex;
            gap: 15px;
        }

        .input-group > div {
            flex: 1;
        }

        .address-group {
            display: flex;
            gap: 15px;
            align-items: end;
        }

        .address-group .village-select {
            flex: 1;
        }

        .address-group .address-input {
            flex: 2;
        }

        .btn {
            margin-top: 30px;
            text-align: center;
        }

        .btn button {
            background: linear-gradient(135deg, #4DA3FF 0%, #0984e3 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .btn button:hover {
            background: linear-gradient(135deg, #3d8bfd 0%, #0770d1 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(77, 163, 255, 0.3);
        }

        .btn button:active {
            transform: translateY(0);
        }

        /* Alert messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }

        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .regformf {
                padding: 30px 20px;
            }

            .input-group {
                flex-direction: column;
                gap: 0;
            }

            .address-group {
                flex-direction: column;
                gap: 0;
            }

            .logo {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }

            .regformf {
                padding: 25px 15px;
            }

            .logo {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="regformf">
            <form action="" method="post">
                <p class="logo">Süt<b>Net</b></p>
                
                <div class="input">
                    <label for="milk_type">Süt Tipi:</label>
                    <select id="milk_type" name="milk_type" required>
                        <option value="">Süt Tipi Seçiniz</option>
                        <option value="cow">İnek Sütü</option>
                        <option value="goat">Keçi Sütü</option>
                        <option value="sheep">Koyun Sütü</option>
                    </select>
                </div>
                
                <div class="input">
                    <label for="quantity">Süt Miktarı (Litre)</label>
                    <input type="number" id="quantity" name="quantity" min="1" step="0.1" placeholder="Örn: 25.5" required/>
                </div>
                
                <div class="radio">
                    <label>Teslim Alınacak Zaman:</label>
                    <br>
                    <input type="radio" name="donation_type" id="now" value="now" checked onchange="showHidePickupDate()"/>
                    <label for="now" style="padding-right: 40px;">Şimdi</label>
                    <input type="radio" name="donation_type" id="scheduled" value="scheduled" onchange="showHidePickupDate()">
                    <label for="scheduled">İleri Tarihli</label>
                </div>
                
                <div id="pickup_date_div" class="pickup-date input">
                    <label for="pickup_date">Alınacak Tarih ve Saat:</label>
                    <input type="datetime-local" id="pickup_date" name="pickup_date">
                </div>
               
                <div class="contact-header">İletişim Bilgileri</div>
                
                <div class="input-group">
                    <div>
                        <label for="name">İsim Soyisim:</label>
                        <input type="text" id="name" name="name" value="<?php echo $_SESSION['name'] ?? ''; ?>" required/>
                    </div>
                    <div>
                        <label for="phoneno">Telefon Numarası:</label>
                        <input type="tel" id="phoneno" name="phoneno" maxlength="11" pattern="[0-9]{10,11}" placeholder="05XXXXXXXXX" required/>
                    </div>
                </div>
                
                <div class="address-group">
                    <div class="village-select">
                        <label for="district">Köy:</label>
                        <select id="district" name="district" required>
                            <option value="">Köy Seçiniz</option>
                            <option value="armutlu">Armutlu</option>
                            <option value="kozaklali">Kozalaklı</option>
                            <option value="mesutlu">Mesutlu</option>
                            <option value="karahayit">Karahayıt</option>
                            <option value="derekoy">Dereköy</option>
                            <option value="dalama">Dalama</option>
                            <option value="sahnali">Şahnalı</option>
                            <option value="golhisar">Gölhisar</option>
                            <option value="baltakoy">Baltaköy</option>
                            <option value="tepekoy">Tepeköy</option>
                            <option value="ciftlikoy">Çiftliköy</option>
                        </select>
                    </div>
                    <div class="address-input">
                        <label for="address">Detaylı Adres:</label>
                        <input type="text" id="address" name="address" placeholder="Mahalle, sokak, kapı no vb." required/>
                    </div>
                </div>
                
                <div class="btn">
                    <button type="submit" name="submit">Teslim Bildirimi Gönder</button>
                </div>
            </form>
        </div>
    </div>
   
    <script>
        function showHidePickupDate() {
            var donationType = document.querySelector('input[name="donation_type"]:checked').value;
            var pickupDateDiv = document.getElementById('pickup_date_div');
            
            if (donationType === 'scheduled') {
                pickupDateDiv.style.display = 'block';
                pickupDateDiv.classList.add('show');
                document.getElementById('pickup_date').required = true;
            } else {
                pickupDateDiv.style.display = 'none';
                pickupDateDiv.classList.remove('show');
                document.getElementById('pickup_date').required = false;
            }
        }
        
        // Sayfa yüklendiğinde çalıştır
        document.addEventListener('DOMContentLoaded', function() {
            showHidePickupDate();
            
            // Telefon numarası formatlaması
            document.getElementById('phoneno').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) {
                    value = value.slice(0, 11);
                }
                e.target.value = value;
            });
        });
    </script>
</body>
</html>