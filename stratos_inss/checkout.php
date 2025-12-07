<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $delivery_method = $_POST['delivery_method'];
    $payment_method = $_POST['payment_method'];
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $phone = $_POST['phone'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    // Calculate points earned (1 point per RM)
    $points_earned = floor($total);
    
    // Insert order
    $order_sql = "INSERT INTO orders (user_id, total_price, status, type, delivery_method, delivery_address, phone, payment_method, notes, points_earned, created_at) 
                  VALUES (?, ?, 'Pending', 'Shop', ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("idsssssi", $user_id, $total, $delivery_method, $address, $phone, $payment_method, $notes, $points_earned);
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        
        // Update user points
        $conn->query("UPDATE users SET points = points + $points_earned WHERE id = $user_id");
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Redirect to confirmation
        header("Location: order_confirmation.php?order_id=$order_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Stratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .delivery-option {
            cursor: pointer;
            transition: all 0.2s;
        }
        .delivery-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .delivery-option input[type="radio"]:checked + label {
            border-color: var(--primary-color);
            background-color: rgba(230, 57, 70, 0.05);
        }
    </style>
</head>
<body>
    <div class="container p-5">
        <h1 class="mb-4">🛒 Checkout</h1>
        <a href="cart.php" class="btn btn-outline-secondary mb-3">← Back to Cart</a>

        <div class="row">
            <!-- Checkout Form -->
            <div class="col-md-8">
                <form method="POST" action="" id="checkoutForm">
                    <!-- Delivery Method -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-white fw-bold">📦 Delivery Method</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="radio" name="delivery_method" value="Delivery" id="delivery" class="d-none" required>
                                    <label for="delivery" class="delivery-option card h-100 p-3 border-2">
                                        <div class="text-center">
                                            <h3>🚚</h3>
                                            <h5>Home Delivery</h5>
                                            <p class="text-muted small mb-0">Delivered to your address</p>
                                            <p class="text-muted small">Est. 2-3 business days</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" name="delivery_method" value="Pickup" id="pickup" class="d-none" required>
                                    <label for="pickup" class="delivery-option card h-100 p-3 border-2">
                                        <div class="text-center">
                                            <h3>🏪</h3>
                                            <h5>Store Pickup</h5>
                                            <p class="text-muted small mb-0">Collect at our store</p>
                                            <p class="text-muted small">Ready in 1-2 hours</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="card shadow mb-4" id="addressSection" style="display: none;">
                        <div class="card-header bg-white fw-bold">📍 Delivery Address</div>
                        <div class="card-body">
                            <textarea name="address" id="addressField" class="form-control" rows="3" placeholder="Street address, city, state, postcode"></textarea>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-white fw-bold">📞 Contact Information</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" placeholder="01X-XXX XXXX" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions?"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-white fw-bold">💳 Payment Method</div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="Online Banking" id="online" required>
                                <label class="form-check-label" for="online">Online Banking / FPX</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="Credit Card" id="card">
                                <label class="form-check-label" for="card">Credit / Debit Card</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="E-Wallet" id="ewallet">
                                <label class="form-check-label" for="ewallet">E-Wallet (Touch 'n Go, GrabPay)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="Cash on Delivery" id="cod">
                                <label class="form-check-label" for="cod">Cash on Delivery / Pickup</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100">
                        Place Order - RM <?php echo number_format($total, 2); ?>
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="col-md-4">
                <div class="card shadow sticky-top" style="top: 20px;">
                    <div class="card-header bg-white fw-bold">📋 Order Summary</div>
                    <div class="card-body">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="d-flex justify-content-between mb-3">
                                <span><?php echo $item['name']; ?></span>
                                <span class="fw-bold">RM <?php echo number_format($item['price'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>RM <?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery:</span>
                            <span class="text-success">FREE</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-danger">RM <?php echo number_format($total, 2); ?></strong>
                        </div>
                        <div class="alert alert-info small mb-0">
                            <strong>💎 You'll earn <?php echo floor($total); ?> points!</strong>
                        </div>
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
        document.querySelectorAll('input[name="delivery_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const addressSection = document.getElementById('addressSection');
                const addressField = document.getElementById('addressField');
                
                if (this.value === 'Delivery') {
                    addressSection.style.display = 'block';
                    addressField.required = true;
                } else {
                    addressSection.style.display = 'none';
                    addressField.required = false;
                }
            });
        });
    </script>
</body>
</html>