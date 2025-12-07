<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

// Check if Admin
$user_id = $_SESSION['user_id'];
$check_admin = $conn->query("SELECT role FROM users WHERE id = $user_id");
$user_role = $check_admin->fetch_assoc()['role'];

if ($user_role !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $conn->query("UPDATE orders SET status = '$new_status' WHERE id = $order_id");
    $success_msg = "Order #$order_id updated to $new_status!";
}

// Fetch Orders
$orders_result = $conn->query("SELECT orders.*, users.username FROM orders JOIN users ON orders.user_id = users.id ORDER BY orders.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Stratos Admin</title>
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
                <li class="nav-item"><a href="admin_dashboard.php" class="nav-link link-dark">Overview</a></li>
                <li><a href="admin_products.php" class="nav-link link-dark">Manage Products</a></li>
                <li><a href="admin_orders.php" class="nav-link active" aria-current="page">Manage Orders</a></li>
                <li><a href="admin_customers.php" class="nav-link link-dark">Customer List</a></li>
                <li><a href="admin_messages.php" class="nav-link link-dark">Support Messages</a></li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="logout.php" class="d-flex align-items-center link-dark text-decoration-none"><strong>Sign out</strong></a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container-fluid p-5">
            <h1 class="mb-4">Order Management</h1>

            <?php if (isset($success_msg)): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Details</th>
                                    <th>Delivery Info</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($row['username']); ?><br>
                                            <small class="text-muted"><?php echo $row['phone']; ?></small>
                                        </td>
                                        <td>
                                            <small>Payment: <?php echo $row['payment_method']; ?></small><br>
                                            <small>Date: <?php echo date('d/m/y', strtotime($row['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($row['delivery_method'] == 'Delivery'): ?>
                                                <span class="badge bg-info text-dark">🚚 Delivery</span><br>
                                                <small class="text-muted" style="font-size: 0.8em;"><?php echo substr($row['delivery_address'], 0, 20) . '...'; ?></small>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">🏪 Pickup</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>RM <?php echo number_format($row['total_price'], 2); ?></td>
                                        <td>
                                            <?php 
                                            $status_class = match($row['status']) {
                                                'Completed' => 'bg-success',
                                                'Pending' => 'bg-warning',
                                                'Shipped' => 'bg-primary',
                                                'Cancelled' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $row['id']; ?>">
                                                Manage
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal for Order Details -->
                                    <div class="modal fade" id="orderModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Order #<?php echo $row['id']; ?> Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Customer:</strong> <?php echo $row['username']; ?></p>
                                                    <p><strong>Phone:</strong> <?php echo $row['phone']; ?></p>
                                                    <hr>
                                                    <p><strong>Delivery Method:</strong> <?php echo $row['delivery_method']; ?></p>
                                                    <?php if ($row['delivery_method'] == 'Delivery'): ?>
                                                        <p><strong>Address:</strong><br><?php echo nl2br($row['delivery_address']); ?></p>
                                                    <?php endif; ?>
                                                    <p><strong>Notes:</strong> <?php echo $row['notes'] ? $row['notes'] : 'None'; ?></p>
                                                    <hr>
                                                    <form method="POST">
                                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                                        <label class="form-label fw-bold">Update Status:</label>
                                                        <select name="status" class="form-select mb-3">
                                                            <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="Shipped" <?php echo $row['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                            <option value="Ready for Pickup" <?php echo $row['status'] == 'Ready for Pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                                            <option value="Completed" <?php echo $row['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                            <option value="Cancelled" <?php echo $row['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        </select>
                                                        <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
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