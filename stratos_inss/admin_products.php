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

$success_msg = '';
$error_msg = '';

// Handle Add Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image = $_POST['image'];

    $sql = "INSERT INTO products (name, category, price, description, image) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdss", $name, $category, $price, $description, $image);

    if ($stmt->execute()) {
        $success_msg = "Product added successfully!";
    } else {
        $error_msg = "Error adding product.";
    }
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: admin_products.php");
    exit();
}

// Fetch Products
$products_result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Stratos Admin</title>
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
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link link-dark">Overview</a>
                </li>
                <li>
                    <a href="admin_products.php" class="nav-link active" aria-current="page">Manage Products</a>
                </li>
                <li>
                    <a href="admin_orders.php" class="nav-link link-dark">Manage Orders</a>
                </li>
                <li>
                    <a href="admin_customers.php" class="nav-link link-dark">Customer List</a>
                </li>
                <li><a href="admin_messages.php" class="nav-link link-dark">Support Messages</a></li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="logout.php" class="d-flex align-items-center link-dark text-decoration-none"><strong>Sign out</strong></a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container-fluid p-5">
            <h1 class="mb-4">Product Management</h1>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <!-- Add Product Form -->
            <div class="card shadow mb-5">
                <div class="card-header bg-primary text-white fw-bold">Add New Product</div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="2T">2T Oil</option>
                                    <option value="4T">4T Oil</option>
                                    <option value="Merchandise">Merchandise</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (RM)</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image Path (e.g., sources/oil.jpg)</label>
                                <input type="text" name="image" class="form-control" value="https://placehold.co/300x300?text=New+Product">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <button type="submit" name="add_product" class="btn btn-success">Add Product</button>
                    </form>
                </div>
            </div>

            <!-- Product List -->
            <div class="card shadow">
                <div class="card-header fw-bold">Current Inventory</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $products_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><img src="<?php echo $row['image']; ?>" style="height: 50px;"></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo $row['category']; ?></span></td>
                                        <td>RM <?php echo $row['price']; ?></td>
                                        <td>
                                            <a href="admin_products.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                                        </td>
                                    </tr>
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