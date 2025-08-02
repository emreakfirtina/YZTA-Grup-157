<?php
include 'connection.php';
// $connection=mysqli_connect("localhost:3307","root","");
// $db=mysqli_select_db($connection,'demo');

// Köyleri veri tabanından çek
$villages_query = "SELECT koy_id, koy_ad FROM koyler ORDER BY koy_ad";
$villages_result = mysqli_query($connection, $villages_query);

if(isset($_POST['sign']))
{

    $username=$_POST['name'];
    $email=$_POST['email'];
    $password=$_POST['password'];
    $village_id=$_POST['village_id']; // Köy ID'sini al
    
    // Süt tiplerini al
    $milk_types = isset($_POST['milk_type']) ? $_POST['milk_type'] : [];
    $milk_types_str = implode(',', $milk_types); // Diziden virgülle ayrılmış stringe dönüştür
    
    $pass=password_hash($password,PASSWORD_DEFAULT);
    $sql="select * from login where email='$email'" ;
    $result= mysqli_query($connection, $sql);
    $num=mysqli_num_rows($result);
    if($num==1){

        echo "<h1><center>Account already exists</center></h1>";
    }
    else{
    
    $query="insert into login(name,email,password,milk_types,koy_id) values('$username','$email','$pass','$milk_types_str','$village_id')";
    $query_run= mysqli_query($connection, $query);
    if($query_run)
    {
      
       
        header("location:signin.php");
       
    }
    else{
        echo '<script type="text/javascript">alert("data not saved")</script>';
        
    }
}


   
}
?>




<DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="loginstyle.css">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

    
</head>
<body style="background:url('img/arka.jpeg');height:100%;">
<style>
.regform {
        background-color: #fbf8f1;
    }
.btn button {
        background-color: #253078; /* örnek: açık krem */
        color: #ffffff;            /* yazı rengi: koyu mavi */
    } 
.checkbox-group {
        margin: 15px 0;
    }
.checkbox-group label {
        margin-left: 5px;
        margin-right: 15px;
    }
.select-group {
        margin: 15px 0;
    }
.select-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        background-color: white;
    }
.select-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
</style>

    <div class="container">
    <div class="regform">
       
        <form action=" " method="post">
            <p class="logo" style ="font-size: 40px;">Süt<b style="color: #253078;">Net</b></p>
            
            <p id="heading">Hesap Oluştur</p>
            
            <div class="input">
                <label class="textlabel" for="name">İsim Soyisim</label><br>
                
                <input type="text" id="name" name="name" required/>
             </div>
             <div class="input">
                <label class="textlabel" for="email">E-Posta</label>
                <input type="email" id="email" name="email" required/>

                <!-- <label class="textlabel" for="phoneno">Phone no</label>
                <input type="text" id="phoneno" name="phoneno" > -->
         
                <!-- <label class="textlabel" for="password">Password</label>
                <input type="password" id="password" name="password" > -->
            
        
              

             </div>
             <label class="textlabel" for="password">Şifre</label>
             <div class="password">
              
                <input type="password" name="password" id="password" required/>
                <!-- <i class="fa fa-eye-slash" aria-hidden="true" id="showpassword"></i> -->
                <!-- <i class="bi bi-eye-slash" id="showpassword"></i>  -->
                <!-- <i class="uil uil-lock icon"></i> -->
                <i class="uil uil-eye-slash showHidePw" id="showpassword"></i>                
			
             </div>
    
             <div class="checkbox-group">
                <label class="textlabel">Ürettiğiniz Süt Tipi</label><br>
                <input type="checkbox" name="milk_type[]" id="cow" value="cow">
                <label for="cow">İnek Sütü</label>
                
                <input type="checkbox" name="milk_type[]" id="goat" value="goat">
                <label for="goat">Keçi Sütü</label>
                
                <input type="checkbox" name="milk_type[]" id="sheep" value="sheep">
                <label for="sheep">Koyun Sütü</label>
             </div>

             <div class="select-group">
                <label class="textlabel" for="village_id">Köyünüz</label>
                <select name="village_id" id="village_id" required>
                    <option value="">Köyünüzü Seçiniz</option>
                    <?php
                    if(mysqli_num_rows($villages_result) > 0) {
                        while($village = mysqli_fetch_assoc($villages_result)) {
                            echo "<option value='".$village['koy_id']."'>".$village['koy_ad']."</option>";
                        }
                    }
                    ?>
                </select>
             </div>
             
             <div class="btn">
                <button type="submit" name="sign">Hesap Oluştur</button>
             </div>
            
           <!-- <button type="submit" style="background-color:white ;color: #000; margin-top:5px;  padding: 10px 25px;">
                 <img src="google.svg" style="width:22px" >
                 Continue With  Google </button>  -->
                
            <div class="signin-up">
                 <p style="font-size: 20px; text-align: center;">Hesabınız Varsa <a href="signin.php">Giriş Yap</a></p>
             </div>
         

        </form>
        </div>
        <!-- <div class="right">
            <img src="cover.jpg" alt="" width="800" height="700">
        </div> -->
       
    </div>
  

    <!-- <script src="login.js"></script> -->
    <script src="admin/login.js"></script>
       
</body>
</html>