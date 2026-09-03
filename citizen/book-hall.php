<?php

include "../config/database.php";
include "../config/session.php";

// =====================================================
// ONLY CUSTOMERS / CITIZENS CAN ACCESS
// =====================================================

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "citizen"
) {
    header("Location: ../auth/login.php");
    exit();
}


// =====================================================
// CHECK HALL ID
// =====================================================

if (!isset($_GET["id"])) {
    header("Location: view-halls.php");
    exit();
}


try {

    $hallId = new MongoDB\BSON\ObjectId(
        $_GET["id"]
    );

} catch (Exception $e) {

    die("Invalid Hall ID!");

}


// =====================================================
// GET HALL
// =====================================================

$hall = $db->halls->findOne([
    "_id" => $hallId
]);


if (!$hall) {
    die("Hall not found!");
}


// =====================================================
// GET LOGGED-IN CUSTOMER
// =====================================================

try {

    $userId = new MongoDB\BSON\ObjectId(
        $_SESSION["user_id"]
    );

} catch (Exception $e) {

    die("Invalid customer account!");

}


$user = $db->users->findOne([
    "_id" => $userId
]);


if (!$user) {
    die("Customer account not found!");
}


// =====================================================
// CUSTOMER LOGIN INFORMATION
// =====================================================

$customerName = $user["name"] ?? "";

$customerEmail = $user["email"] ?? "";

$customerPhone = $user["phone"] ?? "";


// =====================================================
// FORM VARIABLES
// =====================================================

$bookingDate = "";

$startTime = "";

$endTime = "";

$eventType = "";

$relationship = "";

$message = "";

$messageType = "";


// =====================================================
// PAYMENT INFORMATION FROM HALL
// =====================================================

$paymentMethod = trim(
    (string) ($hall["payment_method"] ?? "")
);

$paymentDetails = trim(
    (string) ($hall["payment_details"] ?? "")
);

$upiId = trim(
    (string) ($hall["upi_id"] ?? "")
);

$accountName = trim(
    (string) ($hall["account_name"] ?? "")
);

$accountNumber = trim(
    (string) ($hall["account_number"] ?? "")
);

$ifscCode = trim(
    (string) ($hall["ifsc_code"] ?? "")
);


// =====================================================
// CHECK WHETHER PAYMENT INFORMATION EXISTS
// =====================================================

$hasPaymentInformation =
    $paymentMethod !== "" ||
    $paymentDetails !== "" ||
    $upiId !== "" ||
    $accountName !== "" ||
    $accountNumber !== "" ||
    $ifscCode !== "";


// =====================================================
// PAYMENT SCREENSHOT UPLOAD DIRECTORY
// =====================================================

$paymentScreenshotDirectory =
    __DIR__ .
    "/../uploads/payment-screenshots/";


// =====================================================
// PROCESS BOOKING FORM
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // -------------------------------------------------
    // GET FORM VALUES
    // -------------------------------------------------

    $bookingDate = trim(
        $_POST["booking_date"] ?? ""
    );

    $startTime = trim(
        $_POST["start_time"] ?? ""
    );

    $endTime = trim(
        $_POST["end_time"] ?? ""
    );

    $eventType = trim(
        $_POST["event_type"] ?? ""
    );

    $relationship = trim(
        $_POST["relationship"] ?? ""
    );


    // =================================================
    // TODAY
    // =================================================

    $today = date("Y-m-d");


    // =================================================
    // VALIDATE DATE
    // =================================================

    if (empty($bookingDate)) {

        $message =
            "Please select a booking date.";

        $messageType = "error";


    } elseif ($bookingDate < $today) {

        $message =
            "You cannot book a hall for a past date.";

        $messageType = "error";


    // =================================================
    // VALIDATE START TIME
    // =================================================

    } elseif (empty($startTime)) {

        $message =
            "Please select a start time.";

        $messageType = "error";


    // =================================================
    // VALIDATE END TIME
    // =================================================

    } elseif (empty($endTime)) {

        $message =
            "Please select an end time.";

        $messageType = "error";


    // =================================================
    // VALIDATE TIME ORDER
    // =================================================

    } elseif ($startTime >= $endTime) {

        $message =
            "End time must be later than start time.";

        $messageType = "error";


    // =================================================
    // VALIDATE EVENT
    // =================================================

    } elseif (empty($eventType)) {

        $message =
            "Please enter what event the hall is being booked for.";

        $messageType = "error";


    // =================================================
    // VALIDATE RELATIONSHIP
    // =================================================

    } elseif (empty($relationship)) {

        $message =
            "Please enter the customer's relation to the event.";

        $messageType = "error";


    // =================================================
    // VALIDATE PAYMENT SCREENSHOT
    // =================================================

    } elseif (
        !isset($_FILES["payment_screenshot"]) ||
        $_FILES["payment_screenshot"]["error"]
        === UPLOAD_ERR_NO_FILE
    ) {

        $message =
            "Please upload a screenshot of your completed payment.";

        $messageType = "error";


    } else {


        // =================================================
        // TIME OVERLAP CHECK
        // =================================================

        $existingBooking = $db->bookings->findOne([

            "hall_id" => $hallId,

            "booking_date" => $bookingDate,

            "status" => [
                '$in' => [
                    "pending",
                    "approved"
                ]
            ],

            '$and' => [

                [
                    "start_time" => [
                        '$lt' => $endTime
                    ]
                ],

                [
                    "end_time" => [
                        '$gt' => $startTime
                    ]
                ]

            ]

        ]);


        // =================================================
        // BOOKING OVERLAP FOUND
        // =================================================

        if ($existingBooking) {

            $existingStart =
                $existingBooking["start_time"] ?? "";

            $existingEnd =
                $existingBooking["end_time"] ?? "";


            $message =
                "Sorry! This hall is already booked or has a pending booking during the selected time. " .
                "Existing booking time: " .
                date(
                    "h:i A",
                    strtotime($existingStart)
                ) .
                " - " .
                date(
                    "h:i A",
                    strtotime($existingEnd)
                ) .
                ".";

            $messageType = "error";


        } else {


            // =================================================
            // PROCESS PAYMENT SCREENSHOT
            // =================================================

            $paymentScreenshot =
                $_FILES["payment_screenshot"];


            $paymentScreenshotName = "";

            $paymentUploadError = false;


            // =================================================
            // UPLOAD ERROR
            // =================================================

            if (
                $paymentScreenshot["error"]
                !== UPLOAD_ERR_OK
            ) {

                $message =
                    "There was a problem uploading the payment screenshot.";

                $messageType = "error";

                $paymentUploadError = true;

            }


            // =================================================
            // FILE SIZE
            // =================================================

            elseif (
                $paymentScreenshot["size"]
                > 5 * 1024 * 1024
            ) {

                $message =
                    "Payment screenshot must not be larger than 5 MB.";

                $messageType = "error";

                $paymentUploadError = true;

            }


            // =================================================
            // CHECK MIME TYPE
            // =================================================

            if (!$paymentUploadError) {


                $allowedPaymentImageTypes = [

                    "image/jpeg" => "jpg",

                    "image/png" => "png",

                    "image/webp" => "webp"

                ];


                $finfo =
                    finfo_open(
                        FILEINFO_MIME_TYPE
                    );


                $paymentMimeType =
                    finfo_file(
                        $finfo,
                        $paymentScreenshot["tmp_name"]
                    );


                finfo_close(
                    $finfo
                );


                if (
                    !isset(
                        $allowedPaymentImageTypes[
                            $paymentMimeType
                        ]
                    )
                ) {

                    $message =
                        "Only JPG, PNG, and WEBP payment screenshots are allowed.";

                    $messageType = "error";

                    $paymentUploadError = true;

                }

            }


            // =================================================
            // SAVE PAYMENT SCREENSHOT
            // =================================================

            if (!$paymentUploadError) {


                if (
                    !is_dir(
                        $paymentScreenshotDirectory
                    )
                ) {

                    mkdir(
                        $paymentScreenshotDirectory,
                        0755,
                        true
                    );

                }


                $paymentExtension =
                    $allowedPaymentImageTypes[
                        $paymentMimeType
                    ];


                $paymentScreenshotName =
                    "payment_" .
                    uniqid() .
                    "_" .
                    time() .
                    "." .
                    $paymentExtension;


                $paymentScreenshotPath =
                    $paymentScreenshotDirectory .
                    $paymentScreenshotName;


                if (
                    !move_uploaded_file(
                        $paymentScreenshot["tmp_name"],
                        $paymentScreenshotPath
                    )
                ) {

                    $message =
                        "Unable to save the payment screenshot.";

                    $messageType = "error";

                    $paymentUploadError = true;

                }

            }


            // =================================================
            // CREATE BOOKING
            // =================================================

            if (!$paymentUploadError) {


                try {

                    $db->bookings->insertOne([

                        // ---------------------------------
                        // HALL
                        // ---------------------------------

                        "hall_id" => $hallId,


                        // ---------------------------------
                        // LOGGED-IN CUSTOMER
                        // ---------------------------------

                        "user_id" => $userId,


                        // ---------------------------------
                        // CUSTOMER INFORMATION
                        // ---------------------------------

                        "customer_name" =>
                            $customerName,

                        "customer_email" =>
                            $customerEmail,

                        "customer_phone" =>
                            $customerPhone,


                        // ---------------------------------
                        // BOOKING DATE AND TIME
                        // ---------------------------------

                        "booking_date" =>
                            $bookingDate,

                        "start_time" =>
                            $startTime,

                        "end_time" =>
                            $endTime,


                        // ---------------------------------
                        // EVENT INFORMATION
                        // ---------------------------------

                        "event_type" =>
                            $eventType,

                        "relationship" =>
                            $relationship,


                        // ---------------------------------
                        // PAYMENT INFORMATION
                        // ---------------------------------

                        "payment_screenshot" =>
                            $paymentScreenshotName,


                        // ---------------------------------
                        // BOOKING STATUS
                        // ---------------------------------

                        "status" =>
                            "pending",


                        // ---------------------------------
                        // CREATION TIME
                        // ---------------------------------

                        "created_at" =>
                            new MongoDB\BSON\UTCDateTime()

                    ]);


                    // =================================================
                    // SUCCESS
                    // =================================================

                    $message =
                        "Booking request submitted successfully! " .
                        "Your payment screenshot has been uploaded and " .
                        "your request is now waiting for admin approval.";

                    $messageType = "success";


                    // Clear form values

                    $bookingDate = "";

                    $startTime = "";

                    $endTime = "";

                    $eventType = "";

                    $relationship = "";


                } catch (Exception $e) {


                    // ---------------------------------
                    // DELETE SCREENSHOT IF DATABASE
                    // INSERT FAILED
                    // ---------------------------------

                    if (
                        !empty($paymentScreenshotName)
                    ) {

                        $uploadedScreenshotPath =
                            $paymentScreenshotDirectory .
                            $paymentScreenshotName;


                        if (
                            file_exists(
                                $uploadedScreenshotPath
                            )
                        ) {

                            unlink(
                                $uploadedScreenshotPath
                            );

                        }

                    }


                    $message =
                        "Something went wrong while submitting your booking. Please try again.";

                    $messageType = "error";

                }

            }

        }

    }

}

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Book Hall - Community Hall System
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>


        /* =====================================================
           BOOKING WRAPPER
        ===================================================== */

        .booking-wrapper {

            max-width: 850px;

            margin: 30px auto;

        }


        /* =====================================================
           BOOKING CARD
        ===================================================== */

        .booking-card {

            background-color: white;

            padding: 40px;

            border-radius: 12px;

            box-shadow:
                0 5px 20px rgba(0, 0, 0, 0.08);

        }


        /* =====================================================
           HEADER
        ===================================================== */

        .booking-header {

            background:
                linear-gradient(
                    135deg,
                    #1f3c88,
                    #315bb5
                );

            color: white;

            padding: 30px;

            border-radius: 10px;

            margin-bottom: 25px;

        }


        .booking-header h1 {

            color: white;

            margin-bottom: 8px;

        }


        .booking-header p {

            color: #e5ecff;

            margin: 0;

        }


        /* =====================================================
           HALL SUMMARY
        ===================================================== */

        .hall-summary {

            background-color: #f8f9fc;

            padding: 22px;

            border-radius: 8px;

            margin-bottom: 25px;

            border-left:
                4px solid #1f3c88;

        }


        .hall-summary h2 {

            color: #1f3c88;

            margin-bottom: 15px;

        }


        .hall-summary p {

            margin-bottom: 7px;

        }


        /* =====================================================
           CUSTOMER INFORMATION
        ===================================================== */

        .customer-summary {

            background-color: #eef3ff;

            padding: 22px;

            border-radius: 8px;

            margin-bottom: 25px;

            border-left:
                4px solid #315bb5;

        }


        .customer-summary h2 {

            color: #1f3c88;

            margin-top: 0;

            margin-bottom: 15px;

        }


        .customer-row {

            margin-bottom: 8px;

            color: #444;

        }


        .customer-row:last-child {

            margin-bottom: 0;

        }


        /* =====================================================
           PAYMENT INFORMATION
        ===================================================== */

        .payment-summary {

            background-color: #f8f9fc;

            padding: 22px;

            border-radius: 8px;

            margin-bottom: 25px;

            border-left:
                4px solid #198754;

        }


        .payment-summary h2 {

            color: #198754;

            margin-top: 0;

            margin-bottom: 15px;

        }


        .payment-row {

            margin-bottom: 9px;

            color: #444;

            word-break: break-word;

        }


        .payment-row:last-child {

            margin-bottom: 0;

        }


        .payment-note {

            margin-top: 15px;

            padding: 12px;

            background-color: #fff8e1;

            border-radius: 6px;

            color: #665c3b;

            font-size: 14px;

            line-height: 1.6;

        }


        .no-payment-info {

            color: #777;

            margin: 0;

        }


        /* =====================================================
           PAYMENT SCREENSHOT
        ===================================================== */

        .payment-upload-box {

            background-color: #f8f9fc;

            border: 2px dashed #cfd6e4;

            padding: 20px;

            border-radius: 8px;

        }


        .payment-upload-box input {

            background-color: white;

            width: 100%;

        }


        .payment-help {

            color: #777;

            font-size: 13px;

            margin-top: 8px;

            line-height: 1.6;

        }


        .payment-preview {

            margin-top: 15px;

            display: none;

        }


        .payment-preview img {

            width: 100%;

            max-width: 450px;

            max-height: 450px;

            object-fit: contain;

            border-radius: 8px;

            border: 1px solid #d6dbe5;

            background-color: white;

            padding: 5px;

        }


        /* =====================================================
           FORM
        ===================================================== */

        .booking-form {

            max-width: 100%;

            padding: 0;

            box-shadow: none;

            background: transparent;

        }


        .form-group {

            margin-bottom: 22px;

        }


        .form-group label {

            display: block;

            font-weight: bold;

            margin-bottom: 7px;

            color: #333;

        }


        .form-note {

            color: #777;

            font-size: 14px;

            margin-top: 7px;

        }


        /* =====================================================
           TIME ROW
        ===================================================== */

        .time-row {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 18px;

        }


        /* =====================================================
           OVERLAP NOTE
        ===================================================== */

        .overlap-note {

            background-color: #fff8e1;

            border-left:
                4px solid #f0ad4e;

            padding: 14px;

            border-radius: 6px;

            color: #665c3b;

            margin-top: 10px;

            font-size: 14px;

        }


        /* =====================================================
           ACTION BUTTONS
        ===================================================== */

        .action-buttons {

            display: flex;

            gap: 12px;

            margin-top: 25px;

            padding-top: 25px;

            border-top:
                1px solid #ddd;

            flex-wrap: wrap;

        }


        .action-buttons button,
        .action-buttons .btn {

            min-width: 200px;

            text-align: center;

        }


        .back-btn {

            background-color: #6c757d;

        }


        .back-btn:hover {

            background-color: #545b62;

        }


        /* =====================================================
           SUCCESS / ERROR
        ===================================================== */

        .success {

            margin-bottom: 25px;

            padding: 14px 18px;

            background-color: #d4edda;

            color: #155724;

            border-radius: 7px;

        }


        .error {

            margin-bottom: 25px;

            padding: 14px 18px;

            background-color: #f8d7da;

            color: #721c24;

            border-radius: 7px;

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 650px) {

            .booking-card {

                padding: 25px;

            }


            .booking-header {

                padding: 25px;

            }


            .time-row {

                grid-template-columns: 1fr;

            }


            .action-buttons {

                flex-direction: column;

            }


            .action-buttons button,
            .action-buttons .btn {

                width: 100%;

            }

        }


    </style>

</head>


<body>


<div class="container">


    <div class="booking-wrapper">


        <div class="booking-card">


            <!-- =================================================
                 HEADER
            ================================================== -->

            <div class="booking-header">

                <h1>
                    Book Community Hall
                </h1>

                <p>
                    Submit a booking request for your event.
                </p>

            </div>


            <!-- =================================================
                 HALL INFORMATION
            ================================================== -->

            <div class="hall-summary">

                <h2>

                    <?php

                    echo htmlspecialchars(
                        $hall["hall_name"] ?? ""
                    );

                    ?>

                </h2>


                <p>

                    <strong>
                        📍 Location:
                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $hall["location"] ?? ""
                    );

                    ?>

                </p>


                <p>

                    <strong>
                        👥 Capacity:
                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $hall["capacity"] ?? ""
                    );

                    ?>

                    people

                </p>


                <p>

                    <strong>
                        💰 Price per Day:
                    </strong>

                    ₹<?php

                    echo htmlspecialchars(
                        $hall["price"] ?? ""
                    );

                    ?>

                </p>


            </div>


            <!-- =================================================
                 CUSTOMER LOGIN INFORMATION
            ================================================== -->

            <div class="customer-summary">

                <h2>
                    👤 Customer Information
                </h2>


                <div class="customer-row">

                    <strong>
                        Customer Name:
                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $customerName
                    );

                    ?>

                </div>


                <div class="customer-row">

                    <strong>
                        Email:
                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $customerEmail
                    );

                    ?>

                </div>


                <div class="customer-row">

                    <strong>
                        Phone:
                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $customerPhone
                    );

                    ?>

                </div>


                <p class="form-note">

                    Your name, email and phone number are automatically taken from your customer account.

                </p>


            </div>


            <!-- =================================================
                 PAYMENT INFORMATION
            ================================================== -->

            <div class="payment-summary">

                <h2>
                    💳 Payment Information
                </h2>


                <?php if ($hasPaymentInformation): ?>


                    <?php if ($paymentMethod !== ""): ?>

                        <div class="payment-row">

                            <strong>
                                Payment Method:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $paymentMethod
                            );

                            ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($paymentDetails !== ""): ?>

                        <div class="payment-row">

                            <strong>
                                Payment Details:
                            </strong>

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $paymentDetails
                                )
                            );

                            ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($upiId !== ""): ?>

                        <div class="payment-row">

                            <strong>
                                UPI ID:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $upiId
                            );

                            ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($accountName !== ""): ?>

                        <div class="payment-row">

                            <strong>
                                Account Name:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $accountName
                            );

                            ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($accountNumber !== ""): ?>

                        <div class="payment-row">

                            <strong>
                                Account Number:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $accountNumber
                            );

                            ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($ifscCode !== ""): ?>

                        <div class="payment-row">

                            <strong>
                                IFSC Code:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $ifscCode
                            );

                            ?>

                        </div>

                    <?php endif; ?>


                    <div class="payment-note">

                        Please complete the payment using the payment
                        information above. After making the payment,
                        upload a clear screenshot of the successful
                        payment below.

                    </div>


                <?php else: ?>


                    <p class="no-payment-info">

                        Payment information has not been configured
                        for this hall yet. Please contact the administrator
                        before making a payment.

                    </p>


                <?php endif; ?>


            </div>


            <!-- =================================================
                 MESSAGE
            ================================================== -->

            <?php if (!empty($message)): ?>

                <div
                    class="<?php echo htmlspecialchars($messageType); ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $message
                    );

                    ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 BOOKING FORM
            ================================================== -->

            <form

                method="POST"

                class="booking-form"

                enctype="multipart/form-data"

                id="bookingForm"

            >


                <!-- =================================================
                     BOOKING DATE
                ================================================== -->

                <div class="form-group">

                    <label for="booking_date">

                        Booking Date

                    </label>


                    <input

                        type="date"

                        id="booking_date"

                        name="booking_date"

                        min="<?php echo date("Y-m-d"); ?>"

                        value="<?php echo htmlspecialchars($bookingDate); ?>"

                        required

                    >


                    <p class="form-note">

                        You can only select today or a future date.

                    </p>

                </div>


                <!-- =================================================
                     START / END TIME
                ================================================== -->

                <div class="time-row">


                    <div class="form-group">

                        <label for="start_time">

                            Start Time

                        </label>


                        <input

                            type="time"

                            id="start_time"

                            name="start_time"

                            value="<?php echo htmlspecialchars($startTime); ?>"

                            required

                        >

                    </div>


                    <div class="form-group">

                        <label for="end_time">

                            End Time

                        </label>


                        <input

                            type="time"

                            id="end_time"

                            name="end_time"

                            value="<?php echo htmlspecialchars($endTime); ?>"

                            required

                        >

                    </div>


                </div>


                <div class="overlap-note">

                    🕐 The system will automatically check whether another
                    pending or approved booking already uses this hall
                    during the selected time.

                    <br><br>

                    If the selected time overlaps another booking,
                    your booking request will not be submitted.

                </div>


                <br>


                <!-- =================================================
                     FOR WHICH EVENT
                ================================================== -->

                <div class="form-group">

                    <label for="event_type">

                        🎉 For Which Event?

                    </label>


                    <input

                        type="text"

                        id="event_type"

                        name="event_type"

                        placeholder="Wedding, Birthday, Meeting, etc."

                        value="<?php echo htmlspecialchars($eventType); ?>"

                        required

                    >


                    <p class="form-note">

                        Enter the type or purpose of the event for which you need the hall.

                    </p>

                </div>


                <!-- =================================================
                     CUSTOMER RELATION
                ================================================== -->

                <div class="form-group">

                    <label for="relationship">

                        👨‍👩‍👧 Customer Relation

                    </label>


                    <input

                        type="text"

                        id="relationship"

                        name="relationship"

                        placeholder="Self, Father, Mother, Brother, Sister, Friend, etc."

                        value="<?php echo htmlspecialchars($relationship); ?>"

                        required

                    >


                    <p class="form-note">

                        Enter the customer's relation to the person or occasion for whom the event is being organised.

                    </p>

                </div>


                <!-- =================================================
                     PAYMENT SCREENSHOT
                ================================================== -->

                <div class="form-group">

                    <label for="payment_screenshot">

                        📸 Payment Screenshot

                    </label>


                    <div class="payment-upload-box">

                        <input

                            type="file"

                            id="payment_screenshot"

                            name="payment_screenshot"

                            accept="image/jpeg,image/png,image/webp"

                            required

                        >


                        <div
                            class="payment-preview"
                            id="paymentPreview"
                        >

                            <img
                                id="paymentPreviewImage"
                                src=""
                                alt="Payment Screenshot Preview"
                            >

                        </div>

                    </div>


                    <p class="payment-help">

                        Upload a screenshot showing that the payment
                        was successfully completed.

                        <br>

                        JPG, PNG and WEBP only.

                        <br>

                        Maximum screenshot size:
                        <strong>5 MB</strong>.

                    </p>

                </div>


                <!-- =================================================
                     ACTION BUTTONS
                ================================================== -->

                <div class="action-buttons">


                    <button
                        type="submit"
                    >

                        Submit Booking Request

                    </button>


                    <a

                        href="hall-details.php?id=<?php echo htmlspecialchars((string) $hall["_id"]); ?>"

                        class="btn back-btn"

                    >

                        Back to Hall Details

                    </a>


                </div>


            </form>


        </div>


    </div>


</div>


<script>


// =====================================================
// PAYMENT SCREENSHOT PREVIEW
// =====================================================

const paymentScreenshot =
    document.getElementById(
        "payment_screenshot"
    );


const paymentPreview =
    document.getElementById(
        "paymentPreview"
    );


const paymentPreviewImage =
    document.getElementById(
        "paymentPreviewImage"
    );


paymentScreenshot.addEventListener(

    "change",

    function() {


        const file =
            this.files[0];


        if (!file) {

            paymentPreview.style.display =
                "none";

            paymentPreviewImage.src =
                "";

            return;

        }


        // =================================================
        // CHECK FILE SIZE
        // =================================================

        if (
            file.size >
            5 * 1024 * 1024
        ) {

            alert(
                "Payment screenshot must be smaller than 5 MB."
            );


            this.value = "";

            paymentPreview.style.display =
                "none";

            paymentPreviewImage.src =
                "";

            return;

        }


        // =================================================
        // CHECK FILE TYPE
        // =================================================

        const allowedTypes = [

            "image/jpeg",

            "image/png",

            "image/webp"

        ];


        if (
            !allowedTypes.includes(
                file.type
            )
        ) {

            alert(
                "Only JPG, PNG, and WEBP payment screenshots are allowed."
            );


            this.value = "";

            paymentPreview.style.display =
                "none";

            paymentPreviewImage.src =
                "";

            return;

        }


        // =================================================
        // SHOW PREVIEW
        // =================================================

        const reader =
            new FileReader();


        reader.onload =
            function(event) {

                paymentPreviewImage.src =
                    event.target.result;

                paymentPreview.style.display =
                    "block";

            };


        reader.readAsDataURL(file);

    }

);


// =====================================================
// FORM VALIDATION
// =====================================================

const bookingForm =
    document.getElementById(
        "bookingForm"
    );


bookingForm.addEventListener(

    "submit",

    function(event) {


        const file =
            paymentScreenshot.files[0];


        if (!file) {

            event.preventDefault();


            alert(
                "Please upload the screenshot of your completed payment."
            );


            return;

        }


        if (
            file.size >
            5 * 1024 * 1024
        ) {

            event.preventDefault();


            alert(
                "Payment screenshot must be smaller than 5 MB."
            );


            return;

        }


        const allowedTypes = [

            "image/jpeg",

            "image/png",

            "image/webp"

        ];


        if (
            !allowedTypes.includes(
                file.type
            )
        ) {

            event.preventDefault();


            alert(
                "Only JPG, PNG, and WEBP payment screenshots are allowed."
            );


            return;

        }

    }

);


</script>


</body>

</html>