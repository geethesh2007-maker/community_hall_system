<?php

include "../config/database.php";
include "../config/session.php";

// Only admin can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Check if hall ID exists in URL
if (!isset($_GET["id"])) {
    header("Location: manage-halls.php");
    exit();
}

try {

    // Convert string ID to MongoDB ObjectId
    $hallId = new MongoDB\BSON\ObjectId($_GET["id"]);

    // Delete the hall
    $db->halls->deleteOne([
        "_id" => $hallId
    ]);

} catch (Exception $e) {

    die("Invalid Hall ID!");

}

// Return to Manage Halls page
header("Location: manage-halls.php");
exit();

?>