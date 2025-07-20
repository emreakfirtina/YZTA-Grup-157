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

  $sql = "select * from admin where email='$sanitized_emailid'";
  $result = mysqli_query($connection, $sql);
  $num = mysqli_num_rows($result);
 
  if ($num == 1) {
    while ($row = mysqli_fetch_assoc($result)) {
      if (password_verify($sanitized_password, $row['password'])) {
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $row['name'];
        $_SESSION['location'] = $row['location'];
        $_SESSION['Aid']=$row['Aid'];
        header("location:admin.php");
      } else {
        $msg = 1;
      }
    }
  } else {
    echo "<h1><center>Account does not exists </center></h1>";
  }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SütNet - Admin Girişi</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        color: #ffffff;
        overflow-x: hidden;
        line-height: 1.6;
        min-height: 100vh;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 20% 50%, rgba(37, 48, 120, 0.2) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255, 107, 107, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    .floating-elements {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .floating-element {
        position: absolute;
        color: rgba(37, 48, 120, 0.1);
        font-size: 2rem;
        animation: float 8s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-30px) rotate(180deg); }
    }

    .floating-element:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; color: rgba(37, 48, 120, 0.08); }
    .floating-element:nth-child(2) { top: 60%; left: 85%; animation-delay: 2s; color: rgba(255, 107, 107, 0.08); }
    .floating-element:nth-child(3) { top: 40%; left: 5%; animation-delay: 4s; color: rgba(78, 205, 196, 0.08); }
    .floating-element:nth-child(4) { top: 80%; left: 75%; animation-delay: 1s; color: rgba(249, 202, 36, 0.08); }

    .container {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 450px;
        margin: 0 auto;
        padding: 2rem;
    }

    form {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 3rem 2.5rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        position: relative;
        overflow: hidden;
    }

    form::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(37, 48, 120, 0.1) 0%, transparent 50%, rgba(255, 107, 107, 0.1) 100%);
        opacity: 0.5;
        pointer-events: none;
    }

    .logo {
        font-size: 2.5rem;
        text-align: center;
        font-weight: 700;
        color: #253078;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }

    #heading {
        text-align: center;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 2.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #253078 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        z-index: 1;
    }

    .input-group, .password {
        margin-bottom: 1.5rem;
        position: relative;
        z-index: 1;
    }

    .textlabel, label {
        display: block;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .input-group input, .password input {
        width: 100%;
        padding: 1.2rem 1.5rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        color: #ffffff;
        font-size: 1rem;
        outline: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .input-group input::placeholder, .password input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .input-group input:focus, .password input:focus {
        border-color: #253078;
        box-shadow: 0 0 20px rgba(37, 48, 120, 0.3);
        background: rgba(255, 255, 255, 0.15);
    }

    .password {
        position: relative;
    }

    .password .uil {
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.6);
        cursor: pointer;
        font-size: 1.2rem;
        z-index: 2;
        transition: color 0.3s ease;
        margin-top: 0.75rem;
    }

    .password .uil:hover {
        color: #253078;
    }

    .error {
        color: #ff6b6b;
        font-size: 0.9rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .error-icon, .bx {
        font-size: 1rem;
    }

    button[type="submit"] {
        width: 100%;
        padding: 1.2rem;
        background: linear-gradient(135deg, #253078 0%, #1a2356 100%);
        border: none;
        border-radius: 16px;
        color: #ffffff;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        overflow: hidden;
        margin-top: 1.5rem;
        z-index: 1;
    }

    button[type="submit"]::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(255, 255, 255, 0.1) 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    button[type="submit"]:hover::before {
        opacity: 1;
    }

    button[type="submit"]:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(37, 48, 120, 0.4);
    }

    .login-signup {
        text-align: center;
        position: relative;
        z-index: 1;
        margin-top: 1.5rem;
    }

    .text {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.95rem;
    }

    .text a {
        color: #253078;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .text a:hover {
        color: #ffffff;
        text-shadow: 0 0 10px rgba(37, 48, 120, 0.5);
    }

    @media only screen and (max-width: 768px) {
        .container {
            padding: 1rem;
        }
        
        form {
            padding: 2rem 1.5rem;
        }
        
        .logo {
            font-size: 2rem;
        }
        
        #heading {
            font-size: 1.3rem;
        }
        
        .input-group input, .password input {
            padding: 1rem 1.2rem;
        }
    }

    .fade-in {
        opacity: 0;
        animation: fadeInUp 1s ease-out forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<body>
    <div class="floating-elements">
        <div class="floating-element"><i class="fas fa-cow"></i></div>
        <div class="floating-element"><i class="fas fa-user-shield"></i></div>
        <div class="floating-element"><i class="fas fa-chart-line"></i></div>
        <div class="floating-element"><i class="fas fa-database"></i></div>
    </div>

    <div class="container fade-in">
        <form action="" id="form" method="post">
            <p class="logo">
                <i class="fas fa-cow"></i>
                Diabet<b style="color: #253078;">Care AI</b>
            </p>
            <p id="heading">
                <i class="fas fa-user-shield"></i>
                Doktor Girişi
            </p>
            
            <div class="input-group">
                <label for="email">E-Posta</label>
                <input type="text" id="email" name="email" placeholder="E-posta adresinizi giriniz">
                <div class="error"></div>
            </div>

            <label class="textlabel" for="password">Şifre</label>
            <div class="password">
                <input type="password" name="password" id="password" placeholder="Şifrenizi giriniz" required/>
                <i class="uil uil-eye-slash showHidePw" id="showpassword"></i>                
                <?php
                    if($msg==1){
                        echo '<div class="error">';
                        echo '<i class="fas fa-exclamation-circle error-icon"></i>';
                        echo '<span>Şifre eşleşmiyor.</span>';
                        echo '</div>';
                    }
                ?> 
            </div>
      
            <button type="submit" name="sign">
                <i class="fas fa-sign-in-alt"></i>
                Giriş Yap
            </button>
            
            <div class="login-signup">
                <span class="text">
                    
                </span>
            </div>
        </form>
    </div>

    <script>
        // Password show/hide functionality
        const showHidePw = document.querySelector('.showHidePw');
        const passwordInput = document.getElementById('password');

        showHidePw.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showHidePw.classList.remove('uil-eye-slash');
                showHidePw.classList.add('uil-eye');
            } else {
                passwordInput.type = 'password';
                showHidePw.classList.remove('uil-eye');
                showHidePw.classList.add('uil-eye-slash');
            }
        });

        // Animation on load
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('.container');
            container.style.animationDelay = '0.2s';
        });
    </script>
</body>
</html>