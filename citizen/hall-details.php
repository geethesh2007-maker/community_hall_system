<?php

include "../config/database.php";
include "../config/session.php";


// =========================================
// ONLY LOGGED-IN CITIZENS
// =========================================

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "citizen"
) {

    header("Location: ../auth/login.php");
    exit();

}


// =========================================
// CHECK HALL ID
// =========================================

if (!isset($_GET["id"])) {

    header("Location: view-halls.php");

    exit();

}


// =========================================
// CONVERT HALL ID
// =========================================

try {

    $hallId =
        new MongoDB\BSON\ObjectId(
            $_GET["id"]
        );

} catch (Exception $e) {

    die("Invalid Hall ID!");

}


// =========================================
// FIND HALL
// =========================================

$hall =
    $db->halls->findOne([
        "_id" => $hallId
    ]);


if (!$hall) {

    die("Hall not found!");

}


// =========================================
// DETERMINE VIDEO TYPE
// =========================================

$videoType = "video/mp4";


if (!empty($hall["video"])) {

    $videoExtension =
        strtolower(
            pathinfo(
                $hall["video"],
                PATHINFO_EXTENSION
            )
        );


    if ($videoExtension === "webm") {

        $videoType = "video/webm";

    } elseif ($videoExtension === "ogv") {

        $videoType = "video/ogg";

    }

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

        <?php

        echo htmlspecialchars(
            $hall["hall_name"] ?? "Hall Details"
        );

        ?>

        - Community Hall System

    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        /* =========================================
           MAIN WRAPPER
        ========================================== */

        .details-wrapper {

            max-width: 850px;

            margin: 30px auto;

        }


        /* =========================================
           MAIN CARD
        ========================================== */

        .details-card {

            background-color: white;

            padding: 40px;

            border-radius: 12px;

            box-shadow:
                0 5px 20px rgba(0, 0, 0, 0.08);

        }


        /* =========================================
           HALL VIDEO
        ========================================== */

        .hall-details-video {

            width: 100%;

            height: 400px;

            object-fit: cover;

            background-color: #111;

            border-radius: 10px;

            margin-bottom: 25px;

            display: block;

        }


        .no-details-video {

            width: 100%;

            height: 300px;

            background-color: #eef3ff;

            border-radius: 10px;

            margin-bottom: 25px;

            display: flex;

            justify-content: center;

            align-items: center;

            color: #6c757d;

            font-size: 16px;

        }


        /* =========================================
           HALL HEADER
        ========================================== */

        .details-header {

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


        .details-header h1 {

            color: white;

            margin-bottom: 8px;

        }


        .details-header p {

            color: #e5ecff;

            margin: 0;

        }


        /* =========================================
           DETAIL GRID
        ========================================== */

        .detail-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

            margin-bottom: 25px;

        }


        .detail-box {

            background-color: #f8f9fc;

            padding: 20px;

            border-radius: 8px;

            border-left:
                4px solid #1f3c88;

        }


        .detail-label {

            display: block;

            font-size: 14px;

            font-weight: bold;

            color: #666;

            margin-bottom: 6px;

        }


        .detail-value {

            font-size: 17px;

            color: #333;

        }


        /* =========================================
           PRICE BOX
        ========================================== */

        .price-box {

            background-color: #eef3ff;

            border-left:
                4px solid #1f3c88;

        }


        .price {

            font-size: 25px;

            font-weight: bold;

            color: #1f3c88;

        }


        /* =========================================
           GENERAL DESCRIPTION
        ========================================== */

        .description-section {

            margin-top: 25px;

        }


        .description-section h3 {

            margin-bottom: 10px;

            color: #1f3c88;

        }


        .description-box {

            background-color: #f8f9fc;

            padding: 20px;

            border-radius: 8px;

            color: #555;

            line-height: 1.7;

            white-space: normal;

            word-break: break-word;

        }


        /* =========================================
           CHARGES INCLUDED
        ========================================== */

        .charges-section {

            margin-top: 25px;

        }


        .charges-box {

            background-color: #fff8e8;

            border-left:
                4px solid #e0a13b;

            padding: 20px;

            border-radius: 8px;

            color: #555;

            line-height: 1.8;

            white-space: normal;

            word-break: break-word;

        }


        .charges-title {

            color: #8a5a00;

            margin-bottom: 10px;

            font-weight: bold;

        }


        .charges-note {

            margin-top: 12px;

            font-size: 14px;

            color: #777;

        }


        /* =========================================
           PAYMENT INFORMATION
        ========================================== */

        .payment-section {

            margin-top: 25px;

        }


        .payment-box {

            background-color: #eef3ff;

            border-left:
                4px solid #315bb5;

            padding: 20px;

            border-radius: 8px;

        }


        .payment-box h3 {

            color: #1f3c88;

            margin-top: 0;

            margin-bottom: 18px;

        }


        .payment-row {

            display: flex;

            gap: 15px;

            margin-bottom: 12px;

            line-height: 1.5;

        }


        .payment-row:last-child {

            margin-bottom: 0;

        }


        .payment-label {

            min-width: 180px;

            font-weight: bold;

            color: #333;

        }


        .payment-value {

            color: #555;

            word-break: break-word;

        }


        /* =========================================
           ACTION BUTTONS
        ========================================== */

        .action-buttons {

            display: flex;

            gap: 15px;

            margin-top: 30px;

            padding-top: 25px;

            border-top:
                1px solid #ddd;

            flex-wrap: wrap;

        }


        .action-buttons .btn {

            min-width: 160px;

            text-align: center;

        }


        .back-btn {

            background-color: #6c757d;

        }


        .back-btn:hover {

            background-color: #545b62;

        }


        /* =========================================
           MOBILE
        ========================================== */

        @media (max-width: 650px) {

            .details-card {

                padding: 25px;

            }


            .detail-grid {

                grid-template-columns: 1fr;

            }


            .details-header {

                padding: 25px;

            }


            .hall-details-video {

                height: 250px;

            }


            .no-details-video {

                height: 220px;

            }


            .action-buttons {

                flex-direction: column;

            }


            .action-buttons .btn {

                width: 100%;

            }


            .payment-row {

                display: block;

            }


            .payment-label {

                display: block;

                margin-bottom: 4px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="details-wrapper">


        <div class="details-card">


            <!-- =========================================
                 HALL VIDEO
            ========================================== -->

            <?php if (!empty($hall["video"])): ?>


                <video

                    id="hallVideo"

                    class="hall-details-video"

                    autoplay

                    muted

                    loop

                    playsinline

                    controls

                    preload="auto"

                >

                    <source

                        src="../uploads/halls/<?php echo htmlspecialchars($hall["video"]); ?>"

                        type="<?php echo htmlspecialchars($videoType); ?>"

                    >

                    Your browser does not support video playback.

                </video>


            <?php else: ?>


                <div class="no-details-video">

                    No Video Available

                </div>


            <?php endif; ?>


            <!-- =========================================
                 HALL HEADER
            ========================================== -->

            <div class="details-header">


                <h1>

                    <?php

                    echo htmlspecialchars(
                        $hall["hall_name"] ?? ""
                    );

                    ?>

                </h1>


                <p>

                    Community Hall Details

                </p>


            </div>


            <!-- =========================================
                 BASIC INFORMATION
            ========================================== -->

            <div class="detail-grid">


                <!-- LOCATION -->

                <div class="detail-box">

                    <span class="detail-label">

                        📍 Location

                    </span>


                    <span class="detail-value">

                        <?php

                        echo htmlspecialchars(
                            $hall["location"] ?? ""
                        );

                        ?>

                    </span>

                </div>


                <!-- CAPACITY -->

                <div class="detail-box">

                    <span class="detail-label">

                        👥 Capacity

                    </span>


                    <span class="detail-value">

                        <?php

                        echo htmlspecialchars(
                            $hall["capacity"] ?? ""
                        );

                        ?>

                        people

                    </span>

                </div>


                <!-- TOTAL PRICE -->

                <div class="detail-box price-box">

                    <span class="detail-label">

                        💰 Total Price / Price per Day

                    </span>


                    <span class="price">

                        ₹<?php

                        echo htmlspecialchars(
                            $hall["price"] ?? "0"
                        );

                        ?>

                    </span>

                </div>


            </div>


            <!-- =========================================
                 CHARGES INCLUDED IN TOTAL PRICE
            ========================================== -->

            <?php if (!empty($hall["charges_included"])): ?>


                <div class="charges-section">


                    <h3>

                        💰 What Is Included in the Total Price?

                    </h3>


                    <div class="charges-box">


                        <div class="charges-title">

                            Charges Included:

                        </div>


                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $hall["charges_included"]
                            )
                        );

                        ?>


                        <div class="charges-note">

                            The above charges are included
                            in the total price shown for this hall.

                            Please review these charges before
                            making a payment.

                        </div>


                    </div>


                </div>


            <?php endif; ?>


            <!-- =========================================
                 PAYMENT INFORMATION
            ========================================== -->

            <?php

            $hasPaymentInformation =

                !empty($hall["payment_phone"]) ||

                !empty($hall["upi_id"]);

            ?>


            <?php if ($hasPaymentInformation): ?>


                <div class="payment-section">


                    <div class="payment-box">


                        <h3>

                            💳 Payment Information

                        </h3>


                        <?php if (!empty($hall["payment_phone"])): ?>


                            <div class="payment-row">


                                <div class="payment-label">

                                    Payment Phone Number:

                                </div>


                                <div class="payment-value">

                                    <?php

                                    echo htmlspecialchars(
                                        $hall["payment_phone"]
                                    );

                                    ?>

                                </div>


                            </div>


                        <?php endif; ?>


                        <?php if (!empty($hall["upi_id"])): ?>


                            <div class="payment-row">


                                <div class="payment-label">

                                    UPI ID:

                                </div>


                                <div class="payment-value">

                                    <?php

                                    echo htmlspecialchars(
                                        $hall["upi_id"]
                                    );

                                    ?>

                                </div>


                            </div>


                        <?php endif; ?>


                        <p class="charges-note">

                            Payment details provided by the
                            community hall administrator.

                        </p>


                    </div>


                </div>


            <?php endif; ?>


            <!-- =========================================
                 ABOUT THIS HALL
            ========================================== -->

            <div class="description-section">


                <h3>

                    About This Hall

                </h3>


                <div class="description-box">

                    <?php

                    if (!empty($hall["description"])) {

                        echo nl2br(
                            htmlspecialchars(
                                $hall["description"]
                            )
                        );

                    } else {

                        echo "No description available.";

                    }

                    ?>

                </div>


            </div>


            <!-- =========================================
                 FACILITIES
            ========================================== -->

            <div class="description-section">


                <h3>

                    Facilities

                </h3>


                <div class="description-box">

                    <?php

                    if (!empty($hall["facilities"])) {

                        echo nl2br(
                            htmlspecialchars(
                                $hall["facilities"]
                            )
                        );

                    } else {

                        echo "No facilities information available.";

                    }

                    ?>

                </div>


            </div>


            <!-- =========================================
                 ACTION BUTTONS
            ========================================== -->

            <div class="action-buttons">


                <a

                    href="book-hall.php?id=<?php echo htmlspecialchars((string) $hall["_id"]); ?>"

                    class="btn"

                >

                    Book This Hall

                </a>


                <a

                    href="view-halls.php"

                    class="btn back-btn"

                >

                    Back to Halls

                </a>


            </div>


        </div>


    </div>


</div>


<script>


document.addEventListener(

    "DOMContentLoaded",

    function () {


        const video =

            document.getElementById(
                "hallVideo"
            );


        if (!video) {

            return;

        }


        video.muted = true;


        video.play().catch(

            function () {

                // Browser may block autoplay.

            }

        );

    }

);


</script>


</body>

</html>