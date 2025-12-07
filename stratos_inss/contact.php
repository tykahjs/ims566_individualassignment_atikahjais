<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'conn/db_connect.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // This part saves it to the database!
    $stmt = $conn->prepare("INSERT INTO messages (user_id, subject, message) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iss", $user_id, $subject, $message);
        
        if ($stmt->execute()) {
            $success_msg = "Thank you! Your message has been sent. We will contact you shortly.";
        } else {
            $error_msg = "Error saving message: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_msg = "Database error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Stratos</title>
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
                <li class="nav-item"><a href="dashboard.php" class="nav-link link-dark">Dashboard</a></li>
                <li><a href="fuel.php" class="nav-link link-dark">Pay for Fuel</a></li>
                <li><a href="shop.php" class="nav-link link-dark">Shop Products</a></li>
                <li><a href="rewards.php" class="nav-link link-dark">Rewards</a></li>
                <li><a href="orders.php" class="nav-link link-dark">My Orders</a></li>
                <li><a href="contact.php" class="nav-link active" aria-current="page">Contact Support</a></li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="logout.php" class="d-flex align-items-center link-dark text-decoration-none"><strong>Sign out</strong></a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container-fluid p-5">
            <h1 class="mb-4">📞 Contact Support</h1>

            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Contact Form -->
                <div class="col-md-7">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white fw-bold">Send us a Message</div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Subject</label>
                                    <select name="subject" class="form-select" required>
                                        <option value="">Choose a topic...</option>
                                        <option value="Order Issue">Problem with an Order</option>
                                        <option value="Payment Issue">Payment Issue</option>
                                        <option value="Fuel Inquiry">Fuel Inquiry</option>
                                        <option value="General">General Question</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Your Message</label>
                                    <textarea name="message" class="form-control" rows="5" placeholder="Describe your issue or question here..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary px-4">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-md-5">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-dark text-white fw-bold">Get in Touch</div>
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <span class="fs-4 me-3">📍</span>
                                <div>
                                    <h6 class="fw-bold mb-1">Visit Us</h6>
                                    <p class="text-muted mb-0">Stratos Station, Jalan Tun Razak,<br>50400 Kuala Lumpur, Malaysia</p>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-start mb-3">
                                <span class="fs-4 me-3">📞</span>
                                <div>
                                    <h6 class="fw-bold mb-1">Call Us</h6>
                                    <p class="text-muted mb-0">+60 3-1234 5678</p>
                                    <small class="text-success">Available 24/7</small>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-start mb-3">
                                <span class="fs-4 me-3">✉️</span>
                                <div>
                                    <h6 class="fw-bold mb-1">Email Us</h6>
                                    <p class="text-muted mb-0">support@stratos.com.my</p>
                                </div>
                            </div>
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
</body>
</html>