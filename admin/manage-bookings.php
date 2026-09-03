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
// GET ALL BOOKINGS
// =========================================

$bookings = $db->bookings->find(

    [],

    [

        "sort" => [

            "created_at" => -1

        ]

    ]

);

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
        Manage Bookings - Community Hall System
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>


        /* =========================================
           PAGE HEADER
        ========================================= */

        .page-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 20px;

        }


        /* =========================================
           BOOKING LIST
        ========================================= */

        .booking-list {

            display: flex;

            flex-direction: column;

            gap: 25px;

            margin-top: 25px;

        }


        /* =========================================
           BOOKING CARD
        ========================================= */

        .booking-card {

            background-color: white;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(
                    0,
                    0,
                    0,
                    0.08
                );

            overflow: hidden;

            border-left:
                5px solid #1f3c88;

        }


        /* =========================================
           CARD HEADER
        ========================================= */

        .booking-card-header {

            background:
                linear-gradient(
                    135deg,
                    #1f3c88,
                    #315bb5
                );

            color: white;

            padding: 18px 22px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

        }


        .booking-card-header h2 {

            color: white;

            margin: 0;

            font-size: 20px;

        }


        .booking-id {

            font-size: 12px;

            opacity: 0.85;

        }


        /* =========================================
           CONTENT
        ========================================= */

        .booking-content {

            padding: 25px;

        }


        /* =========================================
           INFORMATION GRID
        ========================================= */

        .booking-sections {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

        }


        /* =========================================
           INFORMATION SECTION
        ========================================= */

        .info-section {

            background-color: #f8f9fc;

            border-radius: 9px;

            padding: 18px;

            border-left:
                4px solid #1f3c88;

        }


        .info-section.full-width {

            grid-column: 1 / -1;

        }


        .info-section h3 {

            color: #1f3c88;

            margin-top: 0;

            margin-bottom: 15px;

            padding-bottom: 8px;

            border-bottom:
                1px solid #dfe4ee;

            font-size: 17px;

        }


        /* =========================================
           INFORMATION ROW
        ========================================= */

        .info-row {

            display: flex;

            gap: 12px;

            margin-bottom: 10px;

            line-height: 1.5;

        }


        .info-row:last-child {

            margin-bottom: 0;

        }


        .info-label {

            min-width: 155px;

            font-weight: bold;

            color: #333;

        }


        .info-value {

            color: #555;

            word-break: break-word;

        }


        /* =========================================
           BOOKING DATE/TIME
        ========================================= */

        .booking-time-box {

            background-color: #eef3ff;

            border-left:
                4px solid #1f3c88;

            padding: 15px;

            border-radius: 7px;

            margin-bottom: 15px;

        }


        .booking-time-box .date {

            font-weight: bold;

            color: #1f3c88;

            font-size: 17px;

            margin-bottom: 7px;

        }


        .booking-time-box .time {

            font-size: 20px;

            font-weight: bold;

            color: #1f3c88;

        }


        /* =========================================
           EVENT DESCRIPTION
        ========================================= */

        .event-description {

            background-color: white;

            padding: 15px;

            border-radius: 7px;

            border: 1px solid #ddd;

            color: #555;

            line-height: 1.7;

            white-space: pre-wrap;

            word-break: break-word;

        }


        /* =========================================
           PAYMENT SCREENSHOT
        ========================================= */

        .payment-screenshot-box {

            background-color: white;

            padding: 15px;

            border-radius: 8px;

            border: 1px solid #dfe4ee;

        }


        .payment-screenshot {

            display: block;

            width: 100%;

            max-width: 500px;

            max-height: 600px;

            object-fit: contain;

            border-radius: 8px;

            border: 1px solid #d6dbe5;

            background-color: #f5f5f5;

            cursor: pointer;

        }


        .payment-help {

            margin-top: 10px;

            color: #777;

            font-size: 13px;

        }


        .no-payment-screenshot {

            margin: 0;

            color: #777;

            font-size: 14px;

        }


        .payment-status {

            display: inline-block;

            margin-bottom: 12px;

            padding: 6px 12px;

            border-radius: 20px;

            background-color: #fff3cd;

            color: #856404;

            font-size: 13px;

            font-weight: bold;

        }


        /* =========================================
           REFUND CALCULATION
        ========================================= */

        .refund-calculation-section {

            background-color: #fff8e8;

            border-left:
                5px solid #f0ad4e;

        }


        .refund-calculation-box {

            background-color: white;

            border-radius: 8px;

            padding: 18px;

            border: 1px solid #eadfb9;

        }


        .refund-calculation-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            padding: 10px 0;

            color: #444;

        }


        .refund-calculation-label {

            font-weight: bold;

        }


        .refund-calculation-value {

            font-weight: bold;

            font-size: 17px;

            color: #333;

        }


        .refund-divider {

            border: none;

            border-top:
                1px solid #ddd4b6;

            margin: 8px 0;

        }


        .customer-refund-row {

            font-size: 22px;

            color: #198754;

        }


        .customer-refund-row .refund-calculation-value {

            color: #198754;

            font-size: 24px;

        }


        /* =========================================
           REFUND SCREENSHOT
        ========================================= */

        .refund-section {

            border-left-color: #198754;

        }


        .refund-screenshot-box {

            background-color: #f0fff5;

            padding: 15px;

            border-radius: 8px;

            border: 1px solid #c9ead6;

        }


        .refund-status {

            display: inline-block;

            margin-bottom: 12px;

            padding: 6px 12px;

            border-radius: 20px;

            background-color: #d4edda;

            color: #155724;

            font-size: 13px;

            font-weight: bold;

        }


        .refund-screenshot {

            display: block;

            width: 100%;

            max-width: 500px;

            max-height: 600px;

            object-fit: contain;

            border-radius: 8px;

            border: 1px solid #b9dcc6;

            background-color: white;

            cursor: pointer;

        }


        .refund-help {

            margin-top: 10px;

            color: #777;

            font-size: 13px;

        }


        .no-refund-screenshot {

            color: #856404;

            margin: 0;

            font-size: 14px;

        }


        /* =========================================
           STATUS
        ========================================= */

        .status {

            display: inline-block;

            padding: 7px 16px;

            border-radius: 20px;

            font-size: 13px;

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
           REJECTION REASON
        ========================================= */

        .rejection-reason {

            margin-top: 12px;

            padding: 12px;

            background-color: #fff5f5;

            border-left:
                3px solid #dc3545;

            border-radius: 5px;

            color: #721c24;

            font-size: 14px;

            line-height: 1.6;

        }


        .rejection-label {

            font-weight: bold;

            display: block;

            margin-bottom: 5px;

        }


        /* =========================================
           ACTION AREA
        ========================================= */

        .booking-actions {

            border-top:
                1px solid #ddd;

            padding-top: 20px;

            margin-top: 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            flex-wrap: wrap;

        }


        .action-buttons {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

        }


        .action-btn {

            padding: 9px 18px;

            font-size: 14px;

        }


        /* =========================================
           APPROVE
        ========================================= */

        .approve-btn {

            background-color: #198754;

        }


        .approve-btn:hover {

            background-color: #146c43;

        }


        /* =========================================
           REJECT
        ========================================= */

        .reject-btn {

            background-color: #dc3545;

        }


        .reject-btn:hover {

            background-color: #b02a37;

        }


        /* =========================================
           EDIT REJECTED BOOKING
        ========================================= */

        .edit-rejected-btn {

            background-color: #f0ad4e;

            color: white;

        }


        .edit-rejected-btn:hover {

            background-color: #d9952e;

        }


        /* =========================================
           EMPTY BOOKINGS
        ========================================= */

        .empty-bookings {

            background-color: white;

            padding: 35px;

            margin-top: 25px;

            border-radius: 10px;

            text-align: center;

            box-shadow:
                0 2px 8px rgba(
                    0,
                    0,
                    0,
                    0.08
                );

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 800px) {

            .booking-sections {

                grid-template-columns: 1fr;

            }


            .info-section.full-width {

                grid-column: auto;

            }


            .page-header {

                display: block;

            }


            .page-header .btn {

                margin-top: 15px;

            }

        }


        @media (max-width: 600px) {

            .booking-content {

                padding: 18px;

            }


            .booking-card-header {

                display: block;

            }


            .booking-id {

                display: block;

                margin-top: 5px;

            }


            .info-row {

                display: block;

            }


            .info-label {

                display: block;

                margin-bottom: 3px;

            }


            .booking-actions {

                display: block;

            }


            .action-buttons {

                margin-top: 15px;

            }


            .action-buttons .btn {

                width: 100%;

                box-sizing: border-box;

                text-align: center;

            }


            .payment-screenshot,

            .refund-screenshot {

                max-height: 500px;

            }


            .refund-calculation-row {

                font-size: 15px;

            }


            .customer-refund-row {

                font-size: 18px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- =========================================
         PAGE HEADER
    ========================================== -->

    <div class="page-header">

        <div>

            <h1>

                Manage Bookings

            </h1>

            <p>

                Review all customer booking information,
                payment proof, refund calculation,
                and booking status.

            </p>

        </div>


        <a
            href="dashboard.php"
            class="btn"
        >

            Back to Dashboard

        </a>

    </div>


    <hr>


    <!-- =========================================
         CHECK BOOKINGS
    ========================================== -->

    <?php if ($db->bookings->countDocuments() === 0): ?>


        <div class="empty-bookings">

            <h2>

                No Bookings Found

            </h2>

            <p>

                There are currently no booking requests.

            </p>

        </div>


    <?php else: ?>


        <div class="booking-list">


            <?php foreach ($bookings as $booking): ?>


                <?php


                // =========================================
                // GET CUSTOMER
                // =========================================

                $user = $db->users->findOne([

                    "_id" => $booking["user_id"]

                ]);


                // =========================================
                // GET HALL
                // =========================================

                $hall = $db->halls->findOne([

                    "_id" => $booking["hall_id"]

                ]);


                // =========================================
                // STATUS
                // =========================================

                $status = strtolower(

                    (string) (
                        $booking["status"] ?? "pending"
                    )

                );


                // =========================================
                // BOOKING DATE
                // =========================================

                $bookingDate =

                    $booking["booking_date"] ?? "";


                // =========================================
                // BOOKING TIME
                // =========================================

                $startTime =

                    $booking["start_time"] ?? "";


                $endTime =

                    $booking["end_time"] ?? "";


                // =========================================
                // CUSTOMER RELATION
                // =========================================

                $customerRelation = "";


                if (

                    isset($booking["relationship"]) &&
                    !empty($booking["relationship"])

                ) {

                    $customerRelation =
                        $booking["relationship"];

                }

                elseif (

                    isset($booking["relation"]) &&
                    !empty($booking["relation"])

                ) {

                    $customerRelation =
                        $booking["relation"];

                }


                // =========================================
                // PAYMENT SCREENSHOT
                // =========================================

                $paymentScreenshot =

                    $booking["payment_screenshot"] ?? "";


                // =========================================
                // REFUND SCREENSHOT
                // =========================================

                $refundScreenshot =

                    $booking["refund_screenshot"] ?? "";


                // =========================================
                // REFUND CALCULATION
                // =========================================

                $totalCharge =

                    isset($booking["total_charge"])

                        ? (float)
                            $booking["total_charge"]

                        : 0;


                $deductionPercentage =

                    isset(
                        $booking[
                            "deduction_percentage"
                        ]
                    )

                        ? (float)
                            $booking[
                                "deduction_percentage"
                            ]

                        : 30;


                $deductionAmount =

                    isset(
                        $booking[
                            "deduction_amount"
                        ]
                    )

                        ? (float)
                            $booking[
                                "deduction_amount"
                            ]

                        : 0;


                $refundAmount =

                    isset(
                        $booking[
                            "refund_amount"
                        ]
                    )

                        ? (float)
                            $booking[
                                "refund_amount"
                            ]

                        : 0;

                ?>


                <!-- =====================================
                     BOOKING CARD
                ====================================== -->

                <div class="booking-card">


                    <!-- CARD HEADER -->

                    <div class="booking-card-header">


                        <h2>

                            <?php

                            echo $hall

                                ? htmlspecialchars(
                                    $hall["hall_name"]
                                )

                                : "Hall not found";

                            ?>

                        </h2>


                        <span class="booking-id">

                            Booking ID:

                            <?php

                            echo htmlspecialchars(
                                (string)
                                $booking["_id"]
                            );

                            ?>

                        </span>


                    </div>


                    <!-- BOOKING CONTENT -->

                    <div class="booking-content">


                        <!-- DATE AND TIME -->

                        <div class="booking-time-box">


                            <div class="date">

                                📅 Booking Date:

                                <?php

                                echo htmlspecialchars(
                                    $bookingDate
                                );

                                ?>

                            </div>


                            <div class="time">

                                🕐

                                <?php

                                if (

                                    !empty($startTime) &&
                                    !empty($endTime)

                                ) {

                                    echo htmlspecialchars(

                                        date(

                                            "h:i A",

                                            strtotime(
                                                $startTime
                                            )

                                        )

                                    );


                                    echo " - ";


                                    echo htmlspecialchars(

                                        date(

                                            "h:i A",

                                            strtotime(
                                                $endTime
                                            )

                                        )

                                    );

                                }

                                else {

                                    echo
                                        "Time not specified";

                                }

                                ?>

                            </div>


                        </div>


                        <!-- INFORMATION GRID -->

                        <div class="booking-sections">


                            <!-- CUSTOMER INFORMATION -->

                            <div class="info-section">


                                <h3>

                                    👤 Customer Information

                                </h3>


                                <?php if ($user): ?>


                                    <?php if (!empty($user["name"])): ?>


                                        <div class="info-row">

                                            <div class="info-label">

                                                Customer Name:

                                            </div>

                                            <div class="info-value">

                                                <?php

                                                echo htmlspecialchars(
                                                    $user["name"]
                                                );

                                                ?>

                                            </div>

                                        </div>


                                    <?php endif; ?>


                                    <?php if (!empty($user["email"])): ?>


                                        <div class="info-row">

                                            <div class="info-label">

                                                Email:

                                            </div>

                                            <div class="info-value">

                                                <?php

                                                echo htmlspecialchars(
                                                    $user["email"]
                                                );

                                                ?>

                                            </div>

                                        </div>


                                    <?php endif; ?>


                                    <?php if (!empty($user["phone"])): ?>


                                        <div class="info-row">

                                            <div class="info-label">

                                                Phone:

                                            </div>

                                            <div class="info-value">

                                                <?php

                                                echo htmlspecialchars(
                                                    $user["phone"]
                                                );

                                                ?>

                                            </div>

                                        </div>


                                    <?php endif; ?>


                                <?php else: ?>


                                    <div class="info-value">

                                        Customer information not found.

                                    </div>


                                <?php endif; ?>


                                <?php if (
                                    !empty(
                                        $customerRelation
                                    )
                                ): ?>


                                    <div class="info-row">

                                        <div class="info-label">

                                            Relation:

                                        </div>

                                        <div class="info-value">

                                            <?php

                                            echo htmlspecialchars(
                                                $customerRelation
                                            );

                                            ?>

                                        </div>

                                    </div>


                                <?php endif; ?>


                                <?php if (
                                    !empty(
                                        $booking[
                                            "contact_number"
                                        ]
                                    )
                                ): ?>


                                    <div class="info-row">

                                        <div class="info-label">

                                            Booking Contact:

                                        </div>

                                        <div class="info-value">

                                            <?php

                                            echo htmlspecialchars(
                                                $booking[
                                                    "contact_number"
                                                ]
                                            );

                                            ?>

                                        </div>

                                    </div>


                                <?php endif; ?>


                                <?php if (
                                    !empty(
                                        $booking[
                                            "number_of_people"
                                        ]
                                    )
                                ): ?>


                                    <div class="info-row">

                                        <div class="info-label">

                                            Expected People:

                                        </div>

                                        <div class="info-value">

                                            <?php

                                            echo htmlspecialchars(
                                                $booking[
                                                    "number_of_people"
                                                ]
                                            );

                                            ?>

                                        </div>

                                    </div>


                                <?php endif; ?>


                            </div>


                            <!-- EVENT INFORMATION -->

                            <div class="info-section">


                                <h3>

                                    🎉 Event Information

                                </h3>


                                <?php if (
                                    !empty(
                                        $booking[
                                            "event_type"
                                        ]
                                    )
                                ): ?>


                                    <div class="info-row">

                                        <div class="info-label">

                                            Event Type:

                                        </div>

                                        <div class="info-value">

                                            <?php

                                            echo htmlspecialchars(
                                                $booking[
                                                    "event_type"
                                                ]
                                            );

                                            ?>

                                        </div>

                                    </div>


                                <?php endif; ?>


                            </div>


                            <!-- HALL INFORMATION -->

                            <div class="info-section">


                                <h3>

                                    🏛️ Hall Information

                                </h3>


                                <?php if ($hall): ?>


                                    <div class="info-row">

                                        <div class="info-label">

                                            Hall Name:

                                        </div>

                                        <div class="info-value">

                                            <?php

                                            echo htmlspecialchars(
                                                $hall[
                                                    "hall_name"
                                                ] ?? ""
                                            );

                                            ?>

                                        </div>

                                    </div>


                                    <div class="info-row">

                                        <div class="info-label">

                                            Location:

                                        </div>

                                        <div class="info-value">

                                            <?php

                                            echo htmlspecialchars(
                                                $hall[
                                                    "location"
                                                ] ?? ""
                                            );

                                            ?>

                                        </div>

                                    </div>


                                    <div class="info-row">

                                        <div class="info-label">

                                            Capacity:

                                        </div>

                                        <div class="info-value">

                                            <?php

                                            echo htmlspecialchars(
                                                $hall[
                                                    "capacity"
                                                ] ?? ""
                                            );

                                            ?>

                                            people

                                        </div>

                                    </div>


                                    <div class="info-row">

                                        <div class="info-label">

                                            Price per Day:

                                        </div>

                                        <div class="info-value">

                                            ₹<?php

                                            echo htmlspecialchars(
                                                $hall[
                                                    "price"
                                                ] ?? ""
                                            );

                                            ?>

                                        </div>

                                    </div>


                                <?php endif; ?>


                            </div>


                            <!-- EVENT DESCRIPTION -->

                            <?php if (
                                !empty(
                                    $booking[
                                        "event_description"
                                    ]
                                )
                            ): ?>


                                <div
                                    class="info-section full-width"
                                >


                                    <h3>

                                        📝 Event Details / Description

                                    </h3>


                                    <div
                                        class="event-description"
                                    >

                                        <?php

                                        echo nl2br(

                                            htmlspecialchars(

                                                $booking[
                                                    "event_description"
                                                ]

                                            )

                                        );

                                        ?>

                                    </div>


                                </div>


                            <?php endif; ?>


                            <!-- PAYMENT SCREENSHOT -->

                            <div
                                class="info-section full-width"
                            >


                                <h3>

                                    💳 Payment Screenshot

                                </h3>


                                <?php if (
                                    !empty(
                                        $paymentScreenshot
                                    )
                                ): ?>


                                    <div
                                        class="payment-screenshot-box"
                                    >


                                        <div
                                            class="payment-status"
                                        >

                                            Payment screenshot uploaded

                                        </div>


                                        <a

                                            href="../uploads/payment-screenshots/<?php echo htmlspecialchars($paymentScreenshot, ENT_QUOTES, "UTF-8"); ?>"

                                            target="_blank"

                                            rel="noopener noreferrer"

                                        >


                                            <img

                                                src="../uploads/payment-screenshots/<?php echo htmlspecialchars($paymentScreenshot, ENT_QUOTES, "UTF-8"); ?>"

                                                alt="Payment Screenshot"

                                                class="payment-screenshot"

                                            >


                                        </a>


                                        <p class="payment-help">

                                            Click the screenshot to open
                                            it in a new tab and view it
                                            in full size.

                                        </p>


                                    </div>


                                <?php else: ?>


                                    <div
                                        class="payment-screenshot-box"
                                    >

                                        <p
                                            class="no-payment-screenshot"
                                        >

                                            ⚠️ No payment screenshot has
                                            been uploaded for this booking.

                                        </p>

                                    </div>


                                <?php endif; ?>


                            </div>


                            <!-- =================================
                                 REFUND CALCULATION
                                 ONLY FOR REJECTED BOOKINGS
                            ================================== -->

                            <?php if ($status === "rejected"): ?>


                                <div
                                    class="info-section full-width refund-calculation-section"
                                >


                                    <h3>

                                        💰 Refund Calculation

                                    </h3>


                                    <div
                                        class="refund-calculation-box"
                                    >


                                        <div
                                            class="refund-calculation-row"
                                        >

                                            <div
                                                class="refund-calculation-label"
                                            >

                                                Total Charge:

                                            </div>


                                            <div
                                                class="refund-calculation-value"
                                            >

                                                ₹<?php

                                                echo number_format(
                                                    $totalCharge,
                                                    2
                                                );

                                                ?>

                                            </div>


                                        </div>


                                        <div
                                            class="refund-calculation-row"
                                        >

                                            <div
                                                class="refund-calculation-label"
                                            >

                                                Admin Deduction / Tax
                                                (<?php
                                                echo number_format(
                                                    $deductionPercentage,
                                                    0
                                                );
                                                ?>%):

                                            </div>


                                            <div
                                                class="refund-calculation-value"
                                            >

                                                ₹<?php

                                                echo number_format(
                                                    $deductionAmount,
                                                    2
                                                );

                                                ?>

                                            </div>


                                        </div>


                                        <hr
                                            class="refund-divider"
                                        >


                                        <div
                                            class="refund-calculation-row customer-refund-row"
                                        >

                                            <div
                                                class="refund-calculation-label"
                                            >

                                                Customer Refund:

                                            </div>


                                            <div
                                                class="refund-calculation-value"
                                            >

                                                ₹<?php

                                                echo number_format(
                                                    $refundAmount,
                                                    2
                                                );

                                                ?>

                                            </div>


                                        </div>


                                    </div>


                                </div>


                                <!-- =================================
                                     REFUND SCREENSHOT
                                ================================== -->

                                <div
                                    class="info-section full-width refund-section"
                                >


                                    <h3>

                                        💰 Refund Payment Proof

                                    </h3>


                                    <?php if (
                                        !empty(
                                            $refundScreenshot
                                        )
                                    ): ?>


                                        <div
                                            class="refund-screenshot-box"
                                        >


                                            <div
                                                class="refund-status"
                                            >

                                                ✓ Refund Payment Proof Uploaded

                                            </div>


                                            <p>

                                                Customer Refund Amount:

                                                <strong>

                                                    ₹<?php

                                                    echo number_format(
                                                        $refundAmount,
                                                        2
                                                    );

                                                    ?>

                                                </strong>

                                            </p>


                                            <a

                                                href="../uploads/refunds/<?php echo htmlspecialchars($refundScreenshot, ENT_QUOTES, "UTF-8"); ?>"

                                                target="_blank"

                                                rel="noopener noreferrer"

                                            >


                                                <img

                                                    src="../uploads/refunds/<?php echo htmlspecialchars($refundScreenshot, ENT_QUOTES, "UTF-8"); ?>"

                                                    alt="Refund Payment Screenshot"

                                                    class="refund-screenshot"

                                                >


                                            </a>


                                            <p
                                                class="refund-help"
                                            >

                                                Click the refund screenshot
                                                to open it in a new tab and
                                                view it in full size.

                                            </p>


                                        </div>


                                    <?php else: ?>


                                        <div
                                            class="refund-screenshot-box"
                                        >

                                            <p
                                                class="no-refund-screenshot"
                                            >

                                                ⚠️ Refund payment proof has
                                                not been uploaded.

                                            </p>

                                        </div>


                                    <?php endif; ?>


                                </div>


                            <?php endif; ?>


                        </div>


                        <!-- =================================
                             STATUS + ACTIONS
                        ================================== -->

                        <div class="booking-actions">


                            <div>


                                <strong>

                                    Booking Status:

                                </strong>


                                <br><br>


                                <span

                                    class="status status-<?php echo htmlspecialchars($status, ENT_QUOTES, "UTF-8"); ?>"

                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $booking["status"] ?? ""
                                    );

                                    ?>

                                </span>


                                <!-- REJECTION REASON -->

                                <?php if (

                                    $status === "rejected" &&

                                    !empty(
                                        $booking[
                                            "rejection_reason"
                                        ]
                                    )

                                ): ?>


                                    <div
                                        class="rejection-reason"
                                    >


                                        <span
                                            class="rejection-label"
                                        >

                                            Rejection Reason:

                                        </span>


                                        <?php

                                        echo nl2br(

                                            htmlspecialchars(

                                                $booking[
                                                    "rejection_reason"
                                                ]

                                            )

                                        );

                                        ?>


                                    </div>


                                <?php endif; ?>


                            </div>


                            <!-- ACTION BUTTONS -->

                            <div class="action-buttons">


                                <?php if (
                                    $status === "pending"
                                ): ?>


                                    <!-- APPROVE -->

                                    <a

                                        href="approve-booking.php?id=<?php echo htmlspecialchars((string) $booking["_id"], ENT_QUOTES, "UTF-8"); ?>"

                                        class="btn action-btn approve-btn"

                                        onclick="return confirm('Are you sure you want to approve this booking? Please make sure you have checked the payment screenshot.');"

                                    >

                                        ✓ Approve

                                    </a>


                                    <!-- REJECT -->

                                    <a

                                        href="reject-booking.php?id=<?php echo htmlspecialchars((string) $booking["_id"], ENT_QUOTES, "UTF-8"); ?>"

                                        class="btn action-btn reject-btn"

                                    >

                                        ✕ Reject

                                    </a>


                                <?php elseif (
                                    $status === "rejected"
                                ): ?>


                                    <!-- EDIT REJECTED BOOKING -->

                                    <a

                                        href="edit-rejected-booking.php?id=<?php echo htmlspecialchars((string) $booking["_id"], ENT_QUOTES, "UTF-8"); ?>"

                                        class="btn action-btn edit-rejected-btn"

                                    >

                                        ✎ Edit Rejection / Refund

                                    </a>


                                <?php endif; ?>


                            </div>


                        </div>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


    <br>


    <a
        href="dashboard.php"
        class="btn"
    >

        ← Back to Dashboard

    </a>


</div>


</body>

</html>