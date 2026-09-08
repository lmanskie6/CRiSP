<?php
$pdo = getConnection();
$stmt = $pdo->query("SELECT user_id, username, name, email, role, is_active, created_at, last_login 
                     FROM users 
                     WHERE role = 'customer' 
                     ORDER BY created_at DESC");
$customers = $stmt->fetchAll();
?>
<div class="admin-table-wrapper">
    <div class="table-header">
        <h2>Customers</h2>
        <span class="customer-count">Total: <?php echo count($customers); ?> customers</span>
    </div>
    <?php if (count($customers) > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['user_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($customer['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><span class="badge badge-<?php echo $customer['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $customer['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                        <td><?php echo $customer['last_login'] ? date('M d, Y', strtotime($customer['last_login'])) : 'Never'; ?></td>
                        <td>
                            <button class="btn-action" onclick="alert('Edit customer: <?php echo $customer['username']; ?>')">Edit</button>
                            <button class="btn-action btn-danger" onclick="if(confirm('Delete customer <?php echo $customer['username']; ?>?')) alert('Deleted!')">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-muted" style="padding: 40px 0;">No customers have registered yet.</p>
    <?php endif; ?>
</div>