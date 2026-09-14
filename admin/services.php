<?php
require_once '../validation.php';

$pdo = getConnection();
$error = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : null;
unset($_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $name = trim($_POST['service_name']);
    $desc = trim($_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    $validation_error = validateRequired($name, 'Service name');
    if (!$validation_error) {
        if ($_POST['action'] == 'add') {
            $stmt = $pdo->prepare("INSERT INTO services (service_name, description, is_active) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $desc, $is_active])) {
                header("Location: dashboard.php?page=services");
                exit();
            } else {
                $validation_error = "Failed to add service. Please try again.";
            }
        } elseif ($_POST['action'] == 'edit') {
            $id = (int)$_POST['service_id'];
            $stmt = $pdo->prepare("UPDATE services SET service_name = ?, description = ?, is_active = ? WHERE service_id = ?");
            if ($stmt->execute([$name, $desc, $is_active, $id])) {
                header("Location: dashboard.php?page=services");
                exit();
            } else {
                $validation_error = "Failed to update service.";
            }
        }
    }

    if ($validation_error) {
        $_SESSION['flash_error'] = $validation_error;
        header("Location: dashboard.php?page=services");
        exit();
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: dashboard.php?page=services");
    exit();
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE services SET is_active = NOT is_active WHERE service_id = ?");
    $stmt->execute([$_GET['toggle']]);
    header("Location: dashboard.php?page=services");
    exit();
}

$services = $pdo->query("SELECT service_id, service_name, description, is_active FROM services ORDER BY service_id")->fetchAll();
?>
<div class="admin-table-wrapper">
    <div class="table-header">
        <h2>Services</h2>
        <button class="btn btn-primary btn-sm" onclick="openAddModal()">+ Add Service</button>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="background:#fbe9e7; color:#c62828; padding:10px 16px; border-radius:8px; margin-bottom:15px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (count($services) > 0): ?>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Service Name</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo $service['service_id']; ?></td>
                        <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                        <td><?php echo htmlspecialchars($service['description']); ?></td>
                        <td>
                            <a href="dashboard.php?page=services&toggle=<?php echo $service['service_id']; ?>" class="badge badge-<?php echo $service['is_active'] ? 'active' : 'inactive'; ?>" style="text-decoration:none;">
                                <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                            </a>
                        </td>
                        <td>
                            <button class="btn-action" onclick="openEditModal(<?php echo $service['service_id']; ?>, '<?php echo addslashes($service['service_name']); ?>', '<?php echo addslashes($service['description']); ?>', <?php echo $service['is_active']; ?>)">Edit</button>
                            <a href="#" data-delete-url="dashboard.php?page=services&delete=<?php echo $service['service_id']; ?>" data-name="Service: <?php echo htmlspecialchars($service['service_name']); ?>" class="btn-action btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-muted" style="padding: 40px 0;">No services added yet. Click "Add Service" to create one.</p>
    <?php endif; ?>
</div>

<!-- Add Service Modal -->
<div id="addModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:20px; padding:30px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto;">
        <h2 style="margin-bottom:20px;">Add New Service</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Service Name *</label>
                <input type="text" name="service_name" required style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px;">
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Description</label>
                <textarea name="description" style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px; min-height:80px;"></textarea>
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="is_active" checked> Active
                </label>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 30px; border:none; border-radius:8px; cursor:pointer;">Add Service</button>
                <button type="button" onclick="closeAddModal()" style="padding:10px 30px; background:#ddd; border:none; border-radius:8px; cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Service Modal -->
<div id="editModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:20px; padding:30px; max-width:500px; width:90%; max-height:90vh; overflow-y:auto;">
        <h2 style="margin-bottom:20px;">Edit Service</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="service_id" id="edit_service_id">
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Service Name *</label>
                <input type="text" name="service_name" id="edit_service_name" required style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px;">
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:500;">Description</label>
                <textarea name="description" id="edit_description" style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px; min-height:80px;"></textarea>
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="is_active" id="edit_is_active"> Active
                </label>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 30px; border:none; border-radius:8px; cursor:pointer;">Update Service</button>
                <button type="button" onclick="closeEditModal()" style="padding:10px 30px; background:#ddd; border:none; border-radius:8px; cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }
function openEditModal(id, name, description, isActive) {
    document.getElementById('edit_service_id').value = id;
    document.getElementById('edit_service_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_is_active').checked = isActive == 1;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
});
</script>