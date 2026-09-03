<?php

include "../config/database.php";
include "../config/session.php";

// Only admin can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Count total halls
$totalHalls = $db->halls->countDocuments();

// Count registered citizens
$totalUsers = $db->users->countDocuments([
    "role" => "citizen"
]);

// Count all bookings
$totalBookings = $db->bookings->countDocuments();

// Count pending bookings
$pendingBookings = $db->bookings->countDocuments([
    "status" => "pending"
]);

// Count approved bookings
$approvedBookings = $db->bookings->countDocuments([
    "status" => "approved"
]);

// Count rejected bookings
$rejectedBookings = $db->bookings->countDocuments([
    "status" => "rejected"
]);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Admin Dashboard - Harmony Halls</title>

    <!-- Connect CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

    <div class="container">

        <h1>
            Welcome,
            <?php echo htmlspecialchars($_SESSION["name"]); ?>!
        </h1>

        <h2>Admin Dashboard</h2>

        <hr>

        <h3>System Statistics</h3>

        <div class="stats">

            <div class="stat-card">
                <h2><?php echo $totalHalls; ?></h2>
                <p>Total Halls</p>
            </div>

            <div class="stat-card">
                <h2><?php echo $totalUsers; ?></h2>
                <p>Registered Customers</p>
            </div>

            <div class="stat-card">
                <h2><?php echo $totalBookings; ?></h2>
                <p>Total Bookings</p>
            </div>

            <div class="stat-card">
                <h2><?php echo $pendingBookings; ?></h2>
                <p>Pending Bookings</p>
            </div>

            <div class="stat-card">
                <h2><?php echo $approvedBookings; ?></h2>
                <p>Approved Bookings</p>
            </div>

            <div class="stat-card">
                <h2><?php echo $rejectedBookings; ?></h2>
                <p>Rejected Bookings</p>
            </div>

        </div>

        <hr>

        <h3>Hall Management</h3>

        <ul>

            <li>
                <a href="manage-halls.php">
                    Manage Community Halls
                </a>
            </li>

            <li>
                <a href="add-hall.php">
                    Add New Hall
                </a>
            </li>

        </ul>

        <h3>Booking Management</h3>

        <ul>

            <li>
                <a href="manage-bookings.php">
                    Manage Bookings
                </a>
            </li>

        </ul>

        <h3>User Management</h3>

        <ul>

            <li>
                <a href="manage-users.php">
                    Manage Registered Customers
                </a>
            </li>

        </ul>

        <hr>

        <a href="../auth/logout.php" class="btn">
            Logout
        </a>

    </div>

</body>

</html>