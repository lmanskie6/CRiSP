<?php
require_once '../validation.php';

$pdo = getConnection();
$error = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : null;
unset($_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $name = trim($_POST['rate_name']);
    $type = trim($_POST['rate_type']);
    $price = (float)$_POST['price_per_unit'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validate
    $validation_error = null;
    $validation_error = validateRequired($name, 'Rate name');
    if (!$validation_error) {
        $validation_error = validateNumeric($price, 0, 999999, 'Price per unit');
    }
    if (!$validation_error) {
        if ($_POST['action'] == 'add') {
            $stmt = $pdo->prepare("INSERT INTO rates (rate_name, rate_type, price_per_unit, is_active) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $type, $price, $is_active])) {
                header("Location: dashboard.php?page=rates");
                exit();
            } else {
                $validation_error = "Failed to add rate.";
            }
        } elseif ($_POST['action'] == 'edit') {
            $id = (int)$_POST['rate_id'];
            $stmt = $pdo->prepare("UPDATE rates SET rate_name = ?, rate_type = ?, price_per_unit = ?, is_active = ? WHERE rate_id = ?");
            if ($stmt->execute([$name, $type, $price, $is_active, $id])) {
                header("Location: dashboard.php?page=rates");
                exit();
            } else {
                $validation_error = "Failed to update rate.";
            }
        }
    }

    if ($validation_error) {
        $_SESSION['flash_error'] = $validation_error;
        header("Location: dashboard.php?page=rates");
        exit();
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM rates WHERE rate_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: dashboard.php?page=rates");
    exit();
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE rates SET is_active = NOT is_active WHERE rate_id = ?");
    $stmt->execute([$_GET['toggle']]);
    header("Location: dashboard.php?page=rates");
    exit();
}

$rates = $pdo->query("SELECT rate_id, rate_name, rate_type, price_per_unit, is_active FROM rates ORDER BY rate_id")->fetchAll();
?>
<div class="admin-table-wrapper">
    <div class="table-header">
        <h2>Rates</h2>
        <button class="btn btn-primary btn-sm" onclick="openAddModal()">+ Add Rate</button>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="background:#fbe9e7; color:#c62828; padding:10px 16px; border-radius:8px; margin-bottom:15px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (count($rates) > 0): ?>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Rate Name</th><th>Rate Type</th><th>Price Per Unit</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($rates as $rate): ?>
                    <tr>
                        <td><?php echo $rate['rate_id']; ?></td>
                        <td><?php echo htmlspecialchars($rate['rate_name']); ?></td>
                        <td><?php echo htmlspecialchars($rate['rate_type']); ?></td>
                        <td>₱<?php echo number_format($rate['price_per_unit'], 2); ?></td>
                        <td>
                            <a href="dashboard.php?page=rates&toggle=<?php echo $rate['rate_id']; ?>" class="badge badge-<?php echo $rate['is_active'] ? 'active' : 'inactive'; ?>" style="text-decoration:none;">
                                <?php echo $rate['is_active'] ? 'Active' : 'Inactive'; ?>
                            </a>
                        </td>
                        <td>
                            <button class="btn-action" onclick="openEditModal(<?php echo $rate['rate_id']; ?>, '<?php echo addslashes($rate['rate_name']); ?>', '<?php echo addslashes($rate['rate_type']); ?>', <?php echo $rate['price_per_unit']; ?>, <?php echo $rate['is_active']; ?>)">Edit</button>
                            <a href="#" data-delete-url="dashboard.php?page=rates&delete=<?php echo $rate['rate_id']; ?>" data-name="Rate: <?php echo htmlspecialchars($rate['rate_name']); ?>" class="btn-action btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-muted" style="padding: 40px 0;">No rates added yet. Click "Add Rate" to create one.</p>
    <?php endif; ?>
</div>

<!-- Add Rate Modal -->
<div id="addModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:20px; padding:30px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto;">
        <h2 style="margin-bottom:20px;">Add New Rate</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Rate Name *</label>
                <input type="text" name="rate_name" required style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px;">
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Rate Type</label>
                <select name="rate_type" style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px;">
                    <option value="per piece">Per Piece</option>
                    <option value="per kg">Per Kilogram</option>
                    <option value="per set">Per Set</option>
                    <option value="per item">Per Item</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Price Per Unit (₱) *</label>
                <input type="number" name="price_per_unit" step="0.01" required style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px;">
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="is_active" checked> Active
                </label>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 30px; border:none; border-radius:8px; cursor:pointer;">Add Rate</button>
                <button type="button" onclick="closeAddModal()" style="padding:10px 30px; background:#ddd; border:none; border-radius:8px; cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Rate Modal -->
<div id="editModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:20px; padding:30px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto;">
        <h2 style="margin-bottom:20px;">Edit Rate</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="rate_id" id="edit_rate_id">
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Rate Name *</label>
                <input type="text" name="rate_name" id="edit_rate_name" required style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px;">
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Rate Type</label>
                <select name="rate_type" id="edit_rate_type" style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px;">
                    <option value="per piece">Per Piece</option>
                    <option value="per kg">Per Kilogram</option>
                    <option value="per set">Per Set</option>
                    <option value="per item">Per Item</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Price Per Unit (₱) *</label>
                <input type="number" name="price_per_unit" id="edit_price_per_unit" step="0.01" required style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px;">
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="is_active" id="edit_is_active"> Active
                </label>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 30px; border:none; border-radius:8px; cursor:pointer;">Update Rate</button>
                <button type="button" onclick="closeEditModal()" style="padding:10px 30px; background:#ddd; border:none; border-radius:8px; cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }
function openEditModal(id, name, type, price, isActive) {
    document.getElementById('edit_rate_id').value = id;
    document.getElementById('edit_rate_name').value = name;
    document.getElementById('edit_rate_type').value = type;
    document.getElementById('edit_price_per_unit').value = price;
    document.getElementById('edit_is_active').checked = isActive == 1;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
});
</script>