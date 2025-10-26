<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$message = "";

// Load current credentials from file or use defaults
$credentials_file = "admin_credentials.json";
$default_credentials = [
    'username' => 'admin',
    'password' => 'admin123'
];

// Load credentials
if (file_exists($credentials_file)) {
    $credentials = json_decode(file_get_contents($credentials_file), true) ?: $default_credentials;
} else {
    $credentials = $default_credentials;
    file_put_contents($credentials_file, json_encode($credentials, JSON_PRETTY_PRINT));
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username === $credentials['username'] && $password === $credentials['password']) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Emri i përdoruesit ose fjalëkalimi është i gabuar.";
    }
}

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $email = $_POST['email'];
    $admin_email = 'heike-morina@gmx.net';
    
    if ($email === $admin_email) {
        // Generate reset code
        $reset_code = sprintf('%06d', mt_rand(100000, 999999));
        
        // Store reset code in session with expiration
        $_SESSION['reset_code'] = $reset_code;
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_expires'] = time() + (15 * 60); // 15 minutes
        
        // Send email
    $subject = "Kodi i Rivendosjes së Kredencialeve - Morina Baustoffe";
        $email_message = "Kodi juaj i rivendosjes së kredencialeve është: " . $reset_code . "\n\n";
        $email_message .= "Ky kod skadon pas 15 minutash.\n\n";
        $email_message .= "Me këtë kod mund të ndryshoni si emrin e përdoruesit ashtu dhe fjalëkalimin.\n\n";
        $email_message .= "Nëse nuk keni kërkuar rivendosjen e kredencialeve, injoroni këtë email.";
        
    $headers = "From: noreply@morina-baustoffe.com\r\n";
    $headers .= "Reply-To: noreply@morina-baustoffe.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        if (mail($email, $subject, $email_message, $headers)) {
            $message = "Kodi i rivendosjes u dërgua në email-in tuaj.";
            $_SESSION['show_verify_form'] = true;
        } else {
            $error = "Gabim në dërgimin e email-it. Provoni përsëri.";
        }
    } else {
        $error = "Email-i nuk është i regjistruar.";
    }
}

// Handle code verification and credentials reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_reset'])) {
    $submitted_code = $_POST['reset_code'];
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if reset session exists and is valid
    if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_expires'])) {
        $error = "Sesioni i rivendosjes ka skaduar. Provoni përsëri.";
    } elseif (time() > $_SESSION['reset_expires']) {
        $error = "Kodi i rivendosjes ka skaduar. Provoni përsëri.";
        unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
    } elseif ($submitted_code !== $_SESSION['reset_code']) {
        $error = "Kodi i rivendosjes është i gabuar.";
    } elseif (empty($new_username) || strlen($new_username) < 3) {
        $error = "Emri i përdoruesit duhet të jetë të paktën 3 karaktere.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Fjalëkalimet nuk përputhen.";
    } elseif (strlen($new_password) < 6) {
        $error = "Fjalëkalimi duhet të jetë të paktën 6 karaktere.";
    } else {
        // Update credentials
        $new_credentials = [
            'username' => $new_username,
            'password' => $new_password
        ];
        
        if (file_put_contents($credentials_file, json_encode($new_credentials, JSON_PRETTY_PRINT))) {
            $message = "Kredencialet u ndryshuan me sukses. Mund të hyni tani me kredencialet e reja.";
            unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires'], $_SESSION['show_verify_form']);
        } else {
            $error = "Gabim në ruajtjen e kredencialeve. Provoni përsëri.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hyrje - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 400px;
            max-width: 90%;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #fadbd8;
            border-radius: 5px;
        }
        
        .success {
            color: #27ae60;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #d5f4e6;
            border-radius: 5px;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .form-toggle {
            text-align: center;
            margin-top: 20px;
        }
        
        .form-toggle a {
            color: #667eea;
            text-decoration: none;
            cursor: pointer;
        }
        
        .form-toggle a:hover {
            text-decoration: underline;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Login Form -->
        <div id="login-form" <?php echo (isset($_SESSION['show_verify_form']) ? 'class="hidden"' : ''); ?>>
            <div class="login-header">
                <h1><i class="fas fa-user-shield"></i> Hyrje Admin</h1>
                <p>Hyni në panelin administrativ</p>
            </div>
            
            <?php if ($error && !isset($_SESSION['show_verify_form'])): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($message && !isset($_SESSION['show_verify_form'])): ?>
                <div class="success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Emri i Përdoruesit:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Fjalëkalimi:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Hyni
                </button>
            </form>
            
            <div class="form-toggle">
                <a onclick="showResetForm()">Keni harruar kredencialet?</a>
            </div>
        </div>
        
        <!-- Password Reset Form -->
        <div id="reset-form" class="hidden">
            <div class="login-header">
                <h1><i class="fas fa-key"></i> Rivendosni Kredencialet</h1>
                <p>Vendosni email-in tuaj për të marrë kodin</p>
            </div>
            
            <?php if ($error && !isset($_SESSION['show_verify_form'])): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($message && !isset($_SESSION['show_verify_form'])): ?>
                <div class="success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="heike-morina@gmx.net" required>
                </div>
                
                <button type="submit" name="reset_password" class="btn">
                    <i class="fas fa-envelope"></i> Dërgo Kodin
                </button>
            </form>
            
            <div class="form-toggle">
                <a onclick="showLoginForm()">Kthehuni në hyrje</a>
            </div>
        </div>
        
        <!-- Verification Form -->
        <div id="verify-form" <?php echo (isset($_SESSION['show_verify_form']) ? '' : 'class="hidden"'); ?>>
            <div class="login-header">
                <h1><i class="fas fa-shield-alt"></i> Verifikoni Kodin</h1>
                <p>Vendosni kodin dhe kredencialet e reja</p>
            </div>
            
            <?php if ($error && isset($_SESSION['show_verify_form'])): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($message && isset($_SESSION['show_verify_form'])): ?>
                <div class="success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="reset_code">Kodi i Rivendosjes:</label>
                    <input type="text" id="reset_code" name="reset_code" maxlength="6" required>
                </div>
                
                <div class="form-group">
                    <label for="new_username">Emri i Ri i Përdoruesit:</label>
                    <input type="text" id="new_username" name="new_username" required 
                           placeholder="Vendosni emrin e ri të përdoruesit">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Fjalëkalimi i Ri:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmoni Fjalëkalimin:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" name="verify_reset" class="btn">
                    <i class="fas fa-check"></i> Rivendosni Kredencialet
                </button>
            </form>
            
            <div class="form-toggle">
                <a onclick="showLoginForm()">Kthehuni në hyrje</a>
            </div>
        </div>
    </div>
    
    <script>
        function showResetForm() {
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('reset-form').classList.remove('hidden');
            document.getElementById('verify-form').classList.add('hidden');
        }
        
        function showLoginForm() {
            document.getElementById('login-form').classList.remove('hidden');
            document.getElementById('reset-form').classList.add('hidden');
            document.getElementById('verify-form').classList.add('hidden');
        }
        
        function showVerifyForm() {
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('reset-form').classList.add('hidden');
            document.getElementById('verify-form').classList.remove('hidden');
        }
    </script>
</body>
</html>
