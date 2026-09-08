<?php
$pdo = getConnection();
$stmt = $pdo->query("SELECT rate_id, rate_name, rate_type, price_per_unit, is_active FROM rates ORDER BY rate_id ASC");
$rates = $stmt->fetchAll();
//sample data
if (empty($rates)) {
    $rates = [
        ['rate_id' => 1, 'rate_name' => 'Daily Wear Rate', 'rate_type' => 'per piece', 'price_per_unit' => 45.00, 'is_active' => 1],
        ['rate_id' => 2, 'rate_name' => 'Formal Wear Rate', 'rate_type' => 'per piece', 'price_per_unit' => 180.00, 'is_active' => 1],
        ['rate_id' => 3, 'rate_name' => 'Delicates Rate', 'rate_type' => 'per piece', 'price_per_unit' => 85.00, 'is_active' => 1],
        ['rate_id' => 4, 'rate_name' => 'Household Rate', 'rate_type' => 'per piece', 'price_per_unit' => 25.00, 'is_active' => 1],
    ];
}
?>
<div class="admin-table-wrapper">
    <div class="table-header">
        <h2>Rates</h2>
        <button class="btn btn-primary btn-sm" onclick="alert('Add rate functionality coming soon!')">Add Rate</button>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Rate Name</th>
                <th>Rate Type</th>
                <th>Price Per Unit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rates as $rate): ?>
                <tr>
                    <td><?php echo $rate['rate_id']; ?></td>
                    <td><?php echo htmlspecialchars($rate['rate_name']); ?></td>
                    <td><?php echo htmlspecialchars($rate['rate_type']); ?></td>
                    <td>₱<?php echo number_format($rate['price_per_unit'], 2); ?></td>
                    <td><span class="badge badge-<?php echo $rate['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $rate['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                    <td>
                        <button class="btn-action" onclick="alert('Edit rate: <?php echo $rate['rate_name']; ?>')">Edit</button>
                        <button class="btn-action btn-danger" onclick="if(confirm('Delete rate <?php echo $rate['rate_name']; ?>?')) alert('Deleted!')">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>