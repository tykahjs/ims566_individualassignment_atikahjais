<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Fetch User Points
$user_sql = "SELECT points FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_points = $stmt->get_result()->fetch_assoc()['points'];

// Fetch Rewards
$rewards_sql = "SELECT * FROM rewards";
$rewards_result = $conn->query($rewards_sql);

// Redemption Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['redeem_id'])) {
    $reward_id = $_POST['redeem_id'];
    $cost = $_POST['cost'];
    $reward_name = $_POST['reward_name'];

    if ($user_points >= $cost) {
        $update_points = $conn->prepare("UPDATE users SET points = points - ? WHERE id = ?");
        $update_points->bind_param("ii", $cost, $user_id);

        if ($update_points->execute()) {
            $insert_redemption = $conn->prepare("INSERT INTO redemptions (user_id, reward_name, points_spent) VALUES (?, ?, ?)");
            $insert_redemption->bind_param("isi", $user_id, $reward_name, $cost);
            $insert_redemption->execute();

            $success_msg = "Successfully redeemed $reward_name!";
            $user_points -= $cost;
        } else {
            $error_msg = "Database error.";
        }
    } else {
        $error_msg = "Insufficient points.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rewards - Stratos</title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Rewards Center</h1>
                <div class="bg-primary text-white px-4 py-2 rounded">
                    Your Points: <strong><?php echo $user_points; ?></strong>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php while ($row = $rewards_result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100 card-product">
                            <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['name']; ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo $row['name']; ?></h5>
                                <h3 class="text-primary fw-bold my-3"><?php echo $row['points_cost']; ?> pts</h3>

                                <form method="POST" action="">
                                    <input type="hidden" name="redeem_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="cost" value="<?php echo $row['points_cost']; ?>">
                                    <input type="hidden" name="reward_name" value="<?php echo $row['name']; ?>">

                                    <?php if ($user_points >= $row['points_cost']): ?>
                                        <button type="submit" class="btn btn-success w-100">Redeem Now</button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary w-100" disabled>Not Enough
                                            Points</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
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