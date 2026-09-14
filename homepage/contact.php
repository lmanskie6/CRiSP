<?php
session_start();
require_once '../database/config.php';
require_once '../validation.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'admin') {
    header("Location: ../admin/dashboard.php");
    exit();
}

$pdo = getConnection();
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['email'] ?? '';
$user_initial = !empty($user_name) ? strtoupper(substr($user_name, 0, 1)) : 'G';
$is_logged_in = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $rules = [
        'name' => [function($v) { return validateRequired($v, 'Name'); }],
        'email' => [
            function($v) { return validateRequired($v, 'Email'); },
            function($v) { return validateEmail($v); }
        ],
        'subject' => [function($v) { return validateRequired($v, 'Subject'); }],
        'message' => [function($v) { return validateRequired($v, 'Message'); }]
    ];

    $data = ['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message];
    $error = validate($data, $rules);

    if (!$error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, 'unread', NOW())");
            $stmt->execute([$name, $email, $subject, $message]);

            $success = "Thank you! Your message has been sent successfully. We'll get back to you soon.";
        } catch (PDOException $e) {
            $error = "Failed to send your message. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us – CRiSP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..800&family=Poppins:wght@400;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/customer.css">
</head>
<body>
    <div id="app">
        <header class="header">
            <div class="container">
                <div class="logo"><a href="index.php"><img src="../assets/images/logo.svg" alt="CRiSP"></a></div>
                <nav class="nav">
                    <ul>
                        <li><a href="index.php">HOME</a></li>
                        <li><a href="index.php#services">SERVICES</a></li>
                        <li><a href="index.php#rates">RATES</a></li>
                        <li><a href="index.php#process">PROCESS</a></li>
                        <li><a href="index.php#about">ABOUT US</a></li>
                        <li><a href="contact.php" class="active">CONTACT</a></li>
                    </ul>
                    <div class="hamburger" id="hamburger" style="display:flex !important;"><span></span><span></span><span></span></div>
                </nav>
            </div>
        </header>

        <div class="nav-overlay" id="navOverlay"></div>
        <div class="mobile-menu" id="mobileMenu">
            <div class="user-info">
                <div class="avatar"><?php echo $user_initial; ?></div>
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
            </div>
            <ul class="menu-items">
                <li><a href="../customer-dashboard/dashboard.php">Dashboard</a></li>
                <li><a href="../customer-dashboard/book.php">Book Now</a></li>
                <li><a href="../customer-dashboard/orders.php">My Orders</a></li>
                <li><a href="../customer-dashboard/reviews.php">Write a Review</a></li>
                <li><a href="../logout.php" class="logout-link">Logout</a></li>
            </ul>
        </div>

        <section class="contact-section">
            <div class="container">
                <div class="contact-form-wrapper">
                    <h2>Send us a Message!</h2>
                    <p class="subtitle">We'd love to hear from you. We'll get back to you as soon as possible.</p>

                    <?php if ($error): ?><div class="contact-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="contact-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

                    <form action="" method="post" class="contact-form">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($user_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user_email); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Send Message</button>
                    </form>
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-column footer-column-left">
                        <img src="../assets/logo white.svg" alt="CRiSP" class="footer-logo-icon">
                        <div class="footer-contact-cluster">
                            <p class="footer-contact-item"><img src="../assets/icons/mail.svg" alt="Email" class="footer-icon"> crisp.snp@gmail.com</p>
                            <p class="footer-contact-item"><img src="../assets/icons/phone.svg" alt="Phone" class="footer-icon"> +63 993 270 8291</p>
                            <p class="footer-contact-item"><img src="../assets/icons/socials.svg" alt="Social" class="footer-social"> CRiSP Official</p>
                        </div>
                    </div>
                    <div class="footer-quick-links">
                        <h3 class="footer-heading">QUICK LINKS</h3>
                        <ul class="footer-links">
                            <li><a href="index.php#rates">RATES</a></li>
                            <li><a href="index.php#about">ABOUT US</a></li>
                            <li><a href="index.php#process">PROCESS</a></li>
                            <li><a href="contact.php">CONTACT</a></li>
                        </ul>
                    </div>
                    <div class="footer-services">
                        <h3 class="footer-heading">SERVICES</h3>
                        <ul class="footer-links">
                            <li><a href="index.php#services">DAILY WEAR</a></li>
                            <li><a href="index.php#services">FORMAL WEAR</a></li>
                            <li><a href="index.php#services">DELICATES</a></li>
                            <li><a href="index.php#services">HOUSEHOLD</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="../js/dashboard.js"></script>
    <script src="../js/logout.js"></script>
</body>
</html>