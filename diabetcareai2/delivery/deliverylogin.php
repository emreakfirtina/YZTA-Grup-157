<?php
session_start();
// $connection = mysqli_connect("localhost:3307", "root", "");
// $db = mysqli_select_db($connection, 'demo');
include '../connection.php'; 
$msg=0;
if (isset($_POST['sign'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $sanitized_emailid =  mysqli_real_escape_string($connection, $email);
  $sanitized_password =  mysqli_real_escape_string($connection, $password);
  // $hash=password_hash($password,PASSWORD_DEFAULT);

  $sql = "select * from delivery_persons where email='$sanitized_emailid'";
  $result = mysqli_query($connection, $sql);
  $num = mysqli_num_rows($result);
 
  if ($num == 1) {
    while ($row = mysqli_fetch_assoc($result)) {
      if (password_verify($sanitized_password, $row['password'])) {
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $row['name'];
        $_SESSION['Did']=$row['Did'];
        $_SESSION['city']=$row['city'];
        header("location:delivery.php");
      } else {
        $msg = 1;
        // echo '<style type="text/css">
        // {
        //     .password input{
                
        //         border:.5px solid red;
                
                
        //       }

        // }
        // </style>';
        // echo "<h1><center> Login Failed incorrect password</center></h1>";
      }
    }
  } else {
    echo "<h3><center>Account does not exist </center></h3>";
  }




  // $query="select * from login where email='$email'and password='$password'";
  // $qname="select name from login where email='$email'and password='$password'";


  // if(mysqli_num_rows($query_run)==1)
  // {
  // //   $_SESSION['name']=$name;

  //   // echo "<h1><center> Login Sucessful  </center></h1>". $name['gender'] ;

  //   $_SESSION['email']=$email;
  //   $_SESSION['name']=$name['name'];
  //   $_SESSION['gender']=$name['gender'];
  //   header("location:home.html");

  // }
  // else{
  //   echo "<h1><center> Login Failed</center></h1>";
  // }
}
?>



<!DOCTYPE html>

<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Login Form</title>
    <link rel="stylesheet" href="deliverycss.css">
  </head>
  <body style="background:url('../img/arka.jpeg');height:100%;">
    <div class="center">
      
      <form method="post" style = "background-color: #fbf8f1;">
        <p class="logo" style ="font-size: 40px; text-align: center;">Süt<b style="color: #253078;">Net</b></p>
        <br>
        
        <p id="heading" style="text-align: center; font-size: 28px;">Kurye Girişi</p>
        <div class="txt_field">
          <input type="email" name="email" required/>
          <span></span>
          <label>E-Posta</label>
        </div>
        <div class="txt_field">
          <input type="password" name="password" required/>
          
          <label>Şifre</label>
          
        </div>
        <?php
        if($msg==1){
                        // echo ' <i class="bx bx-error-circle error-icon"></i>';
                        echo '<p class="error">Password not match.</p>';
                    }
                    ?>
                    <br>
        <!-- <div class="pass">Forgot Password?</div> -->
        <input type="submit" value="Giriş Yap" name="sign" style="background-color: #253078;">
        <div class="signup_link">
          
        </div>
      </form>
    </div>

  </body>
</html>
