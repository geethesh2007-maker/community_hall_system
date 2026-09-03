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
// CHECK HALL ID
// =========================================

if (!isset($_GET["id"]) || $_GET["id"] === "") {

    header("Location: view-halls.php");
    exit();

}


try {

    $hallId = new MongoDB\BSON\ObjectId(
        $_GET["id"]
    );

} catch (Exception $e) {

    header("Location: view-halls.php");
    exit();

}


// =========================================
// GET HALL
// =========================================

$hall = $db->halls->findOne([

    "_id" => $hallId

]);


// =========================================
// CHECK IF HALL EXISTS
// =========================================

if (!$hall) {

    header("Location: view-halls.php");
    exit();

}


// =========================================
// GET PHOTOS
// =========================================

$photos = [];


// New format: photos array

if (
    isset($hall["photos"]) &&
    !empty($hall["photos"])
) {

    foreach ($hall["photos"] as $photo) {

        $photoName = trim((string) $photo);

        if ($photoName !== "") {

            $photos[] = $photoName;

        }

    }

}


// Old format fallback: single photo

if (
    empty($photos) &&
    isset($hall["photo"]) &&
    !empty($hall["photo"])
) {

    $photos[] = (string) $hall["photo"];

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
        Hall Photos - Community Hall System
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        .page-header {

            background-color: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

            margin-bottom: 35px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

        }


        .page-header h1 {

            margin: 0;

            color: #1f3c88;

        }


        .page-header p {

            margin: 0;

            color: #666;

        }


        /* =========================================
           PHOTO GRID
        ========================================== */

        .photo-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;

        }


        .photo-card {

            background-color: white;

            border-radius: 12px;

            overflow: hidden;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;

        }


        .photo-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 8px 22px rgba(0, 0, 0, 0.12);

        }


        .hall-photo {

            width: 100%;

            height: 260px;

            object-fit: cover;

            display: block;

            cursor: pointer;

        }


        .photo-number {

            padding: 15px;

            font-weight: bold;

            color: #1f3c88;

            text-align: center;

        }


        /* =========================================
           EMPTY MESSAGE
        ========================================== */

        .empty-message {

            background-color: white;

            padding: 50px 30px;

            border-radius: 12px;

            text-align: center;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

        }


        .empty-message h2 {

            color: #1f3c88;

            margin-bottom: 10px;

        }


        .empty-message p {

            color: #666;

        }


        /* =========================================
           BACK BUTTON
        ========================================== */

        .back-section {

            margin-top: 35px;

        }


        /* =========================================
           IMAGE MODAL
        ========================================== */

        .image-modal {

            display: none;

            position: fixed;

            z-index: 9999;

            left: 0;

            top: 0;

            width: 100%;

            height: 100%;

            background-color:
                rgba(0, 0, 0, 0.85);

            align-items: center;

            justify-content: center;

            padding: 20px;

            box-sizing: border-box;

        }


        .image-modal.active {

            display: flex;

        }


        .modal-image {

            max-width: 95%;

            max-height: 90vh;

            border-radius: 8px;

            object-fit: contain;

        }


        .close-modal {

            position: absolute;

            top: 20px;

            right: 30px;

            color: white;

            font-size: 40px;

            font-weight: bold;

            cursor: pointer;

            line-height: 1;

        }


        /* =========================================
           RESPONSIVE
        ========================================== */

        @media (max-width: 900px) {

            .photo-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 600px) {

            .page-header {

                flex-direction: column;

                align-items: flex-start;

            }


            .photo-grid {

                grid-template-columns: 1fr;

            }


            .hall-photo {

                height: 230px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- =========================================
         HEADER
    ========================================== -->

    <div class="page-header">


        <div>

            <h1>

                <?php

                echo htmlspecialchars(
                    $hall["hall_name"]
                );

                ?>

                - Photo Gallery

            </h1>


            <p>

                Browse photos of this community hall.

            </p>

        </div>


        <a
            href="view-halls.php"
            class="btn"
        >

            ← Back to Available Halls

        </a>


    </div>



    <!-- =========================================
         PHOTOS
    ========================================== -->

    <?php if (!empty($photos)): ?>


        <div class="photo-grid">


            <?php

            foreach (
                $photos
                as
                $index => $photo
            ):

            ?>


                <?php

                $photoUrl =

                    "../uploads/hall-photos/" .

                    rawurlencode($photo);

                ?>


                <div class="photo-card">


                    <img

                        src="<?php
                        echo htmlspecialchars(
                            $photoUrl
                        );
                        ?>"

                        alt="Hall Photo <?php
                        echo $index + 1;
                        ?>"

                        class="hall-photo"

                        onclick="
                            openImage(
                                '<?php
                                echo htmlspecialchars(
                                    $photoUrl,
                                    ENT_QUOTES
                                );
                                ?>'
                            )
                        "

                        onerror="
                            this.parentElement.style.display='none';
                        "

                    >


                    <div class="photo-number">

                        Photo

                        <?php
                        echo $index + 1;
                        ?>

                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    <?php else: ?>


        <!-- NO PHOTOS -->

        <div class="empty-message">


            <h2>

                No Photos Available

            </h2>


            <p>

                Photos for this community hall
                have not been added yet.

            </p>


        </div>


    <?php endif; ?>



    <!-- =========================================
         BACK BUTTON
    ========================================== -->

    <div class="back-section">

        <a
            href="view-halls.php"
            class="btn"
        >

            ← Back to Available Halls

        </a>

    </div>


</div>



<!-- =========================================
     IMAGE MODAL
========================================= -->

<div
    class="image-modal"
    id="imageModal"
>


    <span
        class="close-modal"
        onclick="closeImage()"
    >

        &times;

    </span>


    <img
        src=""
        id="modalImage"
        class="modal-image"
        alt="Hall Photo"
    >


</div>



<script>


    function openImage(imageSource) {

        document.getElementById(
            "modalImage"
        ).src = imageSource;


        document.getElementById(
            "imageModal"
        ).classList.add(
            "active"
        );

    }


    function closeImage() {

        document.getElementById(
            "imageModal"
        ).classList.remove(
            "active"
        );


        document.getElementById(
            "modalImage"
        ).src = "";

    }


    document.getElementById(
        "imageModal"
    ).addEventListener(

        "click",

        function (event) {

            if (
                event.target === this
            ) {

                closeImage();

            }

        }

    );


</script>


</body>

</html>