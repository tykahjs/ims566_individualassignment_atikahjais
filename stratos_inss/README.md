# STRATOS - Smart Petrol Station Platform

## Project Overview
**Stratos** is a modern online platform designed for a petrol pump station. It allows customers to purchase **fuel, merchandise, and engine oil** seamlessly from their devices. The system supports both **Store Pickup** and **Home Delivery** options, providing flexibility for users.

The main goal of Stratos is to eliminate long queues and counter wait times. Customers can simply browse items, add them to their cart, and complete their purchase online. The application is fully responsive, ensuring a smooth experience on both **laptops and mobile devices**.

## Key Features

### Customer Features
*   **User Dashboard:** View account summary, points balance, and recent activities.
*   **Online Shop:** Browse and buy Fuel, 2T/4T Oil, and Merchandise.
*   **Smart Cart & Checkout:**
    *   Add multiple items to cart.
    *   Choose between **Home Delivery** or **Store Pickup**.
    *   Select payment methods (Online Banking, Card, E-Wallet).
*   **Order Tracking:** View order history and current status (Pending, Shipped, Ready for Pickup).
*   **Rewards System:** Earn points for every purchase.
*   **Contact Support:** Send inquiries or report issues directly to admin.

### Admin Features
*   **Admin Dashboard:** Visual analytics for Total Revenue, Orders, and Sales Charts.
*   **Product Management:** Add, edit, or delete products (Oil, Merch, etc.).
*   **Order Management:**
    *   View all customer orders.
    *   Update order status (e.g., *Pending* → *Shipped* → *Completed*).
    *   View delivery addresses and contact info.
*   **Customer Management:** View list of registered users.
*   **Support System:** Read and reply to customer support messages.

## Technology Stack
*   **Frontend:** HTML5, CSS3, Bootstrap 5.3 (Responsive Design)
*   **Backend:** PHP (Native)
*   **Database:** MySQL
*   **Charts:** Chart.js

## ⚙️ Installation Guide
1.  **Clone/Download** the project to your local server (e.g., `htdocs` in XAMPP).
2.  **Import Database:**
    *   Open phpMyAdmin.
    *   Create a database named `stratos_db`.
3.  **Configure Connection:**
    *   Ensure `conn/db_connect.php` has the correct database credentials.
4.  **Run:** Open your browser and visit `http://localhost/stratos_inss`.

## 📂 Project Structure
*   [index.php](cci:7://file:///c:/Users/User/.gemini/antigravity/scratch/stratos_inss/index.php:0:0-0:0) - Login/Landing page
*   [dashboard.php](cci:7://file:///c:/Users/User/.gemini/antigravity/scratch/stratos_inss/dashboard.php:0:0-0:0) - Customer main page
*   [shop.php](cci:7://file:///c:/Users/User/.gemini/antigravity/scratch/stratos_inss/shop.php:0:0-0:0) / [cart.php](cci:7://file:///c:/Users/User/.gemini/antigravity/scratch/stratos_inss/cart.php:0:0-0:0) / `checkout.php` - Shopping flow
*   [admin_dashboard.php](cci:7://file:///c:/Users/User/.gemini/antigravity/scratch/stratos_inss/admin_dashboard.php:0:0-0:0) - Admin control panel
*   `admin_orders.php` - Order management
*   `contact.php` - Customer support form

---
*Developed for UiTM IMS566 INDIVIDUAL ASSIGNMENT.*