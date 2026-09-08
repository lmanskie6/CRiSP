<?php
$pdo = getConnection();
$stmt = $pdo->query("SELECT order_id, user_id, total_amount, status, created_at FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll();
?>
<div class="admin-table-wrapper">
    <div class="table-header">
        <h2>All Bookings</h2>
        <div class="table-filters">
            <select onchange="alert('Filter by: ' + this.value)">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer ID</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                    <td><?php echo $order['user_id']; ?></td>
                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    <td>
                        <button class="btn-action" onclick="alert('View booking #<?php echo $order['order_id']; ?>')">View</button>
                        <button class="btn-action" onclick="alert('Update booking #<?php echo $order['order_id']; ?>')">Update</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>