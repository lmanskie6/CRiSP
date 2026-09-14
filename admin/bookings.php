<?php
$pdo = getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    header("Location: dashboard.php?page=bookings");
    exit();
}

// Filters
$filter = $_GET['filter'] ?? 'all';
$allowed_filters = ['all', 'pending', 'processing', 'completed', 'cancelled'];
if (!in_array($filter, $allowed_filters)) {
    $filter = 'all';
}

// Booking query
$sql = "
    SELECT 
        o.*, 
        u.name AS customer_name, 
        u.username, 
        u.email,
        s.service_name AS linked_service_name,
        s.description AS service_description,
        r.rate_name,
        r.price_per_unit AS current_rate_price
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN services s ON o.service_id = s.service_id
    LEFT JOIN rates r ON o.rate_id = r.rate_id
";

if ($filter !== 'all') {
    $sql .= " WHERE o.status = :status";
}
$sql .= " ORDER BY user_id ASC";

$stmt = $pdo->prepare($sql);
if ($filter !== 'all') {
    $stmt->bindValue(':status', $filter, PDO::PARAM_STR);
}
$stmt->execute();
$orders = $stmt->fetchAll();
?>
<div class="admin-table-wrapper">
    <div class="table-header">
        <h2>All Bookings</h2>
        <div class="table-filters">
            <select onchange="window.location.href='dashboard.php?page=bookings&filter=' + this.value">
                <option value="all"        <?php echo $filter === 'all'        ? 'selected' : ''; ?>>All Status</option>
                <option value="pending"    <?php echo $filter === 'pending'    ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="completed"  <?php echo $filter === 'completed'  ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled"  <?php echo $filter === 'cancelled'  ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Qty</th>
                <th>Total Amount</th>
                <th>Pickup Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($orders) > 0): foreach ($orders as $order): ?>
                <tr>
                    <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                    <td>
                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                        <br><small style="color:var(--gray);">@<?php echo htmlspecialchars($order['username']); ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($order['linked_service_name'] ?? $order['service_name']); ?>
                        <?php if ($order['rate_name']): ?>
                            <br><small style="color:var(--gray);">
                                <?php echo htmlspecialchars($order['rate_name']); ?> 
                                (Current: ₱<?php echo number_format($order['current_rate_price'], 2); ?>)
                            </small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $order['quantity']; ?></td>
                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo date('M d, Y', strtotime($order['pickup_date'])); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <select name="status" onchange="this.form.submit()" style="padding:4px 8px; border-radius:4px; border:1px solid #ddd; font-family:'Poppins',sans-serif; font-size:12px;">
                                <?php foreach (['pending','processing','completed','cancelled'] as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $order['status'] == $s ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($s); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td>
                        <button class="btn-action view-booking" 
                            data-id="<?php echo $order['order_id']; ?>"
                            data-customer="<?php echo htmlspecialchars($order['customer_name']); ?>"
                            data-service="<?php echo htmlspecialchars($order['linked_service_name'] ?? $order['service_name']); ?>"
                            data-rate="<?php echo htmlspecialchars($order['rate_name'] ?? 'N/A'); ?>"
                            data-qty="<?php echo $order['quantity']; ?>"
                            data-total="<?php echo number_format($order['total_amount'], 2); ?>"
                            data-pickup="<?php echo date('M d, Y', strtotime($order['pickup_date'])); ?>"
                            data-time="<?php echo $order['pickup_time'] ? date('g:i A', strtotime($order['pickup_time'])) : 'N/A'; ?>"
                            data-status="<?php echo ucfirst($order['status']); ?>"
                            data-payment="<?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?>"
                            data-payment-details="<?php echo htmlspecialchars($order['payment_details'] ?? ''); ?>"
                            data-notes="<?php echo htmlspecialchars($order['notes'] ?: 'None'); ?>"
                            data-created="<?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>">View</button>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="8" style="text-align:center; padding:40px 0; color:var(--gray);">
                    No bookings found<?php echo $filter !== 'all' ? ' with status: <strong>' . htmlspecialchars(ucfirst($filter)) . '</strong>' : ''; ?>.
                </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>