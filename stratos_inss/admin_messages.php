<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

// Check Admin
$user_id = $_SESSION['user_id'];
$check_admin = $conn->query("SELECT role FROM users WHERE id = $user_id");
$user_role = $check_admin->fetch_assoc()['role'];

if ($user_role !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Fetch Messages
$messages_res = $conn->query("SELECT messages.*, users.username, users.email FROM messages JOIN users ON messages.user_id = users.id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Messages - Stratos Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 280px;">
            <a href="admin_dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
                <img src="sources/logo.png" alt="Stratos" style="height: 100px;" class="me-2">
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li><a href="admin_dashboard.php" class="nav-link link-dark">Overview</a></li>
                <li><a href="admin_products.php" class="nav-link link-dark">Manage Products</a></li>
                <li><a href="admin_orders.php" class="nav-link link-dark">Manage Orders</a></li>
                <li><a href="admin_customers.php" class="nav-link link-dark">Customer List</a></li>
                <li><a href="admin_messages.php" class="nav-link active">Support Messages</a></li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="logout.php" class="d-flex align-items-center link-dark text-decoration-none"><strong>Sign out</strong></a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container-fluid p-5">
            <h1 class="mb-4">Support Messages</h1>

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($messages_res->num_rows > 0): ?>
                                    <?php while ($row = $messages_res->fetch_assoc()): ?>
                                        <tr>
                                            <td style="width: 150px;"><?php echo date('d M Y, g:i A', strtotime($row['created_at'])); ?></td>
                                            <td style="width: 200px;">
                                                <strong><?php echo htmlspecialchars($row['username']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                            </td>
                                            <td style="width: 200px;"><span class="badge bg-primary"><?php echo htmlspecialchars($row['subject']); ?></span></td>
                                            <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                                            <td>
                                                <a href="mailto:<?php echo $row['email']; ?>?subject=Re: <?php echo $row['subject']; ?>" class="btn btn-sm btn-outline-primary">Reply</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">No messages found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Stratos INSS. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
</html>