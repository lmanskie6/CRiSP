<?php
session_start();
require_once '../database/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            
            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../customer-dashboard/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – CRiSP</title>
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

        <section class="login-section">
            <div class="container">
                <div class="login-card">
                    <h2 class="login-title">WELCOME BACK!</h2>
                    <p class="login-message">Please log in to continue.</p>
                    
                    <?php if ($error): ?>
                        <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form action="" method="post" class="login-form">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary login-btn">LOG IN</button>
                    </form>
                    <p class="login-footer">
                        Don't have an account? <a href="../signup/signup.php">Sign up here</a>
                    </p>
                    <p class="login-footer">
                        <a href="../index.php">Back to the Home Page</a>
                    </p>
                </div>
            </div>
        </section>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.querySelector('.page-wrapper');
        wrapper.addEventListener('animationend', function() {
            wrapper.style.animation = 'none';
        });
    });
    </script>
</body>
</html>