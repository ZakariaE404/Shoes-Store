<?php
session_start();

require 'config.php'; // Uses $pdo from config.php

$adminUsername = "zakaria";
$adminPassword = "spongebob123";

// --- Helper Functions ---
function clean($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// --- Authentication Logic ---

// Login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['user_id'] = 1; // Dummy ID
        $_SESSION['login_time'] = time();
        // Regenerate session ID for security
        session_regenerate_id(true);
        header("Location: my_window.php");
        exit;
    } else {
        $loginError = "Invalid username or password.";
    }
}

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: my_window.php");
    exit;
}

// Session Timeout (Optional Security Feature - 30 mins)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: my_window.php?timeout=1");
    exit;
}
$_SESSION['login_time'] = time();

// Require Login for Dashboard
if (!isset($_SESSION['user_id'])) {
    // Show Login Page
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - PHANTOM</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    </head>

    <body class="bg-slate-950 flex items-center justify-center min-h-screen font-sans antialiased">
        <div class="bg-slate-900 p-8 rounded-lg shadow-2xl w-full max-w-md border border-slate-800">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black text-white italic tracking-tighter uppercase">Phantom Admin</h2>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-2">Secure Access Portal</p>
            </div>

            <?php if (isset($loginError)): ?>
                <div class="bg-red-500/10 border border-red-500 text-red-500 p-3 rounded mb-6 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php echo clean($loginError); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['timeout'])): ?>
                <div class="bg-orange-500/10 border border-orange-500 text-orange-500 p-3 rounded mb-6 text-sm">
                    Session timed out. Please login again.
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Username</label>
                    <input type="text" name="username"
                        class="w-full bg-slate-950 border border-slate-700 rounded p-3 text-white focus:outline-none focus:border-orange-600 transition"
                        required>
                </div>
                <div class="mb-6">
                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Password</label>
                    <input type="password" name="password"
                        class="w-full bg-slate-950 border border-slate-700 rounded p-3 text-white focus:outline-none focus:border-orange-600 transition"
                        required>
                </div>
                <button type="submit" name="login"
                    class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-3 px-4 rounded transition uppercase tracking-widest shadow-lg shadow-orange-900/20">
                    Sign In
                </button>
            </form>
        </div>
    </body>

    </html>
    <?php
    exit;
}

// --- Dashboard Logic (Only reachable if logged in) ---

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    // Delete Order
    if (isset($_POST['delete_order'])) {
        $id = $_POST['order_id'];
        // Secure Prepared Statement (PDO)
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);

        // Redirect to prevent form resubmission
        header("Location: my_window.php");
        exit;
    }
}

// Fetch Data

// 1. Visitors Stats
$visitorCount = 0;
try {
    $result = $pdo->query("SELECT COUNT(DISTINCT ip_address) as count FROM site_visitors");
    $row = $result->fetch();
    if ($row) {
        $visitorCount = $row['count'];
    }
} catch (Exception $e) {
    // Table might not exist, ignore
}

// 2. Orders
$orders = [];
try {
    $result = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    $orders = $result->fetchAll();
} catch (Exception $e) {
    // Handle error
}

// 3. Products (Read Only)
$products = [];
try {
    $result = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $result->fetchAll();
} catch (Exception $e) {
    // Handle error
}

// No need to explicitly close PDO connection
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PHANTOM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this order? This action cannot be undone.");
        }
    </script>
</head>

<body class="bg-slate-950 text-slate-300 font-sans antialiased selection:bg-orange-500 selection:text-white">

    <!-- Top Navigation -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50 backdrop-blur-md bg-opacity-80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 bg-orange-600 rounded flex items-center justify-center text-white font-black italic">
                        P</div>
                    <span class="text-white font-black text-xl italic tracking-tighter">PHANTOM <span
                            class="text-slate-600 not-italic font-medium text-sm ml-1 tracking-widest uppercase">Dashboard</span></span>
                </div>
                <div class="flex items-center gap-6">
                    <div class="hidden md:block">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Logged in as</span>
                        <span class="text-sm font-bold text-white ml-1">Administrator</span>
                    </div>
                    <a href="?action=logout"
                        class="group flex items-center gap-2 text-xs font-bold uppercase tracking-widest bg-slate-800 hover:bg-red-900/20 hover:text-red-500 text-slate-400 px-4 py-2 rounded-full border border-slate-700 hover:border-red-900 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Stat Card 1 -->
            <div
                class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Unique Visitors</h3>
                <div class="flex items-end gap-2">
                    <p class="text-4xl font-black text-white tracking-tight">
                        <?php echo clean($visitorCount); ?>
                    </p>
                    <span
                        class="text-emerald-500 text-xs font-bold mb-1.5 bg-emerald-500/10 px-1.5 py-0.5 rounded">Live</span>
                </div>
            </div>

            <!-- Stat Card 2 -->
            <div
                class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-24 h-24 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Total Orders</h3>
                <div class="flex items-end gap-2">
                    <p class="text-4xl font-black text-orange-500 tracking-tight">
                        <?php echo count($orders); ?>
                    </p>
                    <span class="text-slate-600 text-xs font-bold mb-1.5">All time</span>
                </div>
            </div>

            <!-- Stat Card 3 -->
            <div
                class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-xl relative overflow-hidden group hover:border-slate-700 transition-all">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-24 h-24 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Inventory Items</h3>
                <div class="flex items-end gap-2">
                    <p class="text-4xl font-black text-blue-500 tracking-tight">
                        <?php echo count($products); ?>
                    </p>
                    <span class="text-slate-600 text-xs font-bold mb-1.5">Active</span>
                </div>
            </div>
        </div>

        <div class="space-y-12">

            <!-- Orders Section (Full Width, Primary Focus) -->
            <section>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white uppercase tracking-tight italic">Incoming <span
                            class="text-orange-600">Orders</span></h2>
                    <div
                        class="bg-slate-900 px-3 py-1 rounded text-xs font-bold text-slate-500 border border-slate-800 uppercase tracking-widest">
                        Real-time Data
                    </div>
                </div>

                <div
                    class="bg-slate-900/50 backdrop-blur rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-400">
                            <thead
                                class="bg-slate-950/50 border-b border-slate-800 text-xs uppercase text-slate-500 font-extrabold tracking-wider">
                                <tr>
                                    <th class="px-8 py-5">Order ID</th>
                                    <th class="px-8 py-5">Customer Details</th>
                                    <th class="px-8 py-5">Product Info</th>
                                    <th class="px-8 py-5">Total</th>
                                    <th class="px-8 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php if (count($orders) > 0): ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr class="hover:bg-slate-800/30 transition-colors group">
                                            <td class="px-8 py-6 font-mono text-xs text-slate-500 group-hover:text-slate-300">#
                                                <?php echo str_pad(clean($order['id']), 6, '0', STR_PAD_LEFT); ?>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="font-bold text-white text-base mb-1">
                                                    <?php echo clean($order['client_fullname'] ?? 'N/A'); ?>
                                                </div>
                                                <div class="text-xs font-medium text-slate-500 flex items-center gap-2">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                                        </path>
                                                    </svg>
                                                    <?php echo clean($order['client_phone'] ?? ''); ?>
                                                </div>
                                                <?php if (!empty($order['client_address'])): ?>
                                                    <div class="text-xs text-slate-600 mt-1 truncate max-w-xs">
                                                        <?php echo clean($order['client_address']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="text-slate-300 font-medium">
                                                    <?php echo clean($order['product_name'] ?? 'N/A'); ?>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold leading-4 bg-orange-600/10 text-orange-500 border border-orange-600/20">
                                                    $
                                                    <?php echo number_format((float) ($order['product_price'] ?? 0), 2); ?>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <form method="POST" onsubmit="return confirmDelete()" class="inline-block">
                                                    <input type="hidden" name="csrf_token"
                                                        value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="delete_order" value="1">
                                                    <button type="submit"
                                                        class="text-slate-500 hover:text-red-500 transition-colors p-2 rounded hover:bg-red-500/10"
                                                        title="Delete Order">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-8 py-16 text-center">
                                            <div
                                                class="flex flex-col items-center justify-center text-slate-500 opacity-50">
                                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                <p class="text-sm font-bold uppercase tracking-widest">No orders received
                                                    yet</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Product Inventory Section (Read Only, Compact) -->
            <section class="opacity-75 hover:opacity-100 transition-opacity">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-400 uppercase tracking-tight">Active Inventory</h2>
                </div>

                <div class="bg-slate-900/30 rounded-2xl border border-slate-800/50 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-500">
                            <thead class="bg-slate-950/30 text-xs uppercase text-slate-600 font-bold tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Preview</th>
                                    <th class="px-6 py-4">Product Name</th>
                                    <th class="px-6 py-4">Description</th>
                                    <th class="px-6 py-4 text-right">Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/30">
                                <?php if (count($products) > 0): ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr class="hover:bg-slate-800/30 transition">
                                            <td class="px-6 py-3">
                                                <?php if (!empty($product['image_url'])): ?>
                                                    <img src="<?php echo clean($product['image_url']); ?>" alt="Product"
                                                        class="h-8 w-8 object-cover rounded border border-slate-700/50 grayscale hover:grayscale-0 transition-all">
                                                <?php else: ?>
                                                    <div
                                                        class="h-8 w-8 bg-slate-800 rounded flex items-center justify-center text-[10px] uppercase">
                                                        N/A</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-3 font-medium text-slate-400">
                                                <?php echo clean($product['title']); ?>
                                            </td>
                                            <td class="px-6 py-3 text-xs text-slate-600 max-w-xs truncate">
                                                <?php echo clean($product['description']); ?>
                                            </td>
                                            <td class="px-6 py-3 text-right font-mono text-slate-500">$
                                                <?php echo clean($product['price']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center italic text-xs">Inventory empty.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>

        <!-- Footer -->
        <footer
            class="mt-12 border-t border-slate-800/50 pt-8 flex items-center justify-between text-xs text-slate-600 uppercase tracking-widest font-bold">
            <div>&copy;
                <?php echo date('Y'); ?> Phantom Store
            </div>
            <div>Secure Admin Panel v1.2</div>
        </footer>

    </div>

</body>

</html>