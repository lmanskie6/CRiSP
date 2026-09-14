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

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT order_id, service_name, quantity, total_amount, status, pickup_date, pickup_time, pickup_location, notes, payment_method, created_at FROM orders WHERE user_id = ? ORDER BY user_id ASC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings – CRiSP</title>
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
                <h1 style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 28px; color: var(--primary-navyblue); margin-bottom: 20px;">My Bookings</h1>
                <?php if (count($orders) > 0): ?>
                    <div style="background: var(--white); border-radius: 20px; padding: 25px 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); overflow-x:auto;">
                        <table style="width:100%; border-collapse: collapse;">
                            <thead><tr style="border-bottom:2px solid var(--primary-navyblue);">
                                <th style="text-align:left; padding:12px; font-size:13px; text-transform:uppercase; color:var(--gray);">Order #</th>
                                <th style="text-align:left; padding:12px; font-size:13px; text-transform:uppercase; color:var(--gray);">Service</th>
                                <th style="text-align:left; padding:12px; font-size:13px; text-transform:uppercase; color:var(--gray);">Qty</th>
                                <th style="text-align:left; padding:12px; font-size:13px; text-transform:uppercase; color:var(--gray);">Total</th>
                                <th style="text-align:left; padding:12px; font-size:13px; text-transform:uppercase; color:var(--gray);">Pickup</th>
                                <th style="text-align:left; padding:12px; font-size:13px; text-transform:uppercase; color:var(--gray);">Status</th>
                                <th style="text-align:left; padding:12px; font-size:13px; text-transform:uppercase; color:var(--gray);">Date</th>
                                <th style="text-align:left; padding:12px; font-size:13px; text-transform:uppercase; color:var(--gray);">Payment</th>
                                <th style="text-align:left; padding:12px; font-size:13px; text-transform:uppercase; color:var(--gray);">Receipt</th>
                            </tr></thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr style="border-bottom:1px solid #eee;">
                                        <td style="padding:12px; font-weight:600;">#<?php echo $order['order_id']; ?></td>
                                        <td style="padding:12px;"><?php echo htmlspecialchars($order['service_name']); ?></td>
                                        <td style="padding:12px;"><?php echo $order['quantity']; ?></td>
                                        <td style="padding:12px;">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td style="padding:12px;"><?php echo date('M d, Y', strtotime($order['pickup_date'])); ?></td>
                                        <td style="padding:12px;"><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                        <td style="padding:12px;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td style="padding:12px;"><?php echo htmlspecialchars($order['payment_method'] ?? 'Cash on Delivery'); ?></td>
                                        <td style="padding:12px;">
                                            <a href="receipt.php?order_id=<?php echo $order['order_id']; ?>"
                                               style="display:inline-block; padding:6px 16px; background:var(--primary-navyblue); color:#fff; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none; font-family:'Poppins',sans-serif; white-space:nowrap; transition:all 0.25s ease;"
                                               onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(27,60,107,0.3)';"
                                               onmouseout="this.style.transform=''; this.style.boxShadow='';">
                                                View Receipt
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="background: var(--white); border-radius: 20px; padding: 60px 30px; text-align:center; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                        <p style="font-family: 'Poppins', sans-serif; color: var(--gray);">You haven't placed any orders yet. <a href="book.php" style="color:var(--primary-navyblue); font-weight:600;">Book for a Pickup now!</a></p>
                    </div>
                <?php endif; ?>
                <div style="margin-top: 20px;"><a href="dashboard.php" style="color: var(--primary-navyblue); text-decoration: none; font-weight: 500;">Back to Dashboard</a></div>
            </div>
        </section>
    <script src="../js/dashboard.js"></script>
    <script src="../js/logout.js"></script>
</body>
</html>