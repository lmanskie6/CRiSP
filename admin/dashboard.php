<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'overview';
$allowed_pages = ['overview', 'customers', 'bookings', 'services', 'rates', 'reviews'];

$page_titles = [
    'overview' => 'Dashboard',
    'customers' => 'Customers',
    'bookings' => 'Bookings',
    'services' => 'Services',
    'rates' => 'Rates',
    'reviews' => 'Reviews'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'overview';
}

$user_name = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – CRiSP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..800&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div id="app">
        <header class="admin-header">
            <div class="admin-header-container">
                <div class="admin-logo">
                    <a href="dashboard.php">
                        <img src="../images/logo.svg" alt="CRiSP" class="admin-logo-icon">
                    </a>
                </div>
                <div class="admin-user">
                    <span class="admin-user-name">Welcome, <b><?php echo htmlspecialchars($user_name); ?></b></span>
                    <a href="../logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
        </header>

        <div class="admin-body">
            <aside class="admin-sidebar">
                <nav class="admin-nav">
                    <ul>
                        <li><a href="dashboard.php?page=overview" class="<?php echo $page === 'overview' ? 'active' : ''; ?>">Dashboard</a></li>
                        <li><a href="dashboard.php?page=customers" class="<?php echo $page === 'customers' ? 'active' : ''; ?>">Customers</a></li>
                        <li><a href="dashboard.php?page=bookings" class="<?php echo $page === 'bookings' ? 'active' : ''; ?>">Bookings</a></li>
                        <li><a href="dashboard.php?page=services" class="<?php echo $page === 'services' ? 'active' : ''; ?>">Services</a></li>
                        <li><a href="dashboard.php?page=rates" class="<?php echo $page === 'rates' ? 'active' : ''; ?>">Rates</a></li>
                        <li><a href="dashboard.php?page=reviews" class="<?php echo $page === 'reviews' ? 'active' : ''; ?>">Reviews</a></li>
                    </ul>
                </nav>
            </aside>

            <main class="admin-content">
                <div class="admin-content-header">
                    <h1><?php echo $page_titles[$page]; ?></h1>
                </div>
                <div class="admin-content-body">
                    <?php include $page . '.php'; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>