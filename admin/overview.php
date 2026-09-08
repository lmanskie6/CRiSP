<?php
$pdo = getConnection();

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$total_users = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$total_bookings = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$pending_bookings = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as count FROM reviews");
$total_reviews = $stmt->fetch()['count'] ?? 0;

$stmt = $pdo->query("SELECT order_id, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
$recent_bookings = $stmt->fetchAll();

// Recent reviews
$stmt = $pdo->query("
    SELECT r.rating, r.comment, r.created_at, u.full_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$recent_reviews = $stmt->fetchAll();
?>

<!-- Status Card -->
<div class="admin-stats">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($total_users); ?></h3>
            <p>Total Customers</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($total_bookings); ?></h3>
            <p>Total Bookings</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($pending_bookings); ?></h3>
            <p>Pending Bookings</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>₱<?php echo number_format($total_revenue, 2); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($total_reviews); ?></h3>
            <p>Total Reviews</p>
        </div>
    </div>
</div>

<!-- Recent -->
<div class="admin-grid-2">
    <div class="admin-table-wrapper">
        <div class="table-header">
            <h2>Recent Bookings</h2>
            <a href="dashboard.php?page=bookings" class="btn-action" style="font-weight:600;">View All →</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_bookings as $booking): ?>
                    <tr>
                        <td>#<?php echo $booking['order_id']; ?></td>
                        <td>₱<?php echo number_format($booking['total_amount'], 2); ?></td>
                        <td><span class="badge badge-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-table-wrapper">
        <div class="table-header">
            <h2>Recent Reviews</h2>
            <a href="dashboard.php?page=reviews" class="btn-action" style="font-weight:600;">View All →</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_reviews as $review): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($review['full_name']); ?></td>
                        <td>
                            <span style="color:#f5a623;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                                <?php endfor; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars(substr($review['comment'], 0, 40)) . (strlen($review['comment']) > 40 ? '...' : ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>