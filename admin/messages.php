<?php
$pdo = getConnection();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Message deleted successfully.'];
    header("Location: dashboard.php?page=messages");
    exit();
}

// Handle mark as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->execute([$_GET['read']]);
    header("Location: dashboard.php?page=messages");
    exit();
}

// Fetch all messages
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

$unread_count = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
?>
<div class="admin-table-wrapper">
    <div class="table-header">
        <h2>Contact Messages <?php echo $unread_count > 0 ? '<span style="background:#c62828;color:white;padding:2px 10px;border-radius:20px;font-size:12px;margin-left:10px;">' . $unread_count . ' unread</span>' : ''; ?></h2>
    </div>

    <?php if (count($messages) > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name / Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr style="<?php echo $msg['status'] === 'unread' ? 'background:#fef9e7;' : ''; ?>">
                        <td><?php echo $msg['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                            <br><small style="color:var(--gray);"><?php echo htmlspecialchars($msg['email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                        <td><?php echo htmlspecialchars(substr($msg['message'], 0, 50)) . (strlen($msg['message']) > 50 ? '...' : ''); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $msg['status'] === 'read' ? 'active' : 'pending'; ?>">
                                <?php echo ucfirst($msg['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></td>
                        <td>
                            <?php if ($msg['status'] === 'unread'): ?>
                                <a href="dashboard.php?page=messages&read=<?php echo $msg['id']; ?>" class="btn-action">Mark Read</a>
                            <?php endif; ?>
                            <button class="btn-action view-message" 
                                data-name="<?php echo htmlspecialchars($msg['name']); ?>"
                                data-email="<?php echo htmlspecialchars($msg['email']); ?>"
                                data-subject="<?php echo htmlspecialchars($msg['subject']); ?>"
                                data-message="<?php echo htmlspecialchars($msg['message']); ?>"
                                data-date="<?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?>">View</button>
                            <a href="dashboard.php?page=messages&delete=<?php echo $msg['id']; ?>" 
                               class="btn-action btn-danger" 
                               onclick="return confirm('Delete this message?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-muted" style="padding: 40px 0;">No messages yet.</p>
    <?php endif; ?>
</div>

<!-- Message View Modal -->
<div class="booking-modal" id="messageModal">
    <div class="booking-modal-content">
        <span class="booking-modal-close" id="messageModalClose">&times;</span>
        <h2>Message Details</h2>
        <div class="booking-details">
            <div class="detail-row"><strong>Name:</strong> <span id="mName"></span></div>
            <div class="detail-row"><strong>Email:</strong> <span id="mEmail"></span></div>
            <div class="detail-row"><strong>Subject:</strong> <span id="mSubject"></span></div>
            <div class="detail-row"><strong>Date:</strong> <span id="mDate"></span></div>
            <div class="detail-row" style="flex-direction:column; align-items:flex-start; gap:5px;">
                <strong>Message:</strong>
                <span id="mMessage" style="padding:10px; background:var(--gray-light); border-radius:8px; width:100%;"></span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('messageModal');
    const closeBtn = document.getElementById('messageModalClose');

    document.querySelectorAll('.view-message').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('mName').textContent = this.dataset.name;
            document.getElementById('mEmail').textContent = this.dataset.email;
            document.getElementById('mSubject').textContent = this.dataset.subject;
            document.getElementById('mMessage').textContent = this.dataset.message;
            document.getElementById('mDate').textContent = this.dataset.date;
            modal.classList.add('active');
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.classList.remove('active');
        });
    }

    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            modal.classList.remove('active');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    });
});
</script>