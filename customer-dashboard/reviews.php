<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Customer';
$user_initial = strtoupper(substr($user_name, 0, 1));
$error = '';
$success = '';

$pdo = getConnection();

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reviews WHERE user_id = ?");
$stmt->execute([$user_id]);
$already_reviewed = $stmt->fetch()['count'] > 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$already_reviewed) {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating < 1 || $rating > 5) {
        $error = "Please select a rating (1-5 stars).";
    } elseif (empty($comment)) {
        $error = "Please write your review.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, rating, comment, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        if ($stmt->execute([$user_id, $rating, $comment])) {
            $success = "Thank you! Your review has been submitted for approval.";
            $already_reviewed = true;
        } else {
            $error = "Failed to submit review. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write a Review – CRiSP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..800&family=Poppins:wght@400;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/customer.css">
    <style>
        .review-card { background: var(--white); border-radius: 20px; padding: 35px 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); max-width: 600px; margin: 0 auto; }
        .star-rating { display: flex; gap: 10px; font-size: 40px; cursor: pointer; justify-content: center; margin: 15px 0; }
        .star-rating .star { color: #ddd; transition: color 0.2s ease; }
        .star-rating .star.active, .star-rating .star:hover { color: #f5a623; }
        .review-form textarea { width: 100%; padding: 12px 16px; border: 2px solid #ddd; border-radius: 10px; font-family: "Poppins", sans-serif; font-size: 15px; min-height: 120px; resize: vertical; transition: border-color 0.3s ease; }
        .review-form textarea:focus { outline: none; border-color: var(--primary-navyblue); }
        .review-form .btn-submit { width: 100%; padding: 14px; background: var(--primary-navyblue); color: var(--primary-cream); border: none; border-radius: 10px; font-family: "Poppins", sans-serif; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-top: 10px; }
        .review-form .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(27, 60, 107, 0.3); }
        .review-error, .review-success { padding: 10px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; font-family: "Poppins", sans-serif; }
        .review-error { background: #fbe9e7; color: #c62828; }
        .review-success { background: #e8f5e9; color: #2e7d32; }
        .already-reviewed { text-align: center; padding: 40px 0; }
        .already-reviewed .icon-big { font-size: 60px; margin-bottom: 15px; }
        .already-reviewed h3 { font-family: "Poppins", sans-serif; color: var(--primary-navyblue); }
    </style>
</head>
<body>
    <div id="app">
        <header class="header">
            <div class="container">
                <div class="logo"><a href="../index.php"><img src="../assets/images/logo.svg" alt="CRiSP"></a></div>
                <nav class="nav">
                    <ul>
                        <li><a href="../homepage/index.php">HOME</a></li>
                        <li><a href="../homepage/index.php#services">SERVICES</a></li>
                        <li><a href="../homepage/index.php#rates">RATES</a></li>
                        <li><a href="../homepage/index.php#process">PROCESS</a></li>
                        <li><a href="../homepage/index.php#about">ABOUT US</a></li>
                        <li><a href="../homepage/contact.php">CONTACT</a></li>
                    </ul>
                    <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
                </nav>
            </div>
        </header>

        <div class="nav-overlay" id="navOverlay"></div>
        <div class="mobile-menu" id="mobileMenu">
            <div class="user-info">
                <div class="avatar"><?php echo $user_initial; ?></div>
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            </div>
            <ul class="menu-items">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="book.php">Book Now</a></li>
                <li><a href="orders.php">My Bookings</a></li>
                <li><a href="reviews.php">Write a Review</a></li>
                <li><a href="../logout.php" class="logout-link">Logout</a></li>
            </ul>
        </div>

        <section class="dashboard-section">
            <div class="container">
                <h1 style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 28px; color: var(--primary-navyblue); text-align:center; margin-bottom: 20px;">Write a Review</h1>
                <div class="review-card">
                    <?php if ($already_reviewed): ?>
                        <div class="already-reviewed">
                            <h3>You've Already Left a Review!</h3>
                            <p style="font-family: 'Poppins', sans-serif; color: var(--gray);">Thank you for your feedback. We appreciate it!</p>
                            <a href="dashboard.php" style="display:inline-block; margin-top:20px; color: var(--primary-navyblue); font-weight:600; text-decoration:none;">Back to Dashboard</a>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?><div class="review-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                        <?php if ($success): ?><div class="review-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
                        <form action="" method="post" class="review-form">
                            <p style="font-family: 'Poppins', sans-serif; font-weight: 500; text-align:center; margin-bottom:5px;">How would you rate your experience?</p>
                            <div class="star-rating" id="starRating">
                                <span class="star" data-value="1">★</span>
                                <span class="star" data-value="2">★</span>
                                <span class="star" data-value="3">★</span>
                                <span class="star" data-value="4">★</span>
                                <span class="star" data-value="5">★</span>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="0">
                            <div class="form-group" style="margin-top:15px;">
                                <label for="comment" style="font-family: 'Poppins', sans-serif; font-weight:500; display:block; margin-bottom:5px;">Your Review</label>
                                <textarea id="comment" name="comment" placeholder="Share your experience with CRiSP..." required></textarea>
                            </div>
                            <button type="submit" class="btn-submit">SUBMIT REVIEW</button>
                        </form>
                        <div style="margin-top: 15px; text-align:center;"><a href="dashboard.php" style="color: var(--primary-navyblue); text-decoration: none; font-weight: 500;">Back to Dashboard</a></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <script src="../js/dashboard.js"></script>
    <script src="../js/logout.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star-rating .star');
            const ratingInput = document.getElementById('ratingValue');
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = parseInt(this.dataset.value);
                    ratingInput.value = value;
                    stars.forEach(s => {
                        const sv = parseInt(s.dataset.value);
                        s.classList.toggle('active', sv <= value);
                    });
                });
                star.addEventListener('mouseenter', function() {
                    const value = parseInt(this.dataset.value);
                    stars.forEach(s => {
                        s.style.color = (parseInt(s.dataset.value) <= value) ? '#f5a623' : '#ddd';
                    });
                });
                star.addEventListener('mouseleave', function() {
                    const selected = parseInt(ratingInput.value);
                    stars.forEach(s => {
                        s.style.color = (selected > 0 && parseInt(s.dataset.value) <= selected) ? '#f5a623' : '#ddd';
                    });
                });
            });
        });
    </script>
</body>
</html>