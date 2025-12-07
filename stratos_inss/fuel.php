<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

$success_msg = '';
$error_msg = '';

// Fuel Prices
$prices = [
    'BUDI95' => 1.99,
    'RON95' => 2.63,
    'RON97' => 3.26,
    'Diesel' => 3.08
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $fuel_type = $_POST['fuel_type'];
    $amount = $_POST['amount'];
    $pump_no = $_POST['pump_no'];
    $payment_method = $_POST['payment_method'];

    if ($amount > 0) {
        $liters = $amount / $prices[$fuel_type];

        $sql = "INSERT INTO orders (user_id, total_price, status, type) VALUES (?, ?, 'Completed', 'Fuel')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $user_id, $amount);

        if ($stmt->execute()) {
            $points = floor($amount);
            $update_points = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
            $update_points->bind_param("ii", $points, $user_id);
            $update_points->execute();

            $success_msg = "Payment Successful via " . strtoupper(str_replace('_', ' ', $payment_method)) . "! You filled " . number_format($liters, 2) . "L of $fuel_type at Pump $pump_no. You earned $points points.";
        } else {
            $error_msg = "Transaction failed.";
        }
    } else {
        $error_msg = "Please enter a valid amount.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay for Fuel - Stratos</title>
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
            <h1 class="mb-4">Pay at Pump</h1>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="pump_no" class="form-label">Select Pump Number</label>
                                    <select class="form-select" id="pump_no" name="pump_no" required>
                                        <option value="1">Pump 1</option>
                                        <option value="2">Pump 2</option>
                                        <option value="3">Pump 3</option>
                                        <option value="4">Pump 4</option>
                                        <option value="5">Pump 5</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="fuel_type" class="form-label">Select Fuel Type</label>
                                    <select class="form-select" id="fuel_type" name="fuel_type" required>
                                        <option value="BUDI95">BUDI95 (RM 1.99/L)</option>
                                        <option value="RON95">RON95 (RM 2.63/L)</option>
                                        <option value="RON97">RON97 (RM 3.26/L)</option>
                                        <option value="Diesel">Diesel (RM 3.08/L)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (RM)</label>
                                    <input type="number" class="form-control" id="amount" name="amount" min="1" step="1"
                                        required>
                                </div>
                                <div class="mb-4">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="credit_card">Credit/Debit Card</option>
                                        <option value="qr_pay">QR Pay (DuitNow/GrabPay)</option>
                                        <option value="ewallet">TNG eWallet</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-stratos w-100 btn-lg">Pay Now</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-white bg-secondary mb-3">
                        <div class="card-header">How it works</div>
                        <div class="card-body">
                            <h5 class="card-title">Skip the Counter!</h5>
                            <p class="card-text">
                                1. Park at the pump.<br>
                                2. Select your pump number here.<br>
                                3. Select Payment Method.<br>
                                4. Pay online & Start refueling!<br><br>
                                <strong>Bonus:</strong> Points are automatically credited to your account.
                            </p>
                        </div>
                    </div>
                </div>
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