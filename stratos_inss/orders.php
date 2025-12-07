<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

$user_id = $_SESSION['user_id'];

// Fetch Orders
$orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

// Fetch Redemptions
$redemptions_sql = "SELECT * FROM redemptions WHERE user_id = ? ORDER BY created_at DESC";
$stmt2 = $conn->prepare($redemptions_sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$redemptions_result = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Stratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 280px;">
            <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
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
            <h1 class="mb-4">Transaction History</h1>

            <h3 class="mt-4">Purchases (Fuel & Shop)</h3>
            <div class="table-responsive mb-5">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders_result->num_rows > 0): ?>
                            <?php while ($row = $orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td>
                                        <?php if ($row['type'] == 'Fuel'): ?>
                                            <span class="badge bg-danger">Fuel</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Shop</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                                    <td>RM <?php echo $row['total_price']; ?></td>
                                    <td><span class="badge bg-success"><?php echo $row['status']; ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h3 class="mt-4">Rewards Redeemed</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-secondary">
                        <tr>
                            <th>Redemption ID</th>
                            <th>Reward Name</th>
                            <th>Points Spent</th>
                            <th>Date Redeemed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($redemptions_result->num_rows > 0): ?>
                            <?php while ($row = $redemptions_result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td><?php echo $row['reward_name']; ?></td>
                                    <td class="text-danger">-<?php echo $row['points_spent']; ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No redemptions yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> Stratos INSS. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>