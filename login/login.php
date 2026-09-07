<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – CRiSP</title>
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

        <section class="login-section">
            <div class="container">
                <div class="login-card">
                    <h2 class="login-title">LOGIN</h2>
                    <p class="login-message">Please log in to book a pickup.</p>
                    <form action="#" method="post" class="login-form">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
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
                        <a href="../index.php">← Back to Home</a>
                    </p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>