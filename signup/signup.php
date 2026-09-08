<?php
require_once '../validation.php';  
require_once '../database/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $pdo = getConnection();
        
        // Check if username exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $existing_username = $stmt->fetch();
        
        if ($existing_username) {
            $error = "Username already taken. Please choose another.";
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existing_email = $stmt->fetch();
            
            if ($existing_email) {
                $error = "Email already registered. Please use another email.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, name, email, password_hash, role, created_at) 
                    VALUES (?, ?, ?, ?, 'customer', NOW())
                ");
                
                if ($stmt->execute([$username, $name, $email, $hashed_password])) {
                    $success = "Registration successful! You can now log in.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up – CRiSP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..800&family=Poppins:wght@400;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div id="app" class="page-wrapper">
        <header class="header">
            <div class="container">
                <div class="logo">
                    <a href="../index.php">
                        <img src="../images/logo.svg" alt="CRiSP">
                    </a>
                </div>
                <nav class="nav">
                    <ul>
                        <li><a href="../index.php#home">HOME</a></li>
                        <li><a href="../index.php#services">SERVICES</a></li>
                        <li><a href="../index.php#rates">RATES</a></li>
                        <li><a href="../index.php#process">PROCESS</a></li>
                        <li><a href="../index.php#about">ABOUT US</a></li>
                        <li><a href="../index.php#footer">CONTACT</a></li>
                    </ul>
                    <a href="../login/login.php" class="btn btn-outline">BOOK A PICKUP</a>
                </nav>
            </div>
        </header>

        <section class="login-section signup-section">
            <div class="container">
                <div class="login-card signup-card">
                    <h2 class="login-title">NEW TO CRiSP?</h2>
                    <p class="login-message">Create an account to start booking.</p>
                    
                    <?php if ($error): ?>
                        <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="login-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form action="" method="post" class="login-form">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary login-btn">SIGN UP</button>
                    </form>
                    <p class="login-footer">
                        Already have an account? <a href="../login/login.php">Log in here</a>
                    </p>
                    <p class="login-footer">
                        <a href="../index.php">Back to the Home Page</a>
                    </p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>