<?php

include "../config/database.php";
include "../config/session.php";

// Only citizens can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "citizen") {
    header("Location: ../auth/login.php");
    exit();
}

// Check booking ID
if (!isset($_GET["id"])) {
    header("Location: my-bookings.php");
    exit();
}

try {
    $bookingId = new MongoDB\BSON\ObjectId($_GET["id"]);
    $userId = new MongoDB\BSON\ObjectId($_SESSION["user_id"]);
} catch (Exception $e) {
    die("Invalid Booking ID!");
}

// Find the booking belonging to the current citizen
$booking = $db->bookings->findOne([
    "_id" => $bookingId,
    "user_id" => $userId
]);

if (!$booking) {
    die("Booking not found!");
}

// Allow cancellation only when booking is pending
if ($booking["status"] !== "pending") {
    die("Only pending bookings can be cancelled!");
}

// Update booking status
$db->bookings->updateOne(
    [
        "_id" => $bookingId,
        "user_id" => $userId
    ],
    [
        '$set' => [
            "status" => "cancelled"
        ]
    ]
);

// Return to My Bookings
header("Location: my-bookings.php");
exit();

?>