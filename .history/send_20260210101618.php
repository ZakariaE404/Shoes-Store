<?php
// send.php

// Database configuration
$host = 'localhost';
$dbname = 'your_database_name';  // ← Change this
$username = 'your_username';      // ← Change this
$password = 'your_password';      // ← Change this

// Create connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if form is submitted
if(isset($_POST['done'])) {
    
    // Get form data
    $client_name = trim($_POST['client-name']);
    $client_phone = trim($_POST['client-phone']);
    $client_address = trim($_POST['client-adress']);
    $product_name = trim($_POST['product-name']);
    $product_price = trim($_POST['product-price']);
    
    // Basic validation
    if(empty($client_name) || empty($client_phone) || empty($client_address) || empty($product_name) || empty($product_price)) {
        die("Error: All fields are required!");
    }
    
    try {
        // Prepare SQL statement
        $sql = "INSERT INTO orders (client_fullname, client_phone, client_address, product_name, product_price) 
                VALUES (:client_name, :client_phone, :client_address, :product_name, :product_price)";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':client_name', $client_name, PDO::PARAM_STR);
        $stmt->bindParam(':client_phone', $client_phone, PDO::PARAM_STR);
        $stmt->bindParam(':client_address', $client_address, PDO::PARAM_STR);
        $stmt->bindParam(':product_name', $product_name, PDO::PARAM_STR);
        $stmt->bindParam(':product_price', $product_price, PDO::PARAM_STR);
        
        // Execute
        $stmt->execute();
        $order_id = $conn->lastInsertId();
        
        // Success - redirect back to index with success message
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Order Confirmed</title>
            <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-slate-900 min-h-screen flex items-center justify-center p-4'>
            <div class='bg-slate-800 p-8 rounded-lg shadow-xl max-w-md w-full border border-slate-700'>
                <div class='text-center'>
                    <svg class='w-16 h-16 text-green-500 mx-auto mb-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                    </svg>
                    <h1 class='text-2xl font-bold text-white mb-2'>Order Confirmed!</h1>
                    <p class='text-slate-400 mb-6'>Order #" . $order_id . " placed successfully.</p>
                    <div class='bg-slate-900 p-4 rounded-md mb-6 text-left'>
                        <p class='text-slate-300 mb-2'><span class='font-bold text-orange-500'>Name:</span> " . htmlspecialchars($client_name) . "</p>
                        <p class='text-slate-300 mb-2'><span class='font-bold text-orange-500'>Phone:</span> " . htmlspecialchars($client_phone) . "</p>
                        <p class='text-slate-300 mb-2'><span class='font-bold text-orange-500'>Product:</span> " . htmlspecialchars($product_name) . "</p>
                        <p class='text-slate-300'><span class='font-bold text-orange-500'>Price:</span> $" . number_format($product_price, 2) . "</p>
                    </div>
                    <a href='index.html' class='inline-block bg-orange-600 text-white px-6 py-3 rounded-md font-bold hover:bg-orange-500 transition-all'>
                        Back to Home
                    </a>
                </div>
            </div>
        </body>
        </html>";
        
    } catch(PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
    
} else {
    header("Location: index.html");
    exit();
}

$conn = null;
?>