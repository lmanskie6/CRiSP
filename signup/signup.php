<?php
session_start();
require_once '../database/config.php';
require_once '../validation.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../homepage/index.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validation
    $rules = [
        'username' => [
            function($v) { return validateRequired($v, 'Username'); }
        ],
        'name' => [
            function($v) { return validateRequired($v, 'Name'); }
        ],
        'email' => [
            function($v) { return validateRequired($v, 'Email'); },
            function($v) { return validateEmail($v); }
        ],
        'password' => [
            function($v) { return validateRequired($v, 'Password'); }
        ],
        'confirm' => [
            function($v) use ($password) { return validatePasswordMatch($password, $v); }
        ]
    ];

    $data = ['username' => $username, 'name' => $name, 'email' => $email, 'password' => $password, 'confirm' => $confirm];
    $error = validate($data, $rules);

    if (!$error) {
        // User validation
        $error = checkUniqueUser($username, $email);
        if (!$error) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo = getConnection();
            $stmt = $pdo->prepare("INSERT INTO users (username, name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, 'customer', NOW())");
            if ($stmt->execute([$username, $name, $email, $hashed])) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Registration failed. Please try again.";
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
    <div id="app">
        <header class="header">
            <div class="container">
                <div class="logo"><a href="../homepage/index.php"><img src="../assets/logo.svg" alt="CRiSP"></a></div>
                <nav class="nav">
                    <ul>
                        <li><a href="../homepage/index.php#home">HOME</a></li>
                        <li><a href="../homepage/index.php#services">SERVICES</a></li>
                        <li><a href="../homepage/index.php#rates">RATES</a></li>
                        <li><a href="../homepage/index.php#process">PROCESS</a></li>
                        <li><a href="../homepage/index.php#about">ABOUT US</a></li>
                        <li><a href="../homepage/contact.php">CONTACT</a></li>
                    </ul>
                    <a href="../login/login.php" class="btn btn-outline">BOOK A PICKUP</a>
                    <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
                </nav>
            </div>
        </header>

        <section class="login-section signup-section">
            <div class="container">
                <div class="login-card signup-card">
                    <h2 class="login-title">NEW TO CRiSP?</h2>
                    <p class="login-message">Create an account to start booking.</p>
                    <?php if ($error): ?><div class="login-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="login-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
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
                    <p class="login-footer">Already have an account? <a href="../login/login.php">Log in here</a></p>
                    <p class="login-footer"><a href="../homepage/index.php">Back to the Home Page</a></p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>