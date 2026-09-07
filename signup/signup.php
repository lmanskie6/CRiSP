<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up – CRiSP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..800&family=Poppins:wght@400;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div id="app">
        <header class="header">
            <div class="container">
                <div class="logo">
                    <a href="../index.php">
                        <img src="../images/logo.svg" alt="CRiSP">
                    </a>
                </div>
            </div>
        </header>

        <section class="login-section signup-section">
            <div class="container">
                <div class="login-card signup-card">
                    <h2 class="login-title">SIGN UP</h2>
                    <p class="login-message">Create your account to start booking.</p>
                    <form action="#" method="post" class="login-form">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
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
                        <a href="../index.php">← Back to Home</a>
                    </p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>