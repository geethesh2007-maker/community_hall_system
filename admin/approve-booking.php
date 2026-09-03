<?php

include "../config/database.php";
include "../config/session.php";

// =========================================
// ONLY ADMIN CAN ACCESS
// =========================================

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../auth/login.php");
    exit();
}


// =========================================
// CHECK BOOKING ID
// =========================================

if (!isset($_GET["id"])) {

    header("Location: manage-bookings.php");

    exit();

}


try {

    $bookingId =
        new MongoDB\BSON\ObjectId(
            $_GET["id"]
        );

} catch (Exception $e) {

    die("Invalid Booking ID!");

}


// =========================================
// FIND BOOKING
// =========================================

$booking =
    $db->bookings->findOne([
        "_id" => $bookingId
    ]);


if (!$booking) {

    die("Booking not found!");

}


// =========================================
// ONLY PENDING BOOKINGS CAN BE APPROVED
// =========================================

if (
    ($booking["status"] ?? "") !== "pending"
) {

    die("This booking cannot be approved!");

}


// =========================================
// GET BOOKING INFORMATION
// =========================================

$hallId =
    $booking["hall_id"];


$bookingDate =
    $booking["booking_date"] ?? "";


$startTime =
    $booking["start_time"] ?? "";


$endTime =
    $booking["end_time"] ?? "";


// =========================================
// MAKE SURE TIME INFORMATION EXISTS
// =========================================

if (
    $bookingDate === "" ||
    $startTime === "" ||
    $endTime === ""
) {

    die(
        "This booking does not contain complete date and time information."
    );

}


// =========================================
// CHECK FOR OVERLAPPING BOOKINGS
// =========================================
//
// Two bookings overlap when:
//
// existing start < new end
// AND
// existing end > new start
//
// Example:
//
// Existing: 10:00 - 12:00
// New:      11:00 - 13:00
//
// They overlap.
//
// Existing: 10:00 - 12:00
// New:      12:00 - 14:00
//
// They do NOT overlap.
// =========================================


$overlappingBooking =
    $db->bookings->findOne([

        "hall_id" => $hallId,

        "booking_date" => $bookingDate,

        "status" => [
            '$in' => [
                "pending",
                "approved"
            ]
        ],

        "_id" => [
            '$ne' => $bookingId
        ],

        "start_time" => [
            '$lt' => $endTime
        ],

        "end_time" => [
            '$gt' => $startTime
        ]

    ]);


if ($overlappingBooking) {

    die(
        "Cannot approve this booking because another booking for the same hall overlaps with this date and time."
    );

}


// =========================================
// APPROVE BOOKING
// =========================================

$db->bookings->updateOne(

    [
        "_id" => $bookingId
    ],

    [
        '$set' => [

            "status" => "approved",

            "approved_at" =>
                new MongoDB\BSON\UTCDateTime(),

            "approved_by" =>
                new MongoDB\BSON\ObjectId(
                    $_SESSION["user_id"]
                )

        ]
    ]

);


// =========================================
// RETURN TO MANAGE BOOKINGS
// =========================================

header(
    "Location: manage-bookings.php"
);

exit();

?>