<?php
$pdo = getConnection();

$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch()['count'],
    'total_bookings' => $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'],
    'pending_bookings' => $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch()['count'],
    'total_revenue' => $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch()['total'] ?? 0,
    'total_reviews' => $pdo->query("SELECT COUNT(*) as count FROM reviews")->fetch()['count'] ?? 0
];

$recent_bookings = $pdo->query("SELECT order_id, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_reviews = $pdo->query("SELECT r.rating, r.comment, r.created_at, u.name FROM reviews r JOIN users u ON r.user_id = u.user_id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
?>
<div class="admin-stats">
    <div class="stat-card"><div class="stat-info"><h3><?php echo number_format($stats['total_users']); ?></h3><p>Total Customers</p></div></div>
    <div class="stat-card"><div class="stat-info"><h3><?php echo number_format($stats['total_bookings']); ?></h3><p>Total Bookings</p></div></div>
    <div class="stat-card"><div class="stat-info"><h3><?php echo number_format($stats['pending_bookings']); ?></h3><p>Pending Bookings</p></div></div>
    <div class="stat-card"><div class="stat-info"><h3>₱<?php echo number_format($stats['total_revenue'], 2); ?></h3><p>Total Sales</p></div></div>
    <div class="stat-card"><div class="stat-info"><h3><?php echo number_format($stats['total_reviews']); ?></h3><p>Total Reviews</p></div></div>
</div>
<div class="admin-grid-2">
    <div class="admin-table-wrapper">
        <div class="table-header"><h2>Recent Bookings</h2><a href="dashboard.php?page=bookings" class="btn-action" style="font-weight:600;">View All →</a></div>
        <table class="admin-table">
            <thead><tr><th>Booking ID</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($recent_bookings as $booking): ?>
                    <tr><td>#<?php echo $booking['order_id']; ?></td><td>₱<?php echo number_format($booking['total_amount'], 2); ?></td><td><span class="badge badge-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="admin-table-wrapper">
        <div class="table-header"><h2>Recent Reviews</h2><a href="dashboard.php?page=reviews" class="btn-action" style="font-weight:600;">View All →</a></div>
        <table class="admin-table">
            <thead><tr><th>Customer</th><th>Rating</th><th>Comment</th></tr></thead>
            <tbody>
                <?php foreach ($recent_reviews as $review): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($review['name']); ?></td>
                        <td><span style="color:#f5a623;"><?php for ($i=1; $i<=5; $i++) echo $i <= $review['rating'] ? '★' : '☆'; ?></span></td>
                        <td><?php echo htmlspecialchars(substr($review['comment'],0,40)) . (strlen($review['comment'])>40?'...':''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>