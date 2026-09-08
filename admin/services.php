<?php
$pdo = getConnection();
$stmt = $pdo->query("SELECT service_id, service_name, description, price, is_active FROM services ORDER BY service_id ASC");
$services = $stmt->fetchAll();
// sample data
if (empty($services)) {
    $services = [
        ['service_id' => 1, 'service_name' => 'Daily Wear', 'description' => 'Dress shirts, trousers, polo shirts', 'price' => 45.00, 'is_active' => 1],
        ['service_id' => 2, 'service_name' => 'Formal Wear', 'description' => 'Suits, tuxedos, blazers', 'price' => 180.00, 'is_active' => 1],
        ['service_id' => 3, 'service_name' => 'Delicates', 'description' => 'Silk, linen, cashmere, wool', 'price' => 85.00, 'is_active' => 1],
        ['service_id' => 4, 'service_name' => 'Household', 'description' => 'Bedsheet sets, tablecloths', 'price' => 25.00, 'is_active' => 1],
    ];
}
?>
<div class="admin-table-wrapper">
    <div class="table-header">
        <h2>Services</h2>
        <button class="btn btn-primary btn-sm" onclick="alert('Add service functionality coming soon!')">Add Service</button>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Service Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?php echo $service['service_id']; ?></td>
                    <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                    <td><?php echo htmlspecialchars($service['description']); ?></td>
                    <td>₱<?php echo number_format($service['price'], 2); ?></td>
                    <td><span class="badge badge-<?php echo $service['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                    <td>
                        <button class="btn-action" onclick="alert('Edit service: <?php echo $service['service_name']; ?>')">Edit</button>
                        <button class="btn-action btn-danger" onclick="if(confirm('Delete service <?php echo $service['service_name']; ?>?')) alert('Deleted!')">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>