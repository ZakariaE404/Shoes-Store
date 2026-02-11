<?php
// 1. Database Configuration
$host = "localhost";
$user = "root";      // Default for XAMPP/WAMP
$pass = "";          // Default for XAMPP/WAMP
$db   = "your_database_name"; // REPLACE WITH YOUR ACTUAL DATABASE NAME

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Check if the form was submitted
if (isset($_POST["done"])) {
    
    // Get data from POST (matching your HTML 'name' attributes)
    $productName  = $_POST["product-name"];
    $productPrice = $_POST["product-price"];
    $clientName   = $_POST["client-name"];
    $clientPhone  = $_POST["client-phone"];
    $clientAdr    = $_POST["client-adress"]; // Matches your HTML spelling

    // 3. Secure SQL Injection Protection (Prepared Statement)
    // We use "ssssd" for: string, string, string, string, double/decimal
    $stmt = $conn->prepare("INSERT INTO orders (client_fullname, client_phone, client_address, product_name, product_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssd", $clientName, $clientPhone, $clientAdr, $productName, $productPrice);

    if ($stmt->execute()) {
        // Success! Redirect back to home or show a message
        echo "Order placed successfully!";
        // header("Location: index.html?status=success"); // Optional redirect
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>