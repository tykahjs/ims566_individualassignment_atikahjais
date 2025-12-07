<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get total spending this month
$current_month = date('Y-m');
$spending_query = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE user_id = $user_id AND DATE_FORMAT(created_at, '%Y-%m') = '$current_month'");
$monthly_spending = $spending_query->fetch_assoc()['total'] ?? 0;

// Get total orders count
$orders_count_query = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id");
$total_orders = $orders_count_query->fetch_assoc()['count'];

// Get recent orders
$recent_orders_query = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// Calculate points needed for next reward tier
$current_points = $user['points'];
$next_tier = ceil($current_points / 100) * 100;
$points_needed = $next_tier - $current_points;
if ($points_needed == 0) $points_needed = 100;

// Get spending data for chart (last 6 months)
$months = [];
$spending = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_label = date('M', strtotime("-$i months"));
    $months[] = $month_label;
    
    $month_spending = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE user_id = $user_id AND DATE_FORMAT(created_at, '%Y-%m') = '$month'");
    $spending[] = floatval($month_spending->fetch_assoc()['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Stratos</title>
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
        .quick-action-btn {
            transition: all 0.2s;
        }
        .quick-action-btn:hover {
            transform: scale(1.02);
        }
    </style>
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
            <h1 class="mb-4">Welcome back, <?php echo htmlspecialchars($user['username']); ?>! 👋</h1>

            <!-- Dashboard Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <a href="rewards.php" class="dashboard-card text-decoration-none">
                        <div class="card text-white bg-primary mb-3 h-100">
                            <div class="card-body">
                                <h6 class="card-title">💎 Stratos Points</h6>
                                <h2 class="display-5 fw-bold"><?php echo $user['points']; ?></h2>
                                <p class="card-text mb-2"><small><?php echo $points_needed; ?> pts to next tier</small></p>
                                <span class="badge bg-light text-primary">Redeem Now →</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="orders.php" class="dashboard-card text-decoration-none">
                        <div class="card text-white bg-success mb-3 h-100">
                            <div class="card-body">
                                <h6 class="card-title">📦 Total Orders</h6>
                                <h2 class="display-5 fw-bold"><?php echo $total_orders; ?></h2>
                                <p class="card-text mb-2"><small>All time purchases</small></p>
                                <span class="badge bg-light text-success">View History →</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3 h-100">
                        <div class="card-body">
                            <h6 class="card-title">💰 This Month</h6>
                            <h2 class="display-5 fw-bold">RM <?php echo number_format($monthly_spending, 2); ?></h2>
                            <p class="card-text mb-2"><small>Total spending</small></p>
                            <span class="badge bg-light text-warning"><?php echo date('F Y'); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3 h-100">
                        <div class="card-body">
                            <h6 class="card-title">⭐ Member Since</h6>
                            <h2 class="display-6 fw-bold"><?php echo date('M Y', strtotime($user['created_at'])); ?></h2>
                            <p class="card-text mb-2"><small>Active member</small></p>
                            <span class="badge bg-light text-info">Premium Status</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white fw-bold">⚡ Quick Actions</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="fuel.php" class="btn btn-danger btn-lg w-100 quick-action-btn">⛽ Pay for Fuel</a>
                                </div>
                                <div class="col-md-3">
                                    <a href="shop.php" class="btn btn-primary btn-lg w-100 quick-action-btn">🛒 Shop Products</a>
                                </div>
                                <div class="col-md-3">
                                    <a href="rewards.php" class="btn btn-warning btn-lg w-100 quick-action-btn">🎁 View Rewards</a>
                                </div>
                                <div class="col-md-3">
                                    <a href="orders.php" class="btn btn-outline-secondary btn-lg w-100 quick-action-btn">📋 My Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Chart -->
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-white fw-bold">📊 Your Spending Overview (Last 6 Months)</div>
                        <div class="card-body">
                            <canvas id="spendingChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Fuel Prices -->
                <div class="col-md-4">
                    <a href="fuel.php" class="text-decoration-none">
                        <div class="card border-primary mb-3 h-100 dashboard-card">
                            <div class="card-header bg-primary text-white fw-bold">⛽ Today's Fuel Prices</div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>BUDI95</span>
                                        <span class="badge bg-success rounded-pill">RM 1.99</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>RON95</span>
                                        <span class="badge bg-primary rounded-pill">RM 2.63</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>RON97</span>
                                        <span class="badge bg-warning text-dark rounded-pill">RM 3.26</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Diesel</span>
                                        <span class="badge bg-dark rounded-pill">RM 3.08</span>
                                    </li>
                                </ul>
                                <div class="mt-3 text-center">
                                    <span class="badge bg-primary">Click to Pay Now →</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-white fw-bold">🕒 Recent Activity</div>
                        <div class="card-body">
                            <?php if ($recent_orders_query->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Points Earned</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($order = $recent_orders_query->fetch_assoc()): ?>
                                                <tr>
                                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                                    <td>RM <?php echo number_format($order['total_price'], 2); ?></td>
                                                    <td>
                                                        <?php 
                                                        $badge_class = $order['status'] == 'Completed' ? 'bg-success' : 
                                                                      ($order['status'] == 'Pending' ? 'bg-warning' : 'bg-secondary');
                                                        ?>
                                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $order['status']; ?></span>
                                                    </td>
                                                    <td><span class="badge bg-primary"><?php echo $order['points_earned']; ?> pts</span></td>
                                                    <td><?php echo date('d M Y, g:i A', strtotime($order['created_at'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="orders.php" class="btn btn-outline-primary">View All Orders →</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">No orders yet. Start shopping to see your activity here!</p>
                                    <a href="shop.php" class="btn btn-primary">Browse Products</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctx = document.getElementById('spendingChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Spending (RM)',
                    data: <?php echo json_encode($spending); ?>,
                    borderColor: '#e63946',
                    backgroundColor: 'rgba(230, 57, 70, 0.2)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Your Fuel & Product Expenses',
                        font: { size: 16 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'RM ' + value;
                            }
                        }
                    }
                }
            }
        });
    </script>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> Stratos INSS. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>

</html>