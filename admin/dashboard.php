<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'overview';
$allowed = ['overview', 'customers', 'bookings', 'services', 'rates', 'reviews', 'messages'];
if (!in_array($page, $allowed)) $page = 'overview';

$page_titles = [
    'overview' => 'Dashboard',
    'customers' => 'Customers',
    'bookings' => 'Bookings',
    'services' => 'Services',
    'rates' => 'Rates',
    'reviews' => 'Reviews',
    'messages' => 'Contact Messages'
];
$user_name = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – CRiSP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..800&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div id="app">
        <header class="admin-header">
            <div class="admin-header-container">
                <div class="admin-logo"><a href="dashboard.php"><img src="../assets/images/logo.svg" alt="CRiSP" class="admin-logo-icon"></a></div>
                <div class="admin-user">
                    <span class="admin-user-name">Welcome, <b><?php echo htmlspecialchars($user_name); ?></b></span>
                    <a href="../logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
        </header>
        <div class="admin-body">
            <aside class="admin-sidebar">
                <nav class="admin-nav">
                    <ul>
                        <li><a href="dashboard.php?page=overview" class="<?php echo $page === 'overview' ? 'active' : ''; ?>">Dashboard</a></li>
                        <li><a href="dashboard.php?page=customers" class="<?php echo $page === 'customers' ? 'active' : ''; ?>">Customers</a></li>
                        <li><a href="dashboard.php?page=bookings" class="<?php echo $page === 'bookings' ? 'active' : ''; ?>">Bookings</a></li>
                        <li><a href="dashboard.php?page=services" class="<?php echo $page === 'services' ? 'active' : ''; ?>">Services</a></li>
                        <li><a href="dashboard.php?page=rates" class="<?php echo $page === 'rates' ? 'active' : ''; ?>">Rates</a></li>
                        <li><a href="dashboard.php?page=reviews" class="<?php echo $page === 'reviews' ? 'active' : ''; ?>">Reviews</a></li>
                        <li><a href="dashboard.php?page=messages" class="<?php echo $page === 'messages' ? 'active' : ''; ?>">Messages</a></li>
                    </ul>
                </nav>
            </aside>
            <main class="admin-content">
                <div class="admin-content-header"><h1><?php echo $page_titles[$page]; ?></h1></div>
                <div class="admin-content-body"><?php include $page . '.php'; ?></div>
            </main>
        </div>
    </div>

<!-- Delete modal -->
<div class="delete-modal" id="deleteModal">
    <div class="delete-modal-content">
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete <span class="item-name" id="deleteItemName"></span>?</p>
        <div class="delete-modal-actions">
            <button class="btn-delete-confirm" id="confirmDeleteBtn">Yes, Delete</button>
            <button class="btn-delete-cancel" id="cancelDeleteBtn">Cancel</button>
        </div>
    </div>
</div>

<!-- Booking details modal -->
<div class="booking-modal" id="bookingModal">
    <div class="booking-modal-content">
        <span class="booking-modal-close" id="bookingModalClose">&times;</span>
        <h2>Booking Details</h2>
        <div class="booking-details">
            <div class="detail-row"><strong>Booking ID:</strong> <span id="bId"></span></div>
            <div class="detail-row"><strong>Customer:</strong> <span id="bCustomer"></span></div>
            <div class="detail-row"><strong>Service:</strong> <span id="bService"></span></div>
            <div class="detail-row"><strong>Quantity:</strong> <span id="bQty"></span></div>
            <div class="detail-row"><strong>Total Amount:</strong> <span id="bTotal"></span></div>
            <div class="detail-row"><strong>Pickup Date:</strong> <span id="bPickupDate"></span></div>
            <div class="detail-row"><strong>Pickup Time:</strong> <span id="bPickupTime"></span></div>
            <div class="detail-row"><strong>Status:</strong> <span id="bStatus"></span></div>
            <div class="detail-row"><strong>Payment Method:</strong> <span id="bPayment"></span></div>
            <div class="detail-row" id="bPaymentDetailsRow" style="display:none; flex-direction:column; align-items:flex-start; gap:5px;">
                <strong>Payment Details:</strong>
                <div id="bPaymentDetails" style="padding:10px 14px; background:var(--gray-light); border-radius:8px; width:100%; font-size:14px; line-height:1.6;"></div>
            </div>
            <div class="detail-row"><strong>Notes:</strong> <span id="bNotes"></span></div>
            <div class="detail-row"><strong>Created At:</strong> <span id="bCreated"></span></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingModal = document.getElementById('bookingModal');
    const closeBtn = document.getElementById('bookingModalClose');

    // Modal handlers
    window.openBookingModal = function(data) {
        document.getElementById('bId').textContent = data.id;
        document.getElementById('bCustomer').textContent = data.customer;
        document.getElementById('bService').textContent = data.service;
        document.getElementById('bQty').textContent = data.qty;
        document.getElementById('bTotal').textContent = '₱' + data.total;
        document.getElementById('bPickupDate').textContent = data.pickup;
        document.getElementById('bPickupTime').textContent = data.time;
        document.getElementById('bStatus').textContent = data.status;
        document.getElementById('bNotes').textContent = data.notes;
        document.getElementById('bCreated').textContent = data.created;

        // Payment details
        document.getElementById('bPayment').textContent = data.payment || 'N/A';

        const detailsRow  = document.getElementById('bPaymentDetailsRow');
        const detailsBox  = document.getElementById('bPaymentDetails');
        detailsBox.innerHTML = '';
        detailsRow.style.display = 'none';

        if (data.paymentDetails && data.paymentDetails.trim() !== '') {
            try {
                const d = JSON.parse(data.paymentDetails);
                let html = '';

                if (data.payment === 'GCash') {
                    html  = '<div><strong>Account Name:</strong> ' + escapeHtml(d.name || '') + '</div>';
                    html += '<div><strong>GCash Number:</strong> ' + escapeHtml(d.number || '') + '</div>';
                } else if (data.payment === 'Maya') {
                    html  = '<div><strong>Account Name:</strong> ' + escapeHtml(d.name || '') + '</div>';
                    html += '<div><strong>Maya Number:</strong> ' + escapeHtml(d.number || '') + '</div>';
                } else if (data.payment === 'Card (Debit/Credit)') {
                    html  = '<div><strong>Cardholder:</strong> ' + escapeHtml(d.cardholder || '') + '</div>';
                    html += '<div><strong>Card:</strong> ' + escapeHtml(d.brand || 'Card') + ' •••• ' + escapeHtml(d.last4 || '') + '</div>';
                    html += '<div><strong>Expiry:</strong> ' + escapeHtml(d.expiry || '') + '</div>';
                } else {
                    // Fallback display
                    Object.keys(d).forEach(k => {
                        html += '<div><strong>' + escapeHtml(k) + ':</strong> ' + escapeHtml(String(d[k])) + '</div>';
                    });
                }

                if (html !== '') {
                    detailsBox.innerHTML = html;
                    detailsRow.style.display = 'flex';
                }
            } catch (e) {
                console.warn('Could not parse payment_details JSON:', e);
            }
        }

        bookingModal.classList.add('active');
    };

    // HTML escape helper
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Modal close events
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            bookingModal.classList.remove('active');
        });
    }

    bookingModal.addEventListener('click', function(e) {
        if (e.target === this) {
            bookingModal.classList.remove('active');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && bookingModal.classList.contains('active')) {
            bookingModal.classList.remove('active');
        }
    });

    // Button handlers
    document.querySelectorAll('.view-booking').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = {
                id: this.dataset.id,
                customer: this.dataset.customer,
                service: this.dataset.service,
                qty: this.dataset.qty,
                total: this.dataset.total,
                pickup: this.dataset.pickup,
                time: this.dataset.time,
                status: this.dataset.status,
                payment: this.dataset.payment,
                paymentDetails: this.dataset.paymentDetails,
                notes: this.dataset.notes,
                created: this.dataset.created
            };
            openBookingModal(data);
        });
    });
});
</script>
<script src="../js/logout.js"></script>
</body>
</html>