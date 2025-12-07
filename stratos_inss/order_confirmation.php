<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];

// Get order details
$order_query = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");
if ($order_query->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}
$order = $order_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Stratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 280px;">
            <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
                <img src="sources/logo.png" alt="Stratos" style="height: 100px;" class="me-2">
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active" aria-current="page">Dashboard</a>
                </li>
                <li>
                    <a href="fuel.php" class="nav-link link-dark">Pay for Fuel</a>
                </li>
                <li>
                    <a href="shop.php" class="nav-link link-dark">Shop Products</a>
                </li>
                <li>
                    <a href="rewards.php" class="nav-link link-dark">Rewards</a>
                </li>
                <li>
                    <a href="orders.php" class="nav-link link-dark">My Orders</a>
                </li>
                <li>
                    <a href="contact.php" class="nav-link link-dark">Contact Support</a>
                </li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="logout.php" class="d-flex align-items-center link-dark text-decoration-none">
                    <strong>Sign out</strong>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container-fluid p-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Success Message -->
                    <div class="text-center mb-5">
                        <div class="mb-4">
                            <span style="font-size: 80px;">✅</span>
                        </div>
                        <h1 class="mb-3">Order Confirmed!</h1>
                        <p class="lead text-muted">Thank you for your purchase. Your order has been received.</p>
                    </div>

                    <!-- Order Details Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white fw-bold">
                            📦 Order Details
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Order Number:</strong></p>
                                    <p class="text-muted">#<?php echo $order['id']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Order Date:</strong></p>
                                    <p class="text-muted"><?php echo date('d M Y, g:i A', strtotime($order['created_at'])); ?></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Total Amount:</strong></p>
                                    <p class="text-danger fw-bold fs-4">RM <?php echo number_format($order['total_price'], 2); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Points Earned:</strong></p>
                                    <p class="text-primary fw-bold fs-4">💎 <?php echo $order['points_earned']; ?> pts</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Delivery Method:</strong></p>
                                    <p class="text-muted">
                                        <?php echo $order['delivery_method'] == 'Delivery' ? '🚚 Home Delivery' : '🏪 Store Pickup'; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Payment Method:</strong></p>
                                    <p class="text-muted">💳 <?php echo $order['payment_method']; ?></p>
                                </div>
                            </div>
                            <?php if ($order['delivery_method'] == 'Delivery'): ?>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <p class="mb-1"><strong>Delivery Address:</strong></p>
                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Contact Phone:</strong></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($order['phone']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Status:</strong></p>
                                    <p><span class="badge bg-warning"><?php echo $order['status']; ?></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- What's Next -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-white fw-bold">
                            ⏭️ What's Next?
                        </div>
                        <div class="card-body">
                            <?php if ($order['delivery_method'] == 'Delivery'): ?>
                                <p>✅ Your order will be processed and shipped within 1-2 business days.</p>
                                <p>✅ You'll receive an SMS notification when your order is out for delivery.</p>
                                <p>✅ Estimated delivery: <strong>2-3 business days</strong></p>
                            <?php else: ?>
                                <p>✅ Your order will be ready for pickup within 1-2 hours.</p>
                                <p>✅ You'll receive an SMS notification when it's ready.</p>
                                <p>✅ Pickup location: <strong>Stratos INSS Main Store</strong></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3">
                        <a href="orders.php" class="btn btn-primary btn-lg flex-grow-1">
                            📋 View My Orders
                        </a>
                        <a href="shop.php" class="btn btn-outline-primary btn-lg flex-grow-1">
                            🛒 Continue Shopping
                        </a>
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