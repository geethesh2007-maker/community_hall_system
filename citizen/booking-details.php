<?php

include "../config/database.php";
include "../config/session.php";


// =========================================
// ONLY CITIZENS CAN ACCESS
// =========================================

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "citizen"
) {

    header("Location: ../auth/login.php");
    exit();

}


// =========================================
// CHECK BOOKING ID
// =========================================

if (!isset($_GET["id"])) {

    header("Location: my-bookings.php");
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
// GET CURRENT USER ID
// =========================================

try {

    $userId =
        new MongoDB\BSON\ObjectId(
            $_SESSION["user_id"]
        );

} catch (Exception $e) {

    die("Invalid User ID!");

}


// =========================================
// FIND BOOKING
// ONLY CURRENT CITIZEN'S BOOKING
// =========================================

$booking =
    $db->bookings->findOne([

        "_id" =>
            $bookingId,

        "user_id" =>
            $userId

    ]);


if (!$booking) {

    die("Booking not found!");

}


// =========================================
// GET HALL DETAILS
// =========================================

$hall =
    $db->halls->findOne([

        "_id" =>
            $booking["hall_id"]

    ]);


// =========================================
// GET BOOKING STATUS
// =========================================

$bookingStatus =
    strtolower(
        (string) (
            $booking["status"] ?? ""
        )
    );


// =========================================
// REFUND SCREENSHOT
// =========================================

$refundScreenshot =

    !empty(
        $booking["refund_screenshot"]
    )

        ? (string)
            $booking["refund_screenshot"]

        : "";


// =========================================
// CHECK REFUND SCREENSHOT EXISTS
// =========================================

$refundFileExists = false;

if ($refundScreenshot !== "") {

    $refundFilePath =

        __DIR__ .
        "/../uploads/refunds/" .
        $refundScreenshot;


    if (
        file_exists($refundFilePath)
    ) {

        $refundFileExists = true;

    }

}


// =========================================
// REFUND CALCULATION
// =========================================

$totalCharge =

    isset($booking["total_charge"])

        ? (float) $booking["total_charge"]

        : 0;


$deductionPercentage =

    isset($booking["deduction_percentage"])

        ? (float) $booking["deduction_percentage"]

        : 30;


$deductionAmount =

    isset($booking["deduction_amount"])

        ? (float) $booking["deduction_amount"]

        : 0;


$refundAmount =

    isset($booking["refund_amount"])

        ? (float) $booking["refund_amount"]

        : 0;


// =========================================
// ESCAPE HELPER
// =========================================

function e($value)
{

    return htmlspecialchars(

        (string) $value,

        ENT_QUOTES,

        "UTF-8"

    );

}

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
        Booking Details - Community Hall System
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>


        /* =========================================
           MAIN CARD
        ========================================= */

        .booking-details-card {

            background-color: white;

            max-width: 850px;

            margin: 30px auto;

            padding: 35px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(
                    0,
                    0,
                    0,
                    0.08
                );

        }


        /* =========================================
           TITLE
        ========================================= */

        .details-title {

            color: #1f3c88;

            margin-bottom: 25px;

        }


        /* =========================================
           HALL SECTION
        ========================================= */

        .hall-section {

            background-color: #f8f9fc;

            padding: 22px;

            border-radius: 8px;

            margin-bottom: 25px;

            border-left:
                4px solid #1f3c88;

        }


        .hall-section h2 {

            color: #1f3c88;

            margin-top: 0;

            margin-bottom: 15px;

        }


        .hall-section p {

            margin-bottom: 8px;

            color: #555;

        }


        /* =========================================
           DETAIL ROW
        ========================================= */

        .detail-row {

            padding: 16px 0;

            border-bottom:
                1px solid #eeeeee;

        }


        .detail-label {

            display: block;

            font-weight: bold;

            color: #555;

            margin-bottom: 6px;

        }


        .detail-value {

            color: #333;

            font-size: 16px;

        }


        /* =========================================
           STATUS
        ========================================= */

        .status {

            display: inline-block;

            padding: 7px 16px;

            border-radius: 20px;

            font-weight: bold;

            text-transform: capitalize;

        }


        .status-pending {

            background-color: #fff3cd;

            color: #856404;

        }


        .status-approved {

            background-color: #d4edda;

            color: #155724;

        }


        .status-rejected {

            background-color: #f8d7da;

            color: #721c24;

        }


        /* =========================================
           REJECTION SECTION
        ========================================= */

        .rejection-section {

            background-color: #fff5f5;

            border-left:
                5px solid #dc3545;

            padding: 22px;

            border-radius: 8px;

            margin-top: 25px;

        }


        .rejection-section h3 {

            color: #721c24;

            margin-top: 0;

            margin-bottom: 15px;

        }


        .rejection-reason {

            color: #721c24;

            line-height: 1.7;

            white-space: pre-line;

            margin-bottom: 0;

        }


        /* =========================================
           REFUND CALCULATION
        ========================================= */

        .refund-calculation {

            background-color: #fff8e8;

            border-left:
                5px solid #f0ad4e;

            padding: 25px;

            border-radius: 10px;

            margin-top: 25px;

        }


        .refund-calculation h3 {

            color: #1f3c88;

            margin-top: 0;

            margin-bottom: 20px;

            font-size: 24px;

        }


        .calculation-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 12px 0;

            font-size: 18px;

            color: #444;

        }


        .calculation-row strong {

            color: #333;

        }


        .calculation-divider {

            border: none;

            border-top:
                1px solid #d8d1b8;

            margin: 8px 0;

        }


        .refund-total {

            font-size: 28px;

            font-weight: bold;

            color: #198754;

        }


        .refund-note {

            margin-top: 15px;

            color: #666;

            line-height: 1.7;

        }


        /* =========================================
           REFUND SECTION
        ========================================= */

        .refund-section {

            background-color: #effcf3;

            border-left:
                5px solid #198754;

            padding: 22px;

            border-radius: 8px;

            margin-top: 25px;

        }


        .refund-section h3 {

            color: #146c43;

            margin-top: 0;

            margin-bottom: 12px;

        }


        .refund-message {

            color: #146c43;

            line-height: 1.7;

            margin-bottom: 18px;

        }


        /* =========================================
           REFUND IMAGE
        ========================================= */

        .refund-image-container {

            background-color: white;

            padding: 15px;

            border-radius: 8px;

            border:
                1px solid #d9e7dd;

            margin-top: 15px;

            text-align: center;

        }


        .refund-image {

            display: block;

            max-width: 100%;

            max-height: 600px;

            width: auto;

            height: auto;

            margin: 0 auto;

            border-radius: 6px;

            box-shadow:
                0 3px 10px rgba(
                    0,
                    0,
                    0,
                    0.10
                );

        }


        .refund-caption {

            color: #666;

            font-size: 13px;

            margin-top: 12px;

            margin-bottom: 0;

        }


        /* =========================================
           REFUND NOT UPLOADED
        ========================================= */

        .refund-pending {

            background-color: #fff8e1;

            border-left:
                5px solid #f0ad4e;

            padding: 22px;

            border-radius: 8px;

            margin-top: 25px;

        }


        .refund-pending h3 {

            color: #856404;

            margin-top: 0;

            margin-bottom: 12px;

        }


        .refund-pending p {

            color: #856404;

            line-height: 1.7;

            margin-bottom: 0;

        }


        /* =========================================
           INVALID REFUND FILE
        ========================================= */

        .refund-error {

            background-color: #fff5f5;

            border-left:
                5px solid #dc3545;

            padding: 20px;

            border-radius: 8px;

            margin-top: 25px;

        }


        .refund-error h3 {

            color: #721c24;

            margin-top: 0;

        }


        .refund-error p {

            color: #721c24;

            margin-bottom: 0;

        }


        /* =========================================
           ACTION BUTTONS
        ========================================= */

        .action-buttons {

            margin-top: 30px;

            display: flex;

            gap: 12px;

            flex-wrap: wrap;

        }


        .cancel-btn {

            background-color: #dc3545;

        }


        .cancel-btn:hover {

            background-color: #b02a37;

        }


        .back-btn {

            background-color: #6c757d;

        }


        .back-btn:hover {

            background-color: #545b62;

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 600px) {

            .booking-details-card {

                padding: 25px;

                margin: 20px 10px;

            }


            .action-buttons {

                flex-direction: column;

            }


            .action-buttons .btn {

                width: 100%;

                box-sizing: border-box;

                text-align: center;

            }


            .refund-image {

                max-height: 450px;

            }


            .calculation-row {

                font-size: 16px;

            }


            .refund-total {

                font-size: 22px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="booking-details-card">


        <h1 class="details-title">

            Booking Details

        </h1>


        <!-- =========================================
             HALL DETAILS
        ========================================== -->

        <?php if ($hall): ?>


            <div class="hall-section">


                <h2>

                    <?php

                    echo e(
                        $hall["hall_name"] ?? ""
                    );

                    ?>

                </h2>


                <p>

                    <strong>
                        📍 Location:
                    </strong>

                    <?php

                    echo e(
                        $hall["location"] ?? ""
                    );

                    ?>

                </p>


                <p>

                    <strong>
                        💰 Price per Day:
                    </strong>

                    ₹<?php

                    echo e(
                        $hall["price"] ?? ""
                    );

                    ?>

                </p>


                <?php if (!empty($hall["capacity"])): ?>

                    <p>

                        <strong>
                            👥 Capacity:
                        </strong>

                        <?php

                        echo e(
                            $hall["capacity"]
                        );

                        ?>

                        people

                    </p>

                <?php endif; ?>


            </div>


        <?php else: ?>


            <div class="error">

                Hall not found.

            </div>


        <?php endif; ?>


        <!-- BOOKING DATE -->

        <div class="detail-row">

            <span class="detail-label">

                Booking Date

            </span>

            <span class="detail-value">

                <?php

                echo e(
                    $booking["booking_date"] ?? ""
                );

                ?>

            </span>

        </div>


        <!-- EVENT TYPE -->

        <div class="detail-row">

            <span class="detail-label">

                Event Type

            </span>

            <span class="detail-value">

                <?php

                echo e(
                    $booking["event_type"] ?? ""
                );

                ?>

            </span>

        </div>


        <!-- BOOKING STATUS -->

        <div class="detail-row">

            <span class="detail-label">

                Booking Status

            </span>

            <span
                class="status status-<?php echo e($bookingStatus); ?>"
            >

                <?php

                echo e(
                    $booking["status"] ?? ""
                );

                ?>

            </span>

        </div>


        <!-- =========================================
             REJECTED BOOKING
        ========================================== -->

        <?php if ($bookingStatus === "rejected"): ?>


            <!-- REJECTION REASON -->

            <?php if (
                !empty(
                    $booking["rejection_reason"]
                )
            ): ?>


                <div class="rejection-section">

                    <h3>

                        ❌ Reason for Rejection

                    </h3>

                    <p class="rejection-reason">

                        <?php

                        echo e(
                            $booking[
                                "rejection_reason"
                            ]
                        );

                        ?>

                    </p>

                </div>


            <?php endif; ?>


            <!-- =====================================
                 REFUND CALCULATION
            ====================================== -->

            <div class="refund-calculation">


                <h3>

                    💰 Refund Calculation

                </h3>


                <div class="calculation-row">

                    <span>

                        Total Charge:

                    </span>

                    <strong>

                        ₹<?php

                        echo number_format(
                            $totalCharge,
                            2
                        );

                        ?>

                    </strong>

                </div>


                <div class="calculation-row">

                    <span>

                        Admin Deduction / Tax
                        (<?php echo number_format($deductionPercentage, 0); ?>%):

                    </span>

                    <strong>

                        ₹<?php

                        echo number_format(
                            $deductionAmount,
                            2
                        );

                        ?>

                    </strong>

                </div>


                <hr class="calculation-divider">


                <div class="calculation-row refund-total">

                    <span>

                        Customer Refund:

                    </span>

                    <span>

                        ₹<?php

                        echo number_format(
                            $refundAmount,
                            2
                        );

                        ?>

                    </span>

                </div>


                <p class="refund-note">

                    The refund amount shown above is calculated
                    automatically when the administrator rejects
                    the booking.

                </p>


            </div>


            <!-- =====================================
                 REFUND INFORMATION
            ====================================== -->

            <?php if ($refundScreenshot !== ""): ?>


                <?php if ($refundFileExists): ?>


                    <div class="refund-section">


                        <h3>

                            💰 Refund Payment Proof

                        </h3>


                        <p class="refund-message">

                            Your booking has been rejected.

                            According to the refund calculation
                            above, the refund amount is
                            <strong>

                                ₹<?php

                                echo number_format(
                                    $refundAmount,
                                    2
                                );

                                ?>

                            </strong>.

                            The administrator has uploaded the
                            refund payment proof below.

                        </p>


                        <div class="refund-image-container">


                            <img

                                src="../uploads/refunds/<?php
                                echo e(
                                    $refundScreenshot
                                );
                                ?>"

                                alt="Refund Payment Proof"

                                class="refund-image"

                            >


                            <p class="refund-caption">

                                Refund payment proof uploaded
                                by the administrator.

                            </p>


                        </div>


                    </div>


                <?php else: ?>


                    <div class="refund-error">

                        <h3>

                            ⚠ Refund Information

                        </h3>

                        <p>

                            The administrator has recorded
                            a refund payment proof, but the
                            image could not be found on the
                            server.

                            Please contact the administrator.

                        </p>

                    </div>


                <?php endif; ?>


            <?php else: ?>


                <div class="refund-pending">

                    <h3>

                        💰 Refund Information

                    </h3>

                    <p>

                        Your booking has been rejected.

                        Your calculated refund amount is:

                        <strong>

                            ₹<?php

                            echo number_format(
                                $refundAmount,
                                2
                            );

                            ?>

                        </strong>

                        <br><br>

                        The refund payment proof has not
                        yet been uploaded by the administrator.

                    </p>

                </div>


            <?php endif; ?>


        <?php endif; ?>


        <!-- =========================================
             ACTION BUTTONS
        ========================================== -->

        <div class="action-buttons">


            <?php if ($bookingStatus === "pending"): ?>


                <a

                    href="cancel-booking.php?id=<?php
                    echo e(
                        $booking["_id"]
                    );
                    ?>"

                    class="btn cancel-btn"

                    onclick="
                        return confirm(
                            'Are you sure you want to cancel this booking?'
                        );
                    "

                >

                    Cancel Booking

                </a>


            <?php endif; ?>


            <a

                href="my-bookings.php"

                class="btn back-btn"

            >

                ← Back to My Bookings

            </a>


        </div>


    </div>


</div>


</body>

</html>