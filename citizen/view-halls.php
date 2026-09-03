<?php

include "../config/database.php";
include "../config/session.php";


// =========================================
// ONLY LOGGED-IN CITIZENS CAN ACCESS
// =========================================

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "citizen"
) {

    header("Location: ../auth/login.php");
    exit();

}


// =========================================
// GET ALL HALLS
// =========================================

$halls = $db->halls->find();

$hallCount =
    $db->halls->countDocuments();

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
        Available Halls - Community Hall System
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

            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

            margin-bottom: 30px;

        }


        .page-header-content {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

        }


        .page-header h1 {

            margin-bottom: 5px;

            color: #1f3c88;

        }


        .page-header p {

            color: #666;

            margin: 0;

        }


        /* =========================================
           HALL GRID
        ========================================= */

        .hall-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;

        }


        /* =========================================
           HALL CARD
        ========================================= */

        .hall-card {

            background-color: white;

            padding: 0;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;

            border-top:
                4px solid #1f3c88;

            overflow: hidden;

        }


        .hall-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 8px 22px rgba(0, 0, 0, 0.12);

        }


        /* =========================================
           VIDEO
        ========================================= */

        .hall-video-container {

            width: 100%;

            height: 210px;

            background-color: #111;

            overflow: hidden;

        }


        .hall-video {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;

        }


        .no-video {

            width: 100%;

            height: 100%;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #aaa;

            font-size: 15px;

        }


        /* =========================================
           HALL CONTENT
        ========================================= */

        .hall-content {

            padding: 25px;

        }


        .hall-card h2 {

            color: #1f3c88;

            margin-bottom: 18px;

        }


        .hall-info {

            margin-bottom: 12px;

            color: #555;

        }


        .hall-info strong {

            color: #333;

        }


        /* =========================================
           PRICE
        ========================================= */

        .hall-price {

            font-size: 24px;

            font-weight: bold;

            color: #1f3c88;

            margin: 20px 0;

        }


        .hall-price small {

            font-size: 14px;

            font-weight: normal;

            color: #777;

        }


        /* =========================================
           CHARGES INCLUDED
        ========================================= */

        .charges-box {

            background-color: #fff8e8;

            border-left:
                4px solid #e0a13b;

            padding: 15px;

            border-radius: 8px;

            margin: 18px 0;

        }


        .charges-title {

            font-weight: bold;

            color: #8a5a00;

            margin-bottom: 8px;

            font-size: 15px;

        }


        .charges-content {

            color: #555;

            font-size: 14px;

            line-height: 1.6;

            white-space: normal;

        }


        /* =========================================
           BUTTONS
        ========================================= */

        .hall-buttons {

            display: flex;

            flex-direction: column;

            gap: 10px;

        }


        .hall-button {

            width: 100%;

            text-align: center;

            box-sizing: border-box;

        }


        /* =========================================
           VIEW PHOTOS BUTTON
        ========================================= */

        .photo-button {

            background-color: #198754;

        }


        .photo-button:hover {

            background-color: #146c43;

        }


        /* =========================================
           EMPTY MESSAGE
        ========================================= */

        .empty-message {

            background-color: white;

            padding: 35px;

            border-radius: 12px;

            text-align: center;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

        }


        .empty-message h2 {

            color: #1f3c88;

        }


        /* =========================================
           BOTTOM BACK BUTTON
        ========================================= */

        .bottom-back {

            margin-top: 35px;

        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 900px) {

            .hall-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 650px) {

            .hall-grid {

                grid-template-columns:
                    1fr;

            }


            .page-header-content {

                display: block;

            }


            .page-header .btn {

                margin-top: 20px;

                display: inline-block;

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


        <div class="page-header-content">


            <div>

                <h1>
                    Available Community Halls
                </h1>


                <p>
                    Browse available halls and choose the perfect
                    location for your event.
                </p>

            </div>


            <a
                href="dashboard.php"
                class="btn"
            >

                Back to Dashboard

            </a>


        </div>


    </div>


    <!-- =========================================
         CHECK FOR HALLS
    ========================================== -->

    <?php if ($hallCount === 0): ?>


        <div class="empty-message">


            <h2>
                No Community Halls Available
            </h2>


            <p>
                There are currently no halls available for booking.
                Please check again later.
            </p>


        </div>


    <?php else: ?>


        <!-- =========================================
             HALL GRID
        ========================================== -->

        <div class="hall-grid">


            <?php foreach ($halls as $hall): ?>


                <div class="hall-card">


                    <!-- =========================================
                         HALL VIDEO
                    ========================================== -->

                    <div class="hall-video-container">


                        <?php if (!empty($hall["video"])): ?>


                            <?php

                            $videoFile =
                                (string) $hall["video"];


                            $videoExtension =
                                strtolower(
                                    pathinfo(
                                        $videoFile,
                                        PATHINFO_EXTENSION
                                    )
                                );


                            $videoType =
                                "video/mp4";


                            if (
                                $videoExtension === "webm"
                            ) {

                                $videoType =
                                    "video/webm";

                            } elseif (
                                $videoExtension === "ogv" ||
                                $videoExtension === "ogg"
                            ) {

                                $videoType =
                                    "video/ogg";

                            }

                            ?>


                            <video

                                class="hall-video"

                                autoplay

                                muted

                                loop

                                playsinline

                                controls

                                preload="metadata"

                            >


                                <source

                                    src="../uploads/halls/<?php
                                    echo htmlspecialchars(
                                        $videoFile,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>"

                                    type="<?php
                                    echo htmlspecialchars(
                                        $videoType,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>"

                                >


                                Your browser does not support
                                video playback.


                            </video>


                        <?php else: ?>


                            <div class="no-video">

                                No Video Available

                            </div>


                        <?php endif; ?>


                    </div>


                    <!-- =========================================
                         HALL INFORMATION
                    ========================================== -->

                    <div class="hall-content">


                        <!-- HALL NAME -->

                        <h2>

                            <?php

                            echo htmlspecialchars(
                                (string) ($hall["hall_name"] ?? ""),
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            ?>

                        </h2>


                        <!-- LOCATION -->

                        <p class="hall-info">

                            <strong>
                                📍 Location:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                (string) ($hall["location"] ?? ""),
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            ?>

                        </p>


                        <!-- CAPACITY -->

                        <p class="hall-info">

                            <strong>
                                👥 Capacity:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                (string) ($hall["capacity"] ?? ""),
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            ?>

                            people

                        </p>


                        <!-- =========================================
                             TOTAL PRICE
                        ========================================== -->

                        <div class="hall-price">

                            ₹<?php

                            echo htmlspecialchars(
                                (string) ($hall["price"] ?? ""),
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            ?>


                            <small>
                                total price / day
                            </small>


                        </div>


                        <!-- =========================================
                             CHARGES INCLUDED IN TOTAL PRICE
                        ========================================== -->

                        <?php if (
                            !empty($hall["charges_included"])
                        ): ?>


                            <div class="charges-box">


                                <div class="charges-title">

                                    💰 Charges Included in Total Price

                                </div>


                                <div class="charges-content">

                                    <?php

                                    echo nl2br(
                                        htmlspecialchars(
                                            (string)
                                            $hall["charges_included"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        )
                                    );

                                    ?>

                                </div>


                            </div>


                        <?php endif; ?>


                        <!-- =========================================
                             BUTTONS
                        ========================================== -->

                        <div class="hall-buttons">


                            <!-- =====================================
                                 VIEW PHOTOS
                            ====================================== -->

                            <?php

                            $hasPhotos =

                                isset($hall["photos"]) &&

                                is_iterable($hall["photos"]) &&

                                count($hall["photos"]) > 0;

                            ?>


                            <?php if ($hasPhotos): ?>


                                <a

                                    href="view-hall-photos.php?id=<?php
                                    echo htmlspecialchars(
                                        (string) $hall["_id"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>"

                                    class="btn hall-button photo-button"

                                >

                                    🖼 View Photos

                                </a>


                            <?php endif; ?>


                            <!-- =====================================
                                 VIEW DETAILS
                            ====================================== -->

                            <a

                                href="hall-details.php?id=<?php
                                echo htmlspecialchars(
                                    (string) $hall["_id"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>"

                                class="btn hall-button"

                            >

                                View Details

                            </a>


                        </div>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


    <!-- =========================================
         BACK TO DASHBOARD
    ========================================== -->

    <div class="bottom-back">


        <a

            href="dashboard.php"

            class="btn"

        >

            ← Back to Dashboard

        </a>


    </div>


</div>


</body>

</html>