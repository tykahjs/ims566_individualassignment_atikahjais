<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

// check admin access
$user_id = $_SESSION['user_id'];
$check_admin = $conn->query("SELECT role FROM users WHERE id = $user_id");
$user_role = $check_admin->fetch_assoc()['role'];

if ($user_role !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// get revenue
$revenue_res = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'Completed'");
$total_revenue = $revenue_res->fetch_assoc()['total'] ?? 0;

// total orders
$orders_count_res = $conn->query("SELECT COUNT(*) as count FROM orders");
$total_orders = $orders_count_res->fetch_assoc()['count'];

// pending orders
$pending_orders_res = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'");
$pending_orders = $pending_orders_res->fetch_assoc()['count'];

// total customers
$customers_res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$total_customers = $customers_res->fetch_assoc()['count'];

// total products
$products_res = $conn->query("SELECT COUNT(*) as count FROM products");
$total_products = $products_res->fetch_assoc()['count'];

// TODO: make this dynamic from database instead of hardcoded
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$sales_data = [1200, 1900, 3000, 5000, 2300, 3400, 4500, 5600, 6000, 7000, 8000, 9500];

// recent orders query
$orders_sql = "SELECT orders.id, users.username, orders.total_price, orders.status, orders.created_at 
               FROM orders 
               JOIN users ON orders.user_id = users.id 
               ORDER BY orders.created_at DESC LIMIT 10";
$orders_result = $conn->query($orders_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Stratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            text-decoration: none;
            display: block;
            height: 100%;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }
    </style>
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
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link active" aria-current="page">Overview</a>
                </li>
                <li>
                    <a href="admin_products.php" class="nav-link link-dark">Manage Products</a>
                </li>
                <li>
                    <a href="admin_orders.php" class="nav-link link-dark">Manage Orders</a>
                </li>
                <li>
                    <a href="admin_customers.php" class="nav-link link-dark">Customer List</a>
                </li>
                <li>
                    <a href="admin_messages.php" class="nav-link link-dark">Support Messages</a>
                </li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="logout.php" class="d-flex align-items-center link-dark text-decoration-none"><strong>Sign out</strong></a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container-fluid p-5">
            <h1 class="mb-4">Admin Overview</h1>

            <!-- Dashboard Cards Row 1 -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <a href="#sales-chart" class="dashboard-card text-decoration-none">
                        <div class="card text-white bg-primary mb-3 h-100">
                            <div class="card-body">
                                <h5 class="card-title">💰 Total Revenue</h5>
                                <h2 class="display-4 fw-bold">RM <?php echo number_format($total_revenue, 2); ?></h2>
                                <p class="card-text">Lifetime earnings • View chart below</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#recent-orders" class="dashboard-card text-decoration-none">
                        <div class="card text-white bg-success mb-3 h-100">
                            <div class="card-body">
                                <h5 class="card-title">📦 Total Orders</h5>
                                <h2 class="display-4 fw-bold"><?php echo $total_orders; ?></h2>
                                <p class="card-text">All transactions • View details</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="admin_customers.php" class="dashboard-card text-decoration-none">
                        <div class="card text-white bg-info mb-3 h-100">
                            <div class="card-body">
                                <h5 class="card-title">👥 Total Customers</h5>
                                <h2 class="display-4 fw-bold"><?php echo $total_customers; ?></h2>
                                <p class="card-text">Registered users • Manage list</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Dashboard Cards Row 2 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <a href="admin_orders.php" class="dashboard-card text-decoration-none">
                        <div class="card text-white bg-warning mb-3 h-100">
                            <div class="card-body">
                                <h5 class="card-title">⏳ Pending Orders</h5>
                                <h2 class="display-4 fw-bold"><?php echo $pending_orders; ?></h2>
                                <p class="card-text">Awaiting processing • Take action</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="admin_products.php" class="dashboard-card text-decoration-none">
                        <div class="card text-white bg-secondary mb-3 h-100">
                            <div class="card-body">
                                <h5 class="card-title">🛢️ Products Listed</h5>
                                <h2 class="display-4 fw-bold"><?php echo $total_products; ?></h2>
                                <p class="card-text">Active inventory • Manage products</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="row mb-4" id="sales-chart">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header fw-bold">📊 Sales Performance (2024)</div>
                        <div class="card-body">
                            <canvas id="adminChart" style="max-height: 400px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="card shadow" id="recent-orders">
                <div class="card-header fw-bold bg-white">📋 Recent Orders</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders_result->num_rows > 0): ?>
                                    <?php while ($row = $orders_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td>RM <?php echo $row['total_price']; ?></td>
                                            <td>
                                                <?php 
                                                $badge_class = $row['status'] == 'Completed' ? 'bg-success' : 
                                                              ($row['status'] == 'Pending' ? 'bg-warning' : 'bg-secondary');
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $row['status']; ?></span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
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
    <script>
        const ctx = document.getElementById('adminChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Monthly Sales (RM)',
                    data: <?php echo json_encode($sales_data); ?>,
                    backgroundColor: '#1d3557',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    </script>
</body>
</html>