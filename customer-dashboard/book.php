<?php
session_start();
require_once '../database/config.php';
require_once '../validation.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Customer';
$user_email = $_SESSION['email'] ?? '';
$user_initial = strtoupper(substr($user_name, 0, 1));
$error = '';
$success = '';
$show_receipt = false;
$receipt_order = null;

$pdo = getConnection();

// Services list
$stmt = $pdo->query("
    SELECT s.service_id, s.service_name, s.description, 
           r.rate_id, r.price_per_unit, r.rate_type
    FROM services s
    LEFT JOIN rates r ON r.service_id = s.service_id AND r.is_active = 1
    WHERE s.is_active = 1
    ORDER BY s.service_id
");
$services = $stmt->fetchAll();

// Payment options
$payment_methods = [
    'Cash on Delivery'    => 'Cash on Delivery',
    'GCash'               => 'GCash',
    'Maya'                => 'Maya',
    'Card (Debit/Credit)' => 'Card (Debit/Credit)'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id      = (int)($_POST['service_id'] ?? 0);
    $quantity        = (int)($_POST['quantity'] ?? 1);
    $pickup_date     = trim($_POST['pickup_date'] ?? '');
    $pickup_time     = trim($_POST['pickup_time'] ?? '');
    $pickup_location = trim($_POST['pickup_location'] ?? '');
    $notes           = trim($_POST['notes'] ?? '');
    $payment_method  = trim($_POST['payment_method'] ?? 'Cash on Delivery');

    $rules = [
        'service_id' => [
            function($v) { return validateNumeric($v, 1, PHP_INT_MAX, 'Service'); }
        ],
        'quantity' => [
            function($v) { return validateNumeric($v, 1, 100, 'Quantity'); }
        ],
        'pickup_date' => [
            function($v) { return validateRequired($v, 'Pickup date'); },
            function($v) { return validateDate($v); }
        ],
        'pickup_location' => [
            function($v) { return validateRequired($v, 'Pickup location'); }
        ],
        'payment_method' => [
            function($v) use ($payment_methods) {
                return array_key_exists($v, $payment_methods) ? null : 'Please select a valid payment method.';
            }
        ]
    ];

    $data = [
        'service_id'      => $service_id,
        'quantity'        => $quantity,
        'pickup_date'     => $pickup_date,
        'pickup_location' => $pickup_location,
        'payment_method'  => $payment_method
    ];
    $error = validate($data, $rules);

    // Payment validation
    $payment_details = [];

    if (!$error && $payment_method === 'GCash') {
        $gcash_name   = trim($_POST['gcash_name']   ?? '');
        $gcash_number = trim($_POST['gcash_number'] ?? '');

        if ($gcash_name === '') {
            $error = "GCash account name is required.";
        } elseif (!preg_match('/^(09|\+639)\d{9}$/', $gcash_number)) {
            $error = "Please enter a valid GCash mobile number (e.g. 09171234567).";
        } else {
            $payment_details['name']   = $gcash_name;
            $payment_details['number'] = $gcash_number;
        }
    }

    if (!$error && $payment_method === 'Maya') {
        $maya_name   = trim($_POST['maya_name']   ?? '');
        $maya_number = trim($_POST['maya_number'] ?? '');

        if ($maya_name === '') {
            $error = "Maya account name is required.";
        } elseif (!preg_match('/^(09|\+639)\d{9}$/', $maya_number)) {
            $error = "Please enter a valid Maya mobile number (e.g. 09171234567).";
        } else {
            $payment_details['name']   = $maya_name;
            $payment_details['number'] = $maya_number;
        }
    }

    if (!$error && $payment_method === 'Card (Debit/Credit)') {
        $card_name   = trim($_POST['card_name']   ?? '');
        $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
        $card_expiry = trim($_POST['card_expiry'] ?? '');
        $card_cvv    = trim($_POST['card_cvv']    ?? '');

        if ($card_name === '') {
            $error = "Cardholder name is required.";
        } elseif (!preg_match('/^\d{13,19}$/', $card_number)) {
            $error = "Card number must be 13–19 digits.";
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $card_expiry)) {
            $error = "Expiry must be in MM/YY format.";
        } else {
            [$mm, $yy] = explode('/', $card_expiry);
            $expiry_ts = mktime(0, 0, 0, (int)$mm + 1, 0, 2000 + (int)$yy);
            if ($expiry_ts < time()) {
                $error = "This card has already expired.";
            } elseif (!preg_match('/^\d{3,4}$/', $card_cvv)) {
                $error = "CVV must be 3 or 4 digits.";
            }
        }

        if (!$error) {
            $payment_details['cardholder'] = $card_name;
            $payment_details['last4']      = substr($card_number, -4);
            $payment_details['brand']      = detectCardBrand($card_number);
            $payment_details['expiry']     = $card_expiry;
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("
            SELECT s.service_id, s.service_name, 
                   r.rate_id, r.price_per_unit
            FROM services s
            LEFT JOIN rates r ON r.service_id = s.service_id AND r.is_active = 1
            WHERE s.service_id = ?
        ");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();

        if ($service) {
            $price = $service['price_per_unit'] ?? 45.00;
            $total = $price * $quantity;
            $payment_details_json = empty($payment_details) ? null : json_encode($payment_details);

            $stmt = $pdo->prepare("
                INSERT INTO orders 
                    (user_id, service_id, rate_id, service_name, quantity, price_per_unit, total_amount, status, pickup_date, pickup_time, pickup_location, notes, payment_method, payment_details, created_at) 
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, NOW())
            ");
            if ($stmt->execute([
                $user_id, 
                $service['service_id'], 
                $service['rate_id'], 
                $service['service_name'], 
                $quantity, 
                $price, 
                $total, 
                $pickup_date, 
                $pickup_time, 
                $pickup_location,
                $notes,
                $payment_method,
                $payment_details_json
            ])) {
                // Receipt setup
                $new_order_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("
                    SELECT o.*, 
                           u.name AS customer_name, u.email AS customer_email, u.username
                    FROM orders o
                    JOIN users u ON o.user_id = u.user_id
                    WHERE o.order_id = ? AND o.user_id = ?
                ");
                $stmt->execute([$new_order_id, $user_id]);
                $receipt_order = $stmt->fetch();
                $show_receipt = true;
            } else {
                $error = "Failed to create booking. Please try again.";
            }
        } else {
            $error = "Invalid service selected.";
        }
    }
}

// Card brand detection
function detectCardBrand(string $number): string {
    if (preg_match('/^4/', $number))          return 'Visa';
    if (preg_match('/^5[1-5]|^2[2-7]/', $number)) return 'Mastercard';
    if (preg_match('/^3[47]/', $number))      return 'Amex';
    if (preg_match('/^6(?:011|5)/', $number)) return 'Discover';
    if (preg_match('/^35/', $number))         return 'JCB';
    return 'Card';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Pickup – CRiSP</title>
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

        <section class="dashboard-section book-page">
            <div class="container">
                <div class="book-form-wrapper">
                    <h1>Book a Pickup</h1>
                    <p class="subtitle">Fill out the form below to schedule your pickup.</p>
                    <?php if ($error): ?><div class="book-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                    <form action="" method="post" class="book-form">
                        <div class="form-group">
                            <label for="service_id">Select Service</label>
                            <select name="service_id" id="service_id" required>
                                <option value="">Select a service</option>
                                <?php foreach ($services as $service): 
                                    $price = $service['price_per_unit'] ?? 0;
                                ?>
                                    <option value="<?php echo $service['service_id']; ?>" data-price="<?php echo $price; ?>">
                                        <?php echo htmlspecialchars($service['service_name']); ?> 
                                        (₱<?php echo number_format($price, 2); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity (number of items)</label>
                            <input type="number" name="quantity" id="quantity" min="1" value="1" required>
                        </div>

                        <!-- PICKUP LOCATION -->
                        <div class="form-group">
                            <label for="pickup_location">Pickup Location</label>
                            <input type="text" name="pickup_location" id="pickup_location"
                                placeholder="House/Unit No., Street, Barangay, City"
                                value="<?php echo htmlspecialchars($_POST['pickup_location'] ?? ''); ?>"
                                required maxlength="255">
                            <small class="field-hint">Where should we pick up your garments?</small>
                        </div>

                        <div class="form-group">
                            <label for="pickup_date">Pickup Date</label>
                            <input type="date" name="pickup_date" id="pickup_date" required>
                        </div>
                        <div class="form-group">
                            <label for="pickup_time">Preferred Time</label>
                            <input type="time" name="pickup_time" id="pickup_time">
                        </div>

                        <!-- PAYMENT METHOD -->
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" required>
                                <?php foreach ($payment_methods as $key => $label): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>"
                                        <?php 
                                        $selected = (isset($_POST['payment_method']) && $_POST['payment_method'] === $key) 
                                                    || (!isset($_POST['payment_method']) && $key === 'Cash on Delivery');
                                        echo $selected ? 'selected' : ''; 
                                        ?>>
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small id="paymentNote" class="payment-note"></small>
                        </div>

                        <!-- GCash -->
                        <div class="payment-form" id="gcash-form" style="display:none;">
                            <div class="payment-form-header">GCash Details</div>
                            <div class="form-group">
                                <label for="gcash_name">Account Name *</label>
                                <input type="text" name="gcash_name" id="gcash_name" placeholder="Juan Dela Cruz"
                                    value="<?php echo htmlspecialchars($_POST['gcash_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="gcash_number">GCash Mobile Number *</label>
                                <input type="tel" name="gcash_number" id="gcash_number" placeholder="09171234567" maxlength="13"
                                    value="<?php echo htmlspecialchars($_POST['gcash_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Maya -->
                        <div class="payment-form" id="maya-form" style="display:none;">
                            <div class="payment-form-header">Maya Details</div>
                            <div class="form-group">
                                <label for="maya_name">Account Name *</label>
                                <input type="text" name="maya_name" id="maya_name" placeholder="Juan Dela Cruz"
                                    value="<?php echo htmlspecialchars($_POST['maya_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="maya_number">Maya Mobile Number *</label>
                                <input type="tel" name="maya_number" id="maya_number" placeholder="09171234567" maxlength="13"
                                    value="<?php echo htmlspecialchars($_POST['maya_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Card -->
                        <div class="payment-form" id="card-form" style="display:none;">
                            <div class="payment-form-header">
                                <span class="payment-logo">💳</span> Card Details
                            </div>
                            <div class="form-group">
                                <label for="card_name">Cardholder Name *</label>
                                <input type="text" name="card_name" id="card_name" placeholder="JUAN DELA CRUZ"
                                    value="<?php echo htmlspecialchars($_POST['card_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="card_number">Card Number *</label>
                                <input type="text" name="card_number" id="card_number" placeholder="1234 5678 9012 3456"
                                    maxlength="23" inputmode="numeric" autocomplete="cc-number"
                                    value="<?php echo htmlspecialchars($_POST['card_number'] ?? ''); ?>">
                            </div>
                            <div class="card-row">
                                <div class="form-group">
                                    <label for="card_expiry">Expiry (MM/YY) *</label>
                                    <input type="text" name="card_expiry" id="card_expiry" placeholder="MM/YY"
                                        maxlength="5" inputmode="numeric" autocomplete="cc-exp"
                                        value="<?php echo htmlspecialchars($_POST['card_expiry'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="card_cvv">CVV *</label>
                                    <input type="password" name="card_cvv" id="card_cvv" placeholder="123"
                                        maxlength="4" inputmode="numeric" autocomplete="cc-csc">
                                </div>
                            </div>
                            <p class="payment-disclaimer">
                                * Only the last 4 digits of your card are stored. CVV is never saved.
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="notes">Special Instructions</label>
                            <textarea name="notes" id="notes" placeholder="Any special requests or instructions..."></textarea>
                        </div>
                        <div class="form-group">
                            <div class="price-display" id="priceDisplay">Total Price: ₱0.00</div>
                        </div>
                        <button type="submit" class="btn-submit">SUBMIT BOOKING</button>
                    </form>
                    <div style="margin-top: 15px; text-align:center;"><a href="dashboard.php" style="color: var(--primary-navyblue); text-decoration: none; font-weight: 500;">Back to Dashboard</a></div>
                </div>
            </div>
        </section>

    <?php if ($show_receipt && $receipt_order): ?>
        <?php
            $payment_details = [];
            if (!empty($receipt_order['payment_details'])) {
                $decoded = json_decode($receipt_order['payment_details'], true);
                if (is_array($decoded)) $payment_details = $decoded;
            }
            $receipt_no = 'CR-' . date('Ymd', strtotime($receipt_order['created_at'])) 
                        . '-' . str_pad($receipt_order['order_id'], 4, '0', STR_PAD_LEFT);
        ?>

        <div class="receipt-overlay active" id="receiptOverlay">
            <div class="receipt-paper" id="receiptPaper">

                <!-- HEADER -->
                <div class="receipt-header">
                    <div class="receipt-header-left">
                        <img src="../assets/logo.svg" alt="CRiSP" class="receipt-logo">
                        <p class="receipt-brand-sub">Steam n' Press</p>
                    </div>
                    <div class="receipt-header-right">
                        <h1 class="receipt-title">OFFICIAL RECEIPT</h1>
                        <p class="receipt-no">#<?php echo htmlspecialchars($receipt_no); ?></p>
                        <p class="receipt-issued">Issued: <?php echo date('M d, Y · g:i A', strtotime($receipt_order['created_at'])); ?></p>
                    </div>
                </div>

                <!-- STATUS -->
                <div class="receipt-status-banner receipt-status-<?php echo htmlspecialchars($receipt_order['status']); ?>">
                    STATUS: <?php echo strtoupper(htmlspecialchars($receipt_order['status'])); ?>
                </div>

                <!-- INFO GRID -->
                <div class="receipt-info-grid">
                    <div class="receipt-info-block">
                        <h3>Billed To</h3>
                        <p><strong><?php echo htmlspecialchars($receipt_order['customer_name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($receipt_order['customer_email']); ?></p>
                        <p>@<?php echo htmlspecialchars($receipt_order['username']); ?></p>
                    </div>
                    <div class="receipt-info-block">
                        <h3>Pickup Details</h3>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($receipt_order['pickup_location'] ?? '—'); ?></p>
                        <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($receipt_order['pickup_date'])); ?></p>
                        <p><strong>Time:</strong> <?php echo $receipt_order['pickup_time'] ? date('g:i A', strtotime($receipt_order['pickup_time'])) : 'Any time'; ?></p>
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
                            <td><?php echo htmlspecialchars($receipt_order['service_name']); ?></td>
                            <td class="receipt-col-center"><?php echo (int)$receipt_order['quantity']; ?></td>
                            <td class="receipt-col-right">₱<?php echo number_format($receipt_order['price_per_unit'], 2); ?></td>
                            <td class="receipt-col-right">₱<?php echo number_format($receipt_order['total_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="receipt-total-row">
                            <td colspan="3" class="receipt-col-right"><strong>TOTAL</strong></td>
                            <td class="receipt-col-right"><strong>₱<?php echo number_format($receipt_order['total_amount'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- PAYMENT -->
                <div class="receipt-payment">
                    <h3>Payment Method</h3>
                    <p><strong><?php echo htmlspecialchars($receipt_order['payment_method'] ?? 'Cash on Delivery'); ?></strong></p>

                    <?php if (!empty($payment_details)): ?>
                        <?php if ($receipt_order['payment_method'] === 'GCash'): ?>
                            <p><strong>Account Name:</strong> <?php echo htmlspecialchars($payment_details['name'] ?? ''); ?></p>
                            <p><strong>GCash Number:</strong> <?php echo htmlspecialchars($payment_details['number'] ?? ''); ?></p>
                        <?php elseif ($receipt_order['payment_method'] === 'Maya'): ?>
                            <p><strong>Account Name:</strong> <?php echo htmlspecialchars($payment_details['name'] ?? ''); ?></p>
                            <p><strong>Maya Number:</strong> <?php echo htmlspecialchars($payment_details['number'] ?? ''); ?></p>
                        <?php elseif ($receipt_order['payment_method'] === 'Card (Debit/Credit)'): ?>
                            <p><strong>Cardholder:</strong> <?php echo htmlspecialchars($payment_details['cardholder'] ?? ''); ?></p>
                            <p><strong>Card:</strong> <?php echo htmlspecialchars($payment_details['brand'] ?? 'Card'); ?> •••• <?php echo htmlspecialchars($payment_details['last4'] ?? ''); ?></p>
                            <p><strong>Expiry:</strong> <?php echo htmlspecialchars($payment_details['expiry'] ?? ''); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($receipt_order['notes'])): ?>
                    <div class="receipt-notes">
                        <h3>Special Instructions</h3>
                        <p><?php echo nl2br(htmlspecialchars($receipt_order['notes'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- FOOTER -->
                <div class="receipt-footer">
    <div class="receipt-thank-you">
        <p><strong>Thank you for choosing CRiSP!</strong></p>
        <p>Your pickup is now queued.</p>
    </div>
</div>

                <p class="receipt-disclaimer">
                    This is a system-generated receipt. No signature required.
                </p>

                <!-- ACTION BUTTONS -->
                <div class="receipt-actions-row">
                    <button type="button" class="receipt-btn receipt-btn-secondary" id="closeReceiptBtn">Close</button>
                    <a href="orders.php" class="receipt-btn receipt-btn-primary">View My Bookings</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="../js/dashboard.js"></script>
    <script src="../js/logout.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ---------- Receipt overlay ---------- */
            const overlay = document.getElementById('receiptOverlay');
            if (overlay) {
                const closeBtn = document.getElementById('closeReceiptBtn');
                const paper    = document.getElementById('receiptPaper');

                function closeReceipt() {
                    overlay.classList.remove('active');
                }

                if (closeBtn) closeBtn.addEventListener('click', closeReceipt);

                overlay.addEventListener('click', function (e) {
                    if (e.target === overlay) closeReceipt();
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && overlay.classList.contains('active')) {
                        closeReceipt();
                    }
                });

                if (paper) {
                    paper.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });
                }
            }

            /* ---------- Estimated price preview ---------- */
            const serviceSelect = document.getElementById('service_id');
            const quantityInput = document.getElementById('quantity');
            const priceDisplay  = document.getElementById('priceDisplay');

            function updatePrice() {
                const selected = serviceSelect.options[serviceSelect.selectedIndex];
                const price    = parseFloat(selected.getAttribute('data-price')) || 0;
                const quantity = parseInt(quantityInput.value) || 1;
                priceDisplay.textContent = 'Total Price: ₱' + (price * quantity).toFixed(2);
            }
            if (serviceSelect && quantityInput && priceDisplay) {
                serviceSelect.addEventListener('change', updatePrice);
                quantityInput.addEventListener('input', updatePrice);
                updatePrice();
            }

            /* ---------- Payment method toggle ---------- */
            const paymentSelect = document.getElementById('payment_method');
            const paymentNote   = document.getElementById('paymentNote');
            const gcashForm     = document.getElementById('gcash-form');
            const mayaForm      = document.getElementById('maya-form');
            const cardForm      = document.getElementById('card-form');

            function setRequired(container, required) {
                container.querySelectorAll('input, select, textarea').forEach(el => {
                    if (required) el.setAttribute('required', 'required');
                    else          el.removeAttribute('required');
                });
            }

            const notes = {
                'Cash on Delivery':    'Please prepare the exact amount upon delivery.',
                'GCash':               'Enter the GCash account you will pay from.',
                'Maya':                'Enter the Maya account you will pay from.',
                'Card (Debit/Credit)': 'Your card will be charged after booking confirmation.'
            };

            function updatePaymentUI() {
                const method = paymentSelect.value;

                gcashForm.style.display = 'none';
                mayaForm.style.display  = 'none';
                cardForm.style.display  = 'none';

                setRequired(gcashForm, false);
                setRequired(mayaForm, false);
                setRequired(cardForm, false);

                if (method === 'GCash') {
                    gcashForm.style.display = 'block';
                    setRequired(gcashForm, true);
                } else if (method === 'Maya') {
                    mayaForm.style.display = 'block';
                    setRequired(mayaForm, true);
                } else if (method === 'Card (Debit/Credit)') {
                    cardForm.style.display = 'block';
                    setRequired(cardForm, true);
                }

                paymentNote.textContent = notes[method] || '';
            }
            if (paymentSelect) {
                paymentSelect.addEventListener('change', updatePaymentUI);
                updatePaymentUI();
            }

            /* ---------- Card input auto-formatting ---------- */
            const cardNumber = document.getElementById('card_number');
            const cardExpiry = document.getElementById('card_expiry');

            if (cardNumber) {
                cardNumber.addEventListener('input', function () {
                    let v = this.value.replace(/\D/g, '').slice(0, 19);
                    this.value = v.replace(/(.{4})/g, '$1 ').trim();
                });
            }
            if (cardExpiry) {
                cardExpiry.addEventListener('input', function () {
                    let v = this.value.replace(/\D/g, '').slice(0, 4);
                    if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
                    this.value = v;
                });
            }
        });
    </script>
</body>
</html>