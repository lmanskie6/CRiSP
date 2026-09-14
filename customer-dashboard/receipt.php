<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Customer';
$user_email = $_SESSION['email'] ?? '';
$user_initial = strtoupper(substr($user_name, 0, 1));

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    header("Location: orders.php");
    exit();
}

$pdo = getConnection();

$stmt = $pdo->prepare("
    SELECT o.*, 
           u.name AS customer_name, u.email AS customer_email, u.username
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

$payment_details = [];
if (!empty($order['payment_details'])) {
    $decoded = json_decode($order['payment_details'], true);
    if (is_array($decoded)) $payment_details = $decoded;
}

$receipt_no = 'CR-' . date('Ymd', strtotime($order['created_at'])) . '-' . str_pad($order['order_id'], 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo htmlspecialchars($receipt_no); ?> – CRiSP</title>
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

        <section class="receipt-section">
            <div class="container">
                <div class="receipt-actions">
                    <a href="orders.php" class="receipt-back-link">← Back to My Bookings</a>
                </div>

                <div class="receipt-paper">

                    <!-- HEADER -->
                    <div class="receipt-header">
                        <div class="receipt-header-left">
                            <img src="../assets/logo.svg" alt="CRiSP" class="receipt-logo">
                            <p class="receipt-brand-sub">Steam n' Press</p>
                        </div>
                        <div class="receipt-header-right">
                            <h1 class="receipt-title">OFFICIAL RECEIPT</h1>
                            <p class="receipt-no">#<?php echo htmlspecialchars($receipt_no); ?></p>
                            <p class="receipt-issued">Issued: <?php echo date('M d, Y · g:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                    </div>

                    <!-- STATUS -->
                    <div class="receipt-status-banner receipt-status-<?php echo htmlspecialchars($order['status']); ?>">
                        STATUS: <?php echo strtoupper(htmlspecialchars($order['status'])); ?>
                    </div>

                    <!-- INFO GRID -->
                    <div class="receipt-info-grid">
                        <div class="receipt-info-block">
                            <h3>Billed To</h3>
                            <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order['customer_email']); ?></p>
                            <p>@<?php echo htmlspecialchars($order['username']); ?></p>
                        </div>
                        <div class="receipt-info-block">
                            <h3>Pickup Details</h3>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($order['pickup_location'] ?? '—'); ?></p>
                            <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($order['pickup_date'])); ?></p>
                            <p><strong>Time:</strong> <?php echo $order['pickup_time'] ? date('g:i A', strtotime($order['pickup_time'])) : 'Any time'; ?></p>
                        </div>
                    </div>

                    <!-- LINE ITEMS -->
                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th class="receipt-col-center">Qty</th>
                                <th class="receipt-col-right">Unit Price</th>
                                <th class="receipt-col-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                <td class="receipt-col-center"><?php echo (int)$order['quantity']; ?></td>
                                <td class="receipt-col-right">₱<?php echo number_format($order['price_per_unit'], 2); ?></td>
                                <td class="receipt-col-right">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="receipt-total-row">
                                <td colspan="3" class="receipt-col-right"><strong>TOTAL</strong></td>
                                <td class="receipt-col-right"><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- PAYMENT -->
                    <div class="receipt-payment">
                        <h3>Payment Method</h3>
                        <p><strong><?php echo htmlspecialchars($order['payment_method'] ?? 'Cash on Delivery'); ?></strong></p>

                        <?php if (!empty($payment_details)): ?>
                            <?php if ($order['payment_method'] === 'GCash'): ?>
                                <p><strong>Account Name:</strong> <?php echo htmlspecialchars($payment_details['name'] ?? ''); ?></p>
                                <p><strong>GCash Number:</strong> <?php echo htmlspecialchars($payment_details['number'] ?? ''); ?></p>
                            <?php elseif ($order['payment_method'] === 'Maya'): ?>
                                <p><strong>Account Name:</strong> <?php echo htmlspecialchars($payment_details['name'] ?? ''); ?></p>
                                <p><strong>Maya Number:</strong> <?php echo htmlspecialchars($payment_details['number'] ?? ''); ?></p>
                            <?php elseif ($order['payment_method'] === 'Card (Debit/Credit)'): ?>
                                <p><strong>Cardholder:</strong> <?php echo htmlspecialchars($payment_details['cardholder'] ?? ''); ?></p>
                                <p><strong>Card:</strong> <?php echo htmlspecialchars($payment_details['brand'] ?? 'Card'); ?> •••• <?php echo htmlspecialchars($payment_details['last4'] ?? ''); ?></p>
                                <p><strong>Expiry:</strong> <?php echo htmlspecialchars($payment_details['expiry'] ?? ''); ?></p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($order['notes'])): ?>
                        <div class="receipt-notes">
                            <h3>Special Instructions</h3>
                            <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- FOOTER -->
                    <div class="receipt-footer">
                    <div class="receipt-thank-you">
                        <p><strong>Thank you for choosing CRiSP!</strong></p>
                        <p>We'll contact you shortly to confirm your pickup schedule.</p>
                    </div>
                </div>

                    <p class="receipt-disclaimer">
                        This is a system-generated receipt. No signature required.
                    </p>
                </div>
            </div>
        </section>

    <script src="../js/dashboard.js"></script>
    <script src="../js/logout.js"></script>
</body>
</html>