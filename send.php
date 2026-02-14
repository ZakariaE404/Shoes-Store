<?php
// send.php
require 'config.php'; // Includes database connection ($pdo)

// 1. Check if the form was submitted
if (isset($_POST["done"])) {

    // Get and sanitize data from POST
    $productName = trim($_POST["product-name"]);
    $productPrice = trim($_POST["product-price"]);
    $clientName = trim($_POST["client-name"]);
    $clientPhone = trim($_POST["client-phone"]);
    $clientAdr = trim($_POST["client-adress"]);

    // Basic validation
    if (empty($productName) || empty($productPrice) || empty($clientName) || empty($clientPhone) || empty($clientAdr)) {
        die("Error: All fields are required!");
    }

    // Validate price
    if (!is_numeric($productPrice) || $productPrice <= 0) {
        die("Error: Invalid product price!");
    }

    try {
        // 2. Prepared Statement (Protection against SQL Injection) using PDO
        // Note: Column names must match your database. send.php previously used:
        // client_fullname, client_phone, client_address, product_name, product_price
        $sql = "INSERT INTO orders (client_fullname, client_phone, client_address, product_name, product_price) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // PDO executes with an array of values
        if ($stmt->execute([$clientName, $clientPhone, $clientAdr, $productName, $productPrice])) {
            $order_id = $pdo->lastInsertId();

            // Success Page
            ?>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Order Confirmed - PHANTOM</title>
                <script src="https://cdn.tailwindcss.com"></script>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
                    rel="stylesheet">
                <link rel="stylesheet" href="style.css">
            </head>

            <body class="bg-slate-950 antialiased">

                <div class="min-h-screen flex items-center justify-center p-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-lg shadow-2xl max-w-2xl w-full overflow-hidden">

                        <!-- Success Header -->
                        <div class="bg-gradient-to-r from-orange-600 to-orange-500 p-8 text-center">
                            <svg class="w-20 h-20 text-white mx-auto mb-4 animate-bounce" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h1 class="text-4xl font-black text-white uppercase italic tracking-tighter">Order Confirmed!</h1>
                            <p class="text-orange-100 text-sm font-bold uppercase tracking-widest mt-2">Thank you for your purchase
                            </p>
                        </div>

                        <!-- Order Details -->
                        <div class="p-8">
                            <div class="mb-6 p-4 bg-slate-800 border-l-4 border-orange-600 rounded">
                                <p class="text-xs font-bold text-orange-500 uppercase tracking-widest mb-1">Order Number</p>
                                <p class="text-2xl font-black text-white">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?>
                                </p>
                            </div>

                            <div class="space-y-4 mb-8">
                                <div class="flex justify-between items-start border-b border-slate-800 pb-4">
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Customer Name</p>
                                        <p class="text-white font-bold text-lg"><?php echo htmlspecialchars($clientName); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Phone</p>
                                        <p class="text-white font-bold"><?php echo htmlspecialchars($clientPhone); ?></p>
                                    </div>
                                </div>

                                <div class="border-b border-slate-800 pb-4">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Delivery Address</p>
                                    <p class="text-white"><?php echo nl2br(htmlspecialchars($clientAdr)); ?></p>
                                </div>

                                <div class="bg-slate-800 p-6 rounded-lg">
                                    <div class="flex justify-between items-center mb-3">
                                        <p class="text-xs font-bold text-orange-500 uppercase tracking-widest">Product</p>
                                        <span
                                            class="bg-orange-600/10 text-orange-500 px-3 py-1 rounded text-xs font-bold uppercase tracking-widest border border-orange-600/20">COD</span>
                                    </div>
                                    <p class="text-white font-black text-2xl mb-2 uppercase italic">
                                        <?php echo htmlspecialchars($productName); ?></p>
                                    <p class="text-3xl font-black text-white">$<?php echo number_format($productPrice, 2); ?></p>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-orange-600/10 border border-orange-600/20 p-4 rounded-lg mb-8">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-orange-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-orange-500 font-bold text-sm mb-1">What's Next?</p>
                                        <p class="text-slate-300 text-sm leading-relaxed">Our team will contact you shortly to
                                            confirm your delivery details. You'll pay cash when you receive your order.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-3">
                                <a href="index.html"
                                    class="w-full bg-orange-600 text-white py-4 rounded-md font-black text-lg hover:bg-orange-500 transition-all uppercase tracking-tighter text-center custom-shadow">
                                    Back to Home
                                </a>
                                <a href="index.html#products"
                                    class="w-full bg-transparent border border-slate-700 text-white py-4 rounded-md font-bold hover:bg-slate-800 transition-all uppercase tracking-widest text-center">
                                    Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </body>

            </html>
            <?php

        } else {
            echo "Error inserting order.";
        }

    } catch (PDOException $e) {
        // In production, log this error instead of showing it
        echo "Database Error: " . $e->getMessage();
    }

} else {
    // If accessed directly without form submission
    header("Location: index.html");
    exit();
}
?>