<?php
$pdo = getConnection();

$stmt = $pdo->query("
    SELECT r.review_id, r.user_id, r.rating, r.comment, r.status, r.created_at,
           u.full_name, u.username
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();

$stmt = $pdo->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews");
$stats = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['review_id'])) {
    $review_id = $_POST['review_id'];
    $status = $_POST['action'] === 'approve' ? 'approved' : 'hidden';
    
    $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE review_id = ?");
    $stmt->execute([$status, $review_id]);
    
    header("Location: dashboard.php?page=reviews");
    exit();
}
?>

<div class="reviews-stats">
    <div class="stat-card">
        <h3><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
        <p>Average Rating</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['total_reviews'] ?? 0; ?></h3>
        <p>Total Reviews</p>
    </div>
</div>

<div class="admin-table-wrapper">
    <div class="table-header">
        <h2>Customer Reviews</h2>
    </div>

    <?php if (count($reviews) > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?php echo $review['review_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                            <br><small style="color: var(--gray);">@<?php echo htmlspecialchars($review['username']); ?></small>
                        </td>
                        <td>
                            <span class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $review['rating'] ? 'filled' : 'empty'; ?>">★</span>
                                <?php endfor; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars(substr($review['comment'], 0, 80)); ?>
                            <?php if (strlen($review['comment']) > 80): ?>...<?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $review['status'] ?? 'pending'; ?>">
                                <?php echo ucfirst($review['status'] ?? 'Pending'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (($review['status'] ?? '') !== 'approved'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-action">Approve</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if (($review['status'] ?? '') !== 'hidden'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                    <input type="hidden" name="action" value="hide">
                                    <button type="submit" class="btn-action btn-danger">Hide</button>
                                </form>
                            <?php endif; ?>
                            
                            <button class="btn-action btn-danger" onclick="if(confirm('Delete review #<?php echo $review['review_id']; ?>?')) alert('Deleted!')">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-muted" style="padding: 40px 0;">No reviews have been submitted yet.</p>
    <?php endif; ?>
</div>