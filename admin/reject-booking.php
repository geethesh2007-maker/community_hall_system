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


// =========================================
// CONVERT BOOKING ID
// =========================================

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
// ONLY PENDING BOOKINGS CAN BE REJECTED
// =========================================

if (
    ($booking["status"] ?? "") !== "pending"
) {

    die("This booking cannot be rejected!");

}


// =========================================
// FIXED DEDUCTION PERCENTAGE
// =========================================

$deductionPercentage = 30;


// =========================================
// VARIABLES
// =========================================

$message = "";

$messageType = "";

$rejectionReason = "";

$totalCharge = "";


// =========================================
// PROCESS REJECTION FORM
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // =========================================
    // GET FORM DATA
    // =========================================

    $rejectionReason =
        trim(
            $_POST["rejection_reason"] ?? ""
        );


    $totalCharge =
        (float) (
            $_POST["total_charge"] ?? 0
        );


    $refundScreenshot =
        $_FILES["refund_screenshot"] ?? null;


    // =========================================
    // VALIDATE REJECTION REASON
    // =========================================

    if (empty($rejectionReason)) {

        $message =
            "Please enter a reason for rejecting this booking.";

        $messageType =
            "error";


    // =========================================
    // VALIDATE TOTAL CHARGE
    // =========================================

    } elseif ($totalCharge <= 0) {

        $message =
            "Total charge must be greater than ₹0.";

        $messageType =
            "error";


    // =========================================
    // VALIDATE REFUND SCREENSHOT
    // =========================================

    } elseif (
        !$refundScreenshot ||
        $refundScreenshot["error"]
        === UPLOAD_ERR_NO_FILE
    ) {

        $message =
            "Please upload the refund payment screenshot.";

        $messageType =
            "error";


    } elseif (
        $refundScreenshot["error"]
        !== UPLOAD_ERR_OK
    ) {

        $message =
            "There was a problem uploading the refund screenshot.";

        $messageType =
            "error";


    // =========================================
    // REFUND SCREENSHOT SIZE
    // =========================================

    } elseif (
        $refundScreenshot["size"]
        > 5 * 1024 * 1024
    ) {

        $message =
            "Refund screenshot must not be larger than 5 MB.";

        $messageType =
            "error";


    } else {


        // =========================================
        // CALCULATE 30% DEDUCTION
        // =========================================

        $deductionAmount =

            round(

                $totalCharge
                *
                $deductionPercentage
                /
                100,

                2

            );


        // =========================================
        // CALCULATE CUSTOMER REFUND
        // =========================================

        $refundAmount =

            round(

                $totalCharge
                -
                $deductionAmount,

                2

            );


        // =========================================
        // CHECK IMAGE TYPE
        // =========================================

        $finfo =
            finfo_open(
                FILEINFO_MIME_TYPE
            );


        $refundMimeType =
            finfo_file(

                $finfo,

                $refundScreenshot["tmp_name"]

            );


        $allowedRefundTypes = [

            "image/jpeg" => "jpg",

            "image/png" => "png",

            "image/webp" => "webp"

        ];


        // =========================================
        // INVALID IMAGE TYPE
        // =========================================

        if (

            !isset(

                $allowedRefundTypes[
                    $refundMimeType
                ]

            )

        ) {

            finfo_close($finfo);


            $message =
                "Invalid refund screenshot format. Only JPG, PNG and WEBP are allowed.";

            $messageType =
                "error";


        } else {


            // =========================================
            // CREATE REFUND DIRECTORY
            // =========================================

            $refundUploadDirectory =

                __DIR__ .
                "/../uploads/refunds/";


            if (
                !is_dir(
                    $refundUploadDirectory
                )
            ) {

                mkdir(
                    $refundUploadDirectory,
                    0755,
                    true
                );

            }


            // =========================================
            // CREATE UNIQUE FILE NAME
            // =========================================

            $refundExtension =

                $allowedRefundTypes[
                    $refundMimeType
                ];


            $refundFileName =

                "refund_" .

                (string) $bookingId .

                "_" .

                uniqid() .

                "_" .

                time() .

                "." .

                $refundExtension;


            $refundFilePath =

                $refundUploadDirectory .
                $refundFileName;


            // =========================================
            // UPLOAD REFUND SCREENSHOT
            // =========================================

            if (

                !move_uploaded_file(

                    $refundScreenshot["tmp_name"],

                    $refundFilePath

                )

            ) {

                finfo_close($finfo);


                $message =
                    "Unable to save the refund screenshot.";

                $messageType =
                    "error";


            } else {


                // =========================================
                // CLOSE FILE INFO
                // =========================================

                finfo_close($finfo);


                // =========================================
                // UPDATE BOOKING
                // =========================================

                $updateResult =

                    $db->bookings->updateOne(

                        [

                            "_id" => $bookingId,

                            "status" => "pending"

                        ],

                        [

                            '$set' => [


                                // =================================
                                // BOOKING STATUS
                                // =================================

                                "status" =>
                                    "rejected",


                                // =================================
                                // REJECTION REASON
                                // =================================

                                "rejection_reason" =>
                                    $rejectionReason,


                                // =================================
                                // PAYMENT DETAILS
                                // =================================

                                "total_charge" =>
                                    $totalCharge,


                                // FIXED 30%

                                "deduction_percentage" =>
                                    $deductionPercentage,


                                // 30% AMOUNT

                                "deduction_amount" =>
                                    $deductionAmount,


                                // 70% CUSTOMER REFUND

                                "refund_amount" =>
                                    $refundAmount,


                                // =================================
                                // REFUND SCREENSHOT
                                // =================================

                                "refund_screenshot" =>
                                    $refundFileName,


                                // =================================
                                // REJECTION DATE
                                // =================================

                                "rejected_at" =>

                                    new MongoDB\BSON\UTCDateTime(),


                                // =================================
                                // ADMIN WHO REJECTED
                                // =================================

                                "rejected_by" =>

                                    new MongoDB\BSON\ObjectId(

                                        $_SESSION["user_id"]

                                    )

                            ]

                        ]

                    );


                // =========================================
                // CHECK DATABASE UPDATE
                // =========================================

                if (

                    $updateResult
                        ->getModifiedCount()

                    === 0

                ) {


                    // =====================================
                    // DELETE UPLOADED SCREENSHOT
                    // =====================================

                    if (
                        file_exists(
                            $refundFilePath
                        )
                    ) {

                        unlink(
                            $refundFilePath
                        );

                    }


                    $message =
                        "Unable to reject the booking. Please try again.";

                    $messageType =
                        "error";


                } else {


                    // =====================================
                    // SUCCESS
                    // =====================================

                    header(
                        "Location: manage-bookings.php"
                    );

                    exit();

                }

            }

        }

    }

}


// =========================================
// GET CITIZEN DETAILS
// =========================================

$user =
    $db->users->findOne([

        "_id" =>
            $booking["user_id"]

    ]);


// =========================================
// GET HALL DETAILS
// =========================================

$hall =
    $db->halls->findOne([

        "_id" =>
            $booking["hall_id"]

    ]);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Reject Booking - Community Hall System
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>


        /* =========================================
           REJECT CARD
        ========================================= */

        .reject-card {

            max-width: 700px;

            margin: 40px auto;

            background-color: white;

            padding: 35px;

            border-radius: 10px;

            box-shadow:
                0 3px 12px rgba(0, 0, 0, 0.08);

        }


        /* =========================================
           TITLE
        ========================================= */

        .reject-title {

            color: #721c24;

            margin-bottom: 10px;

        }


        /* =========================================
           BOOKING SUMMARY
        ========================================= */

        .booking-summary {

            background-color: #f8f9fc;

            padding: 20px;

            border-radius: 8px;

            margin: 20px 0;

        }


        .booking-summary p {

            margin-bottom: 8px;

        }


        /* =========================================
           FORM GROUP
        ========================================= */

        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            font-weight: bold;

            margin-bottom: 8px;

        }


        .form-help {

            color: #666;

            font-size: 14px;

            margin-top: 7px;

            line-height: 1.6;

        }


        /* =========================================
           CALCULATION BOX
        ========================================= */

        .calculation-box {

            background-color: #fff8e1;

            padding: 20px;

            border-radius: 8px;

            margin-top: 20px;

            border-left:
                4px solid #f0ad4e;

        }


        .calculation-box h3 {

            margin-top: 0;

            margin-bottom: 18px;

            color: #665c3b;

        }


        .calculation-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 14px;

            font-size: 16px;

        }


        .refund-result {

            font-size: 22px;

            font-weight: bold;

            color: #198754;

            padding-top: 15px;

            border-top:
                1px solid #ddd;

            margin-bottom: 0;

        }


        /* =========================================
           REFUND SECTION
        ========================================= */

        .refund-section {

            background-color: #eef3ff;

            padding: 20px;

            border-radius: 8px;

            border-left:
                4px solid #1f3c88;

            margin-top: 25px;

        }


        .refund-section h3 {

            color: #1f3c88;

            margin-top: 0;

        }


        .refund-upload-box {

            background-color: white;

            border:
                2px dashed #cfd6e4;

            padding: 18px;

            border-radius: 8px;

        }


        .refund-upload-box input {

            width: 100%;

            box-sizing: border-box;

        }


        /* =========================================
           BUTTONS
        ========================================= */

        .reject-button {

            background-color: #dc3545;

        }


        .reject-button:hover {

            background-color: #b02a37;

        }


        .cancel-button {

            background-color: #6c757d;

        }


        .cancel-button:hover {

            background-color: #545b62;

        }


        .action-buttons {

            display: flex;

            gap: 12px;

            margin-top: 25px;

            flex-wrap: wrap;

        }


        /* =========================================
           SUCCESS / ERROR
        ========================================= */

        .success {

            background-color: #d4edda;

            color: #155724;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;

        }


        .error {

            background-color: #f8d7da;

            color: #721c24;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 600px) {

            .reject-card {

                padding: 25px;

            }


            .calculation-row {

                flex-direction: column;

                gap: 5px;

            }


            .action-buttons {

                flex-direction: column;

            }


            .action-buttons button,

            .action-buttons a {

                width: 100%;

                text-align: center;

                box-sizing: border-box;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="reject-card">


        <!-- =========================================
             TITLE
        ========================================== -->

        <h1 class="reject-title">

            Reject Booking

        </h1>


        <p>

            Enter the total charge.
            A fixed 30% deduction will automatically be calculated,
            and the remaining 70% will be refunded to the customer.

        </p>


        <!-- =========================================
             MESSAGE
        ========================================== -->

        <?php if ($message !== ""): ?>

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


        <!-- =========================================
             BOOKING SUMMARY
        ========================================== -->

        <div class="booking-summary">


            <p>

                <strong>
                    Customer:
                </strong>


                <?php

                echo $user

                    ? htmlspecialchars(
                        $user["name"] ?? ""
                    )

                    : "User not found";

                ?>

            </p>


            <p>

                <strong>
                    Hall:
                </strong>


                <?php

                echo $hall

                    ? htmlspecialchars(
                        $hall["hall_name"] ?? ""
                    )

                    : "Hall not found";

                ?>

            </p>


            <p>

                <strong>
                    Booking Date:
                </strong>


                <?php

                echo htmlspecialchars(
                    $booking["booking_date"] ?? ""
                );

                ?>

            </p>


            <p>

                <strong>
                    Event Type:
                </strong>


                <?php

                echo htmlspecialchars(
                    $booking["event_type"] ?? ""
                );

                ?>

            </p>


        </div>


        <!-- =========================================
             REJECTION FORM
        ========================================== -->

        <form

            method="POST"

            enctype="multipart/form-data"

        >


            <!-- =========================================
                 REJECTION REASON
            ========================================== -->

            <div class="form-group">


                <label
                    for="rejection_reason"
                >

                    Reason for Rejection

                </label>


                <textarea

                    id="rejection_reason"

                    name="rejection_reason"

                    rows="5"

                    placeholder="Example: The hall is already reserved for an official event."

                    required

                ><?php echo htmlspecialchars($rejectionReason); ?></textarea>


            </div>


            <!-- =========================================
                 TOTAL CHARGE
            ========================================== -->

            <div class="form-group">


                <label
                    for="total_charge"
                >

                    💰 Total Charge (₹)

                </label>


                <input

                    type="number"

                    id="total_charge"

                    name="total_charge"

                    min="0.01"

                    step="0.01"

                    value="<?php echo htmlspecialchars((string) $totalCharge); ?>"

                    placeholder="Example: 10000"

                    required

                >


                <p class="form-help">

                    Enter the total amount charged for this booking.

                </p>


            </div>


            <!-- =========================================
                 FIXED 30% DEDUCTION
            ========================================== -->

            <div class="form-group">


                <label>

                    🏛️ Deduction / Tax Percentage

                </label>


                <input

                    type="number"

                    value="30"

                    readonly

                >


                <p class="form-help">

                    A fixed 30% deduction will be taken.
                    The customer will receive the remaining 70%.

                </p>


            </div>


            <!-- =========================================
                 REAL TIME CALCULATION
            ========================================== -->

            <div class="calculation-box">


                <h3>

                    💰 Refund Calculation

                </h3>


                <div class="calculation-row">


                    <span>

                        Total Charge:

                    </span>


                    <strong
                        id="totalChargeDisplay"
                    >

                        ₹0.00

                    </strong>


                </div>


                <div class="calculation-row">


                    <span>

                        30% Deduction:

                    </span>


                    <strong
                        id="deductionAmountDisplay"
                    >

                        ₹0.00

                    </strong>


                </div>


                <div
                    class="
                        calculation-row
                        refund-result
                    "
                >


                    <span>

                        Customer Refund (70%):

                    </span>


                    <strong
                        id="refundAmountDisplay"
                    >

                        ₹0.00

                    </strong>


                </div>


            </div>


            <!-- =========================================
                 REFUND PAYMENT SCREENSHOT
            ========================================== -->

            <div class="refund-section">


                <h3>

                    📸 Refund Payment Screenshot

                </h3>


                <p class="form-help">

                    Upload a screenshot showing that
                    the calculated refund amount has been
                    paid to the customer.

                </p>


                <div class="refund-upload-box">


                    <input

                        type="file"

                        id="refund_screenshot"

                        name="refund_screenshot"

                        accept="image/jpeg,image/png,image/webp"

                        required

                    >


                    <p class="form-help">

                        Supported formats:
                        JPG, PNG and WEBP.

                        <br>

                        Maximum size:
                        <strong>5 MB</strong>.

                    </p>


                </div>


            </div>


            <!-- =========================================
                 ACTION BUTTONS
            ========================================== -->

            <div class="action-buttons">


                <button

                    type="submit"

                    class="reject-button"

                    onclick="
                        return confirm(
                            'Are you sure you want to reject this booking? A fixed 30% deduction and 70% refund will be saved.'
                        );
                    "

                >

                    ✕ Reject Booking & Save Refund

                </button>


                <a

                    href="manage-bookings.php"

                    class="btn cancel-button"

                >

                    Cancel

                </a>


            </div>


        </form>


    </div>


</div>


<!-- =========================================
     REAL TIME CALCULATION
========================================== -->

<script>


    // =========================================
    // GET ELEMENTS
    // =========================================

    const totalChargeInput =

        document.getElementById(
            "total_charge"
        );


    const totalChargeDisplay =

        document.getElementById(
            "totalChargeDisplay"
        );


    const deductionAmountDisplay =

        document.getElementById(
            "deductionAmountDisplay"
        );


    const refundAmountDisplay =

        document.getElementById(
            "refundAmountDisplay"
        );


    // =========================================
    // FIXED DEDUCTION PERCENTAGE
    // =========================================

    const deductionPercentage = 30;


    // =========================================
    // CALCULATE REFUND
    // =========================================

    function calculateRefund() {


        let totalCharge =

            parseFloat(
                totalChargeInput.value
            );


        // =====================================
        // IF EMPTY OR INVALID
        // =====================================

        if (

            isNaN(totalCharge) ||

            totalCharge < 0

        ) {

            totalCharge = 0;

        }


        // =====================================
        // CALCULATE 30% DEDUCTION
        // =====================================

        const deductionAmount =

            totalCharge
            *
            deductionPercentage
            /
            100;


        // =====================================
        // CALCULATE 70% REFUND
        // =====================================

        const refundAmount =

            totalCharge
            -
            deductionAmount;


        // =====================================
        // DISPLAY TOTAL CHARGE
        // =====================================

        totalChargeDisplay.textContent =

            "₹" +

            totalCharge.toFixed(2);


        // =====================================
        // DISPLAY 30% DEDUCTION
        // =====================================

        deductionAmountDisplay.textContent =

            "₹" +

            deductionAmount.toFixed(2);


        // =====================================
        // DISPLAY 70% REFUND
        // =====================================

        refundAmountDisplay.textContent =

            "₹" +

            refundAmount.toFixed(2);

    }


    // =========================================
    // REAL TIME INPUT EVENT
    // =========================================

    totalChargeInput.addEventListener(

        "input",

        calculateRefund

    );


    // =========================================
    // INITIAL CALCULATION
    // =========================================

    calculateRefund();


</script>


</body>

</html>