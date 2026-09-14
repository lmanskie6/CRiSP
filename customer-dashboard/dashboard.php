<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Customer';
$user_email = $_SESSION['email'] ?? '';
$user_initial = strtoupper(substr($user_name, 0, 1));

$pdo = getConnection();

$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed, SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT order_id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reviews WHERE user_id = ?");
$stmt->execute([$user_id]);
$has_reviewed = $stmt->fetch()['count'] > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – CRiSP</title>
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
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
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
                <div class="dashboard-welcome">
                    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
                    <p>Here's an overview of your account.</p>
                </div>
                <div class="dashboard-grid">
                    <div class="dashboard-card"><div class="number"><?php echo $stats['total'] ?? 0; ?></div><div class="label">Total Orders</div></div>
                    <div class="dashboard-card"><div class="number"><?php echo $stats['pending'] ?? 0; ?></div><div class="label">Pending Orders</div></div>
                    <div class="dashboard-card"><div class="number"><?php echo $stats['completed'] ?? 0; ?></div><div class="label">Completed Orders</div></div>
                </div>
                <div class="recent-orders-wrapper">
                    <h3>Recent Orders</h3>
                    <?php if (count($recent_orders) > 0): ?>
                        <table>
                            <thead><tr><th>Order #</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align:center; padding:30px 0; font-family:'Poppins',sans-serif; color:var(--gray);">You haven't placed any orders yet. <a href="book.php" style="color:var(--primary-navyblue); font-weight:600;">Book a for a Pickup now!</a></p>
                    <?php endif; ?>
                </div>
                <div class="quick-actions">
                    <a href="book.php" class="btn-action-dash btn-primary-dash">Book for a Pickup</a>
                    <a href="orders.php" class="btn-action-dash btn-primary-dash">View All Orders</a>
                    <?php if (!$has_reviewed): ?>
                        <a href="reviews.php" class="btn-action-dash btn-secondary-dash">Write a Review</a>
                    <?php else: ?>
                        <span class="already-reviewed">You've already left a review. Thank you!</span>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <script src="../js/dashboard.js"></script>
    <script src="../js/logout.js"></script>
</body>
</html>