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
// CHECK HALL ID
// =========================================

if (!isset($_GET["id"])) {

    header("Location: manage-halls.php");

    exit();

}


// =========================================
// CONVERT ID
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


$message = "";

$messageType = "";


// =========================================
// UPDATE HALL
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // =========================================
    // GET FORM DATA
    // =========================================

    $hallName =
        trim($_POST["hall_name"] ?? "");

    $location =
        trim($_POST["location"] ?? "");

    $capacity =
        (int) ($_POST["capacity"] ?? 0);

    $price =
        (float) ($_POST["price"] ?? 0);

    $description =
        trim($_POST["description"] ?? "");

    $facilities =
        trim($_POST["facilities"] ?? "");


    // =========================================
    // PAYMENT INFORMATION
    // =========================================

    $paymentPhone =
        trim($_POST["payment_phone"] ?? "");

    $upiId =
        trim($_POST["upi_id"] ?? "");

    $chargesIncluded =
        trim($_POST["charges_included"] ?? "");


    // =========================================
    // BASIC VALIDATION
    // =========================================

    if (
        $hallName === "" ||
        $location === ""
    ) {

        $message =
            "Please enter the hall name and location.";

        $messageType =
            "error";


    } elseif ($capacity <= 0) {

        $message =
            "Capacity must be greater than 0.";

        $messageType =
            "error";


    } elseif ($price < 0) {

        $message =
            "Price cannot be negative.";

        $messageType =
            "error";


    // =========================================
    // PAYMENT PHONE VALIDATION
    // =========================================

    } elseif ($paymentPhone === "") {

        $message =
            "Please enter the payment phone number.";

        $messageType =
            "error";


    // =========================================
    // UPI ID VALIDATION
    // =========================================

    } elseif ($upiId === "") {

        $message =
            "Please enter the UPI ID.";

        $messageType =
            "error";


    // =========================================
    // CHARGES VALIDATION
    // =========================================

    } elseif ($chargesIncluded === "") {

        $message =
            "Please explain what charges are included in the total price.";

        $messageType =
            "error";


    } else {


        // =========================================
        // KEEP EXISTING VIDEO
        // =========================================

        $videoName =
            $hall["video"] ?? "";


        // =========================================
        // KEEP EXISTING PHOTOS
        // =========================================

        $existingPhotos = [];

        if (
            isset($hall["photos"]) &&
            is_iterable($hall["photos"])
        ) {

            foreach ($hall["photos"] as $photo) {

                $existingPhotos[] =
                    (string) $photo;

            }

        }


        // =========================================
        // PHOTOS TO REMOVE
        // =========================================

        $removePhotos =
            $_POST["remove_photos"] ?? [];


        if (!is_array($removePhotos)) {

            $removePhotos = [];

        }


        // Make sure only existing photos can be removed

        $removePhotos =
            array_values(
                array_intersect(
                    $existingPhotos,
                    $removePhotos
                )
            );


        // =========================================
        // CREATE PHOTO DIRECTORY
        // =========================================

        $photoUploadDirectory =
            __DIR__ .
            "/../uploads/hall-photos/";


        if (
            !is_dir(
                $photoUploadDirectory
            )
        ) {

            mkdir(
                $photoUploadDirectory,
                0755,
                true
            );

        }


        // =========================================
        // DELETE SELECTED OLD PHOTOS
        // =========================================

        foreach (
            $removePhotos
            as
            $removePhoto
        ) {

            $removePhotoPath =
                $photoUploadDirectory .
                basename($removePhoto);


            if (
                file_exists(
                    $removePhotoPath
                )
            ) {

                unlink(
                    $removePhotoPath
                );

            }

        }


        // =========================================
        // REMOVE THEM FROM PHOTO ARRAY
        // =========================================

        $remainingPhotos =
            array_values(
                array_diff(
                    $existingPhotos,
                    $removePhotos
                )
            );


        // =========================================
        // CHECK NEW PHOTO UPLOADS
        // =========================================

        $newUploadedPhotos = [];

        $photoUploadError = false;


        if (
            isset($_FILES["hall_photos"]) &&
            isset($_FILES["hall_photos"]["name"]) &&
            is_array($_FILES["hall_photos"]["name"])
        ) {


            $photoCount =
                count(
                    $_FILES["hall_photos"]["name"]
                );


            // =========================================
            // COUNT TOTAL PHOTOS
            // =========================================

            $validNewPhotoCount = 0;


            for (
                $i = 0;
                $i < $photoCount;
                $i++
            ) {

                if (
                    $_FILES["hall_photos"]["name"][$i] !== "" &&
                    $_FILES["hall_photos"]["error"][$i]
                    === UPLOAD_ERR_OK
                ) {

                    $validNewPhotoCount++;

                }

            }


            // =========================================
            // MAXIMUM 10 PHOTOS
            // =========================================

            if (
                count($remainingPhotos) +
                $validNewPhotoCount
                > 10
            ) {

                $photoUploadError = true;

                $message =
                    "A maximum of 10 hall photos are allowed.";

                $messageType =
                    "error";

            }


            // =========================================
            // UPLOAD NEW PHOTOS
            // =========================================

            if (!$photoUploadError) {


                $allowedImageTypes = [

                    "image/jpeg" => "jpg",

                    "image/png" => "png",

                    "image/webp" => "webp"

                ];


                $finfo =
                    finfo_open(
                        FILEINFO_MIME_TYPE
                    );


                for (
                    $i = 0;
                    $i < $photoCount;
                    $i++
                ) {


                    // Ignore empty upload fields

                    if (
                        $_FILES["hall_photos"]["name"][$i]
                        === ""
                    ) {

                        continue;

                    }


                    // =========================================
                    // UPLOAD ERROR
                    // =========================================

                    if (
                        $_FILES["hall_photos"]["error"][$i]
                        !== UPLOAD_ERR_OK
                    ) {

                        $photoUploadError = true;

                        $message =
                            "There was a problem uploading one of the photos.";

                        break;

                    }


                    // =========================================
                    // PHOTO SIZE
                    // =========================================

                    if (
                        $_FILES["hall_photos"]["size"][$i]
                        > 5 * 1024 * 1024
                    ) {

                        $photoUploadError = true;

                        $message =
                            "Each photo must not be larger than 5 MB.";

                        break;

                    }


                    // =========================================
                    // PHOTO MIME TYPE
                    // =========================================

                    $photoMimeType =
                        finfo_file(
                            $finfo,
                            $_FILES["hall_photos"]["tmp_name"][$i]
                        );


                    if (
                        !isset(
                            $allowedImageTypes[
                                $photoMimeType
                            ]
                        )
                    ) {

                        $photoUploadError = true;

                        $message =
                            "Only JPG, PNG, and WEBP photos are allowed.";

                        break;

                    }


                    // =========================================
                    // PHOTO EXTENSION
                    // =========================================

                    $photoExtension =
                        $allowedImageTypes[
                            $photoMimeType
                        ];


                    // =========================================
                    // CREATE PHOTO NAME
                    // =========================================

                    $newPhotoName =
                        "hall_photo_" .
                        uniqid() .
                        "_" .
                        time() .
                        "_" .
                        $i .
                        "." .
                        $photoExtension;


                    $newPhotoPath =
                        $photoUploadDirectory .
                        $newPhotoName;


                    // =========================================
                    // MOVE PHOTO
                    // =========================================

                    if (
                        move_uploaded_file(
                            $_FILES["hall_photos"]["tmp_name"][$i],
                            $newPhotoPath
                        )
                    ) {

                        $newUploadedPhotos[] =
                            $newPhotoName;

                    } else {

                        $photoUploadError = true;

                        $message =
                            "Unable to save one of the hall photos.";

                        break;

                    }

                }


                finfo_close($finfo);

            }


            // =========================================
            // CLEAN UP IF PHOTO UPLOAD FAILED
            // =========================================

            if ($photoUploadError) {

                foreach (
                    $newUploadedPhotos
                    as
                    $newPhoto
                ) {

                    $newPhotoPath =
                        $photoUploadDirectory .
                        $newPhoto;


                    if (
                        file_exists(
                            $newPhotoPath
                        )
                    ) {

                        unlink(
                            $newPhotoPath
                        );

                    }

                }

                $newUploadedPhotos = [];

            }

        }


        // =========================================
        // UPDATE DATABASE ONLY IF PHOTO OPERATION
        // WAS SUCCESSFUL
        // =========================================

        if (!$photoUploadError) {


            // =========================================
            // FINAL PHOTO ARRAY
            // =========================================

            $finalPhotos =
                array_merge(
                    $remainingPhotos,
                    $newUploadedPhotos
                );


            // =========================================
            // CHECK NEW VIDEO
            // =========================================

            if (
                isset($_FILES["hall_video"]) &&
                $_FILES["hall_video"]["error"]
                !== UPLOAD_ERR_NO_FILE
            ) {


                $video =
                    $_FILES["hall_video"];


                // =========================================
                // VIDEO UPLOAD ERROR
                // =========================================

                if (
                    $video["error"]
                    !== UPLOAD_ERR_OK
                ) {

                    $message =
                        "There was a problem uploading the video.";

                    $messageType =
                        "error";

                }


                // =========================================
                // VIDEO SIZE
                // =========================================

                elseif (
                    $video["size"]
                    > 50 * 1024 * 1024
                ) {

                    $message =
                        "Video must not be larger than 50 MB.";

                    $messageType =
                        "error";

                }


                else {


                    // =========================================
                    // VIDEO MIME TYPE
                    // =========================================

                    $finfo =
                        finfo_open(
                            FILEINFO_MIME_TYPE
                        );


                    $videoMimeType =
                        finfo_file(
                            $finfo,
                            $video["tmp_name"]
                        );


                    finfo_close(
                        $finfo
                    );


                    $allowedVideoTypes = [

                        "video/mp4" => "mp4",

                        "video/webm" => "webm",

                        "video/ogg" => "ogv"

                    ];


                    if (
                        !isset(
                            $allowedVideoTypes[
                                $videoMimeType
                            ]
                        )
                    ) {

                        $message =
                            "Invalid video format. Please upload MP4, WebM, or OGG.";

                        $messageType =
                            "error";

                    }


                    else {


                        // =========================================
                        // VIDEO DIRECTORY
                        // =========================================

                        $videoUploadDirectory =
                            __DIR__ .
                            "/../uploads/halls/";


                        if (
                            !is_dir(
                                $videoUploadDirectory
                            )
                        ) {

                            mkdir(
                                $videoUploadDirectory,
                                0755,
                                true
                            );

                        }


                        // =========================================
                        // CREATE NEW VIDEO NAME
                        // =========================================

                        $videoExtension =
                            $allowedVideoTypes[
                                $videoMimeType
                            ];


                        $newVideoName =
                            "hall_video_" .
                            uniqid() .
                            "_" .
                            time() .
                            "." .
                            $videoExtension;


                        $newVideoPath =
                            $videoUploadDirectory .
                            $newVideoName;


                        // =========================================
                        // MOVE NEW VIDEO
                        // =========================================

                        if (
                            !move_uploaded_file(
                                $video["tmp_name"],
                                $newVideoPath
                            )
                        ) {

                            $message =
                                "Unable to save the new hall video.";

                            $messageType =
                                "error";

                        }


                        else {


                            // =========================================
                            // DELETE OLD VIDEO
                            // =========================================

                            if (
                                !empty($videoName)
                            ) {

                                $oldVideoPath =
                                    $videoUploadDirectory .
                                    basename($videoName);


                                if (
                                    file_exists(
                                        $oldVideoPath
                                    )
                                ) {

                                    unlink(
                                        $oldVideoPath
                                    );

                                }

                            }


                            $videoName =
                                $newVideoName;

                        }

                    }

                }

            }


            // =========================================
            // SAVE EVERYTHING
            // =========================================

            if (
                $messageType !== "error"
            ) {


                try {

                    $db->halls->updateOne(

                        [
                            "_id" => $hallId
                        ],

                        [
                            '$set' => [

                                "hall_name" =>
                                    $hallName,

                                "location" =>
                                    $location,

                                "capacity" =>
                                    $capacity,

                                "price" =>
                                    $price,

                                "description" =>
                                    $description,

                                "facilities" =>
                                    $facilities,


                                // =========================================
                                // PAYMENT INFORMATION
                                // =========================================

                                "payment_phone" =>
                                    $paymentPhone,

                                "upi_id" =>
                                    $upiId,

                                "charges_included" =>
                                    $chargesIncluded,


                                "video" =>
                                    $videoName,

                                "photos" =>
                                    $finalPhotos

                            ]
                        ]

                    );


                    // =========================================
                    // RELOAD HALL
                    // =========================================

                    $hall =
                        $db->halls->findOne([
                            "_id" => $hallId
                        ]);


                    $message =
                        "Hall updated successfully!";

                    $messageType =
                        "success";


                } catch (Exception $e) {

                    $message =
                        "Something went wrong while updating the hall.";

                    $messageType =
                        "error";

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
        Edit Hall - Community Hall System
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>


        /* =========================================
           FORM CARD
        ========================================= */

        .form-card {

            background-color: white;

            max-width: 850px;

            margin: 30px auto;

            padding: 35px;

            border-radius: 10px;

            box-shadow:
                0 3px 12px rgba(0, 0, 0, 0.08);

        }


        .form-title {

            color: #1f3c88;

            margin-bottom: 10px;

        }


        .form-description {

            color: #666;

            margin-bottom: 25px;

        }


        .admin-form {

            max-width: 100%;

            padding: 0;

            box-shadow: none;

        }


        .form-group {

            margin-bottom: 22px;

        }


        .form-group label {

            display: block;

            font-weight: bold;

            color: #444;

            margin-bottom: 7px;

        }


        /* =========================================
           PAYMENT BOX
        ========================================= */

        .payment-section {

            background-color: #eef3ff;

            padding: 20px;

            border-radius: 8px;

            border-left:
                4px solid #1f3c88;

            margin-bottom: 20px;

        }


        .payment-section h3 {

            color: #1f3c88;

            margin-top: 0;

            margin-bottom: 18px;

        }


        .form-help {

            color: #777;

            font-size: 13px;

            margin-top: 7px;

            line-height: 1.6;

        }


        /* =========================================
           CURRENT VIDEO
        ========================================= */

        .current-video-box {

            background-color: #f8f9fc;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 15px;

        }


        .current-video {

            width: 100%;

            max-width: 650px;

            max-height: 400px;

            border-radius: 8px;

            display: block;

            background-color: #000;

        }


        .no-video {

            color: #777;

            font-size: 14px;

            margin: 0;

        }


        .video-upload-box {

            background-color: #f8f9fc;

            border: 2px dashed #cfd6e4;

            padding: 20px;

            border-radius: 8px;

        }


        .video-upload-box input {

            background-color: white;

            width: 100%;

        }


        .video-help {

            color: #777;

            font-size: 13px;

            margin-top: 8px;

            line-height: 1.6;

        }


        /* =========================================
           CURRENT PHOTOS
        ========================================= */

        .current-photos-box {

            background-color: #f8f9fc;

            padding: 20px;

            border-radius: 8px;

            border: 1px solid #e0e4ec;

        }


        .photo-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(150px, 1fr)
                );

            gap: 15px;

        }


        .photo-item {

            background-color: white;

            border: 1px solid #d6dbe5;

            border-radius: 8px;

            padding: 10px;

            overflow: hidden;

        }


        .photo-item img {

            width: 100%;

            height: 130px;

            object-fit: cover;

            border-radius: 6px;

            display: block;

            margin-bottom: 10px;

        }


        .photo-name {

            font-size: 12px;

            color: #555;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            margin-bottom: 8px;

        }


        .remove-photo-label {

            display: flex !important;

            align-items: center;

            gap: 7px;

            font-size: 13px !important;

            font-weight: normal !important;

            color: #dc3545 !important;

            cursor: pointer;

        }


        .remove-photo-label input {

            width: auto;

        }


        .no-photos {

            color: #777;

            margin: 0;

        }


        /* =========================================
           ADD PHOTOS
        ========================================= */

        .photo-upload-box {

            background-color: #f8f9fc;

            border: 2px dashed #cfd6e4;

            padding: 20px;

            border-radius: 8px;

        }


        .photo-upload-box input {

            background-color: white;

            width: 100%;

        }


        .photo-help {

            color: #777;

            font-size: 13px;

            margin-top: 8px;

            line-height: 1.6;

        }


        /* =========================================
           SELECTED NEW PHOTOS
        ========================================= */

        .selected-photo-list {

            margin-top: 15px;

            display: flex;

            flex-direction: column;

            gap: 8px;

        }


        .selected-photo-item {

            display: flex;

            justify-content: space-between;

            align-items: center;

            background-color: white;

            padding: 10px 12px;

            border-radius: 6px;

            border: 1px solid #d6dbe5;

            gap: 10px;

        }


        .selected-photo-name {

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            flex: 1;

        }


        .remove-selected-photo {

            background-color: #dc3545;

            color: white;

            border: none;

            padding: 7px 12px;

            border-radius: 5px;

            cursor: pointer;

        }


        .remove-selected-photo:hover {

            background-color: #b02a37;

        }


        .photo-counter {

            margin-top: 12px;

            font-weight: bold;

            color: #444;

        }


        /* =========================================
           ACTION BUTTONS
        ========================================= */

        .action-buttons {

            margin-top: 25px;

            display: flex;

            gap: 12px;

            flex-wrap: wrap;

        }


        .back-btn {

            background-color: #6c757d;

        }


        .back-btn:hover {

            background-color: #545b62;

        }


        /* =========================================
           MESSAGES
        ========================================= */

        .success {

            background-color: #d4edda;

            color: #155724;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            border-left:
                4px solid #198754;

        }


        .error {

            background-color: #f8d7da;

            color: #721c24;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            border-left:
                4px solid #dc3545;

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 600px) {

            .form-card {

                padding: 25px;

            }


            .photo-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .selected-photo-item {

                align-items: flex-start;

            }

        }


    </style>

</head>


<body>


<div class="container">


    <div class="form-card">


        <h1 class="form-title">

            Edit Community Hall

        </h1>


        <p class="form-description">

            Update hall details, payment information,
            video and photos.

        </p>


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


        <form

            method="POST"

            class="admin-form"

            enctype="multipart/form-data"

            id="editHallForm"

        >


            <!-- =========================================
                 HALL NAME
            ========================================== -->

            <div class="form-group">

                <label for="hall_name">

                    Hall Name

                </label>


                <input

                    type="text"

                    id="hall_name"

                    name="hall_name"

                    value="<?php

                    echo htmlspecialchars(
                        $hall["hall_name"] ?? ""
                    );

                    ?>"

                    required

                >

            </div>


            <!-- =========================================
                 LOCATION
            ========================================== -->

            <div class="form-group">

                <label for="location">

                    Location

                </label>


                <input

                    type="text"

                    id="location"

                    name="location"

                    value="<?php

                    echo htmlspecialchars(
                        $hall["location"] ?? ""
                    );

                    ?>"

                    required

                >

            </div>


            <!-- =========================================
                 CAPACITY
            ========================================== -->

            <div class="form-group">

                <label for="capacity">

                    Capacity

                </label>


                <input

                    type="number"

                    id="capacity"

                    name="capacity"

                    min="1"

                    value="<?php

                    echo htmlspecialchars(
                        $hall["capacity"] ?? ""
                    );

                    ?>"

                    required

                >

            </div>


            <!-- =========================================
                 PRICE
            ========================================== -->

            <div class="form-group">

                <label for="price">

                    Price per Day (₹)

                </label>


                <input

                    type="number"

                    id="price"

                    name="price"

                    min="0"

                    step="0.01"

                    value="<?php

                    echo htmlspecialchars(
                        $hall["price"] ?? ""
                    );

                    ?>"

                    required

                >

            </div>


            <!-- =========================================
                 DESCRIPTION
            ========================================== -->

            <div class="form-group">

                <label for="description">

                    Description

                </label>


                <textarea

                    id="description"

                    name="description"

                    required

                ><?php

                echo htmlspecialchars(
                    $hall["description"] ?? ""
                );

                ?></textarea>

            </div>


            <!-- =========================================
                 FACILITIES
            ========================================== -->

            <div class="form-group">

                <label for="facilities">

                    Facilities

                </label>


                <textarea

                    id="facilities"

                    name="facilities"

                    required

                ><?php

                echo htmlspecialchars(
                    $hall["facilities"] ?? ""
                );

                ?></textarea>

            </div>


            <!-- =========================================
                 PAYMENT INFORMATION
            ========================================= -->

            <div class="payment-section">

                <h3>
                    💳 Payment Information
                </h3>


                <!-- PAYMENT PHONE -->

                <div class="form-group">

                    <label for="payment_phone">

                        Payment Phone Number

                    </label>


                    <input

                        type="text"

                        id="payment_phone"

                        name="payment_phone"

                        value="<?php

                        echo htmlspecialchars(
                            $hall["payment_phone"] ?? ""
                        );

                        ?>"

                        placeholder="Example: 9876543210"

                        required

                    >


                    <p class="form-help">

                        This phone number will be shown
                        to the citizen for demo payment.

                    </p>

                </div>


                <!-- UPI ID -->

                <div class="form-group">

                    <label for="upi_id">

                        UPI ID

                    </label>


                    <input

                        type="text"

                        id="upi_id"

                        name="upi_id"

                        value="<?php

                        echo htmlspecialchars(
                            $hall["upi_id"] ?? ""
                        );

                        ?>"

                        placeholder="Example: communityhall@upi"

                        required

                    >


                    <p class="form-help">

                        This UPI ID will be shown
                        to the citizen while booking.

                    </p>

                </div>


                <!-- CHARGES INCLUDED -->

                <div class="form-group">

                    <label for="charges_included">

                        Charges Included in Total Price

                    </label>


                    <textarea

                        id="charges_included"

                        name="charges_included"

                        placeholder="Example:
Hall rental: ₹5000
Cleaning charge: ₹500
Electricity charge: ₹300
Security deposit: ₹1000"

                        required

                    ><?php

                    echo htmlspecialchars(
                        $hall["charges_included"] ?? ""
                    );

                    ?></textarea>


                    <p class="form-help">

                        Clearly explain all charges included
                        in the total price. Citizens will be
                        able to view this before payment.

                    </p>

                </div>

            </div>


            <!-- =========================================
                 CURRENT VIDEO
            ========================================== -->

            <div class="form-group">

                <label>

                    Current Hall Video

                </label>


                <div class="current-video-box">


                    <?php if (!empty($hall["video"])): ?>


                        <video

                            class="current-video"

                            autoplay

                            muted

                            loop

                            playsinline

                            controls

                            preload="auto"

                        >


                            <source

                                src="../uploads/halls/<?php

                                echo htmlspecialchars(
                                    $hall["video"]
                                );

                                ?>"

                            >


                            Your browser does not support video playback.


                        </video>


                    <?php else: ?>


                        <p class="no-video">

                            No video has been uploaded
                            for this hall yet.

                        </p>


                    <?php endif; ?>


                </div>

            </div>


            <!-- =========================================
                 REPLACE VIDEO
            ========================================== -->

            <div class="form-group">


                <label for="hall_video">

                    Replace Hall Video

                </label>


                <div class="video-upload-box">


                    <input

                        type="file"

                        id="hall_video"

                        name="hall_video"

                        accept="video/mp4,video/webm,video/ogg"

                    >


                </div>


                <p class="video-help">

                    Leave this empty to keep the current video.

                    <br>

                    Supported formats:
                    MP4, WebM, OGG.

                    <br>

                    Maximum video size:
                    <strong>50 MB</strong>.

                </p>


            </div>


            <!-- =========================================
                 CURRENT PHOTOS
            ========================================== -->

            <div class="form-group">


                <label>

                    Current Hall Photos

                </label>


                <div class="current-photos-box">


                    <?php

                    $currentPhotos = [];


                    if (
                        isset($hall["photos"]) &&
                        is_iterable($hall["photos"])
                    ) {

                        foreach (
                            $hall["photos"]
                            as
                            $photo
                        ) {

                            $currentPhotos[] =
                                (string) $photo;

                        }

                    }

                    ?>


                    <?php if (count($currentPhotos) > 0): ?>


                        <div class="photo-grid">


                            <?php foreach (
                                $currentPhotos
                                as
                                $photo
                            ): ?>


                                <div class="photo-item">


                                    <img

                                        src="../uploads/hall-photos/<?php

                                        echo htmlspecialchars(
                                            $photo
                                        );

                                        ?>"

                                        alt="Hall Photo"

                                    >


                                    <div class="photo-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $photo
                                        );

                                        ?>

                                    </div>


                                    <label
                                        class="remove-photo-label"
                                    >


                                        <input

                                            type="checkbox"

                                            name="remove_photos[]"

                                            value="<?php

                                            echo htmlspecialchars(
                                                $photo
                                            );

                                            ?>"

                                        >


                                        Remove Photo


                                    </label>


                                </div>


                            <?php endforeach; ?>


                        </div>


                    <?php else: ?>


                        <p class="no-photos">

                            No photos have been uploaded
                            for this hall yet.

                        </p>


                    <?php endif; ?>


                </div>


                <p class="photo-help">

                    Tick "Remove Photo" for any photo
                    you want to delete.

                    <br>

                    Removed photos cannot be recovered.

                </p>


            </div>


            <!-- =========================================
                 ADD NEW PHOTOS
            ========================================== -->

            <div class="form-group">


                <label for="photoSelector">

                    Add New Hall Photos

                </label>


                <div class="photo-upload-box">


                    <!-- ONE PERMANENT INPUT -->

                    <input

                        type="file"

                        id="photoSelector"

                        accept="image/jpeg,image/png,image/webp"

                    >


                    <!-- NEW PHOTO LIST -->

                    <div

                        class="selected-photo-list"

                        id="selectedPhotoList"

                    ></div>


                    <!-- COUNTER -->

                    <div

                        class="photo-counter"

                        id="photoCounter"

                    >

                        0 new photos selected

                    </div>


                </div>


                <p class="photo-help">

                    Select one photo at a time.

                    <br>

                    JPG, PNG and WEBP only.

                    <br>

                    Maximum 5 MB per photo.

                    <br>

                    Maximum 10 photos total
                    including the existing photos.

                </p>


                <!-- HIDDEN FILE INPUTS -->

                <div
                    id="hiddenPhotoInputs"
                ></div>


            </div>


            <!-- =========================================
                 BUTTONS
            ========================================== -->

            <div class="action-buttons">


                <button type="submit">

                    Update Hall

                </button>


                <a

                    href="manage-halls.php"

                    class="btn back-btn"

                >

                    Back to Manage Halls

                </a>


            </div>


        </form>


    </div>


</div>


<script>


// =========================================
// PHOTO SELECTOR
// =========================================

const photoSelector =
    document.getElementById(
        "photoSelector"
    );


const selectedPhotoList =
    document.getElementById(
        "selectedPhotoList"
    );


const hiddenPhotoInputs =
    document.getElementById(
        "hiddenPhotoInputs"
    );


const photoCounter =
    document.getElementById(
        "photoCounter"
    );


const editHallForm =
    document.getElementById(
        "editHallForm"
    );


const maxPhotos = 10;


// =========================================
// EXISTING PHOTO COUNT
// =========================================

const existingPhotoCount =
    <?php echo count($currentPhotos); ?>;


// =========================================
// STORE NEW PHOTOS
// =========================================

let selectedPhotos = [];


// =========================================
// UPDATE NEW PHOTO LIST
// =========================================

function updatePhotoList() {


    selectedPhotoList.innerHTML = "";

    hiddenPhotoInputs.innerHTML = "";


    selectedPhotos.forEach(

        function(photo, index) {


            // =========================================
            // CREATE ROW
            // =========================================

            const item =
                document.createElement(
                    "div"
                );


            item.className =
                "selected-photo-item";


            // =========================================
            // PHOTO NAME
            // =========================================

            const photoName =
                document.createElement(
                    "span"
                );


            photoName.className =
                "selected-photo-name";


            photoName.textContent =
                (index + 1) +
                ". " +
                photo.name;


            // =========================================
            // REMOVE BUTTON
            // =========================================

            const removeButton =
                document.createElement(
                    "button"
                );


            removeButton.type =
                "button";


            removeButton.className =
                "remove-selected-photo";


            removeButton.textContent =
                "Remove";


            removeButton.addEventListener(

                "click",

                function() {


                    selectedPhotos.splice(
                        index,
                        1
                    );


                    updatePhotoList();

                }

            );


            item.appendChild(
                photoName
            );


            item.appendChild(
                removeButton
            );


            selectedPhotoList.appendChild(
                item
            );


            // =========================================
            // HIDDEN FILE INPUT
            // =========================================

            const hiddenInput =
                document.createElement(
                    "input"
                );


            hiddenInput.type =
                "file";


            hiddenInput.name =
                "hall_photos[]";


            hiddenInput.style.display =
                "none";


            const dataTransfer =
                new DataTransfer();


            dataTransfer.items.add(
                photo
            );


            hiddenInput.files =
                dataTransfer.files;


            hiddenPhotoInputs.appendChild(
                hiddenInput
            );

        }

    );


    // =========================================
    // UPDATE COUNTER
    // =========================================

    const totalPhotos =
        existingPhotoCount +
        selectedPhotos.length;


    photoCounter.textContent =
        selectedPhotos.length +
        " new photos selected (" +
        totalPhotos +
        " / " +
        maxPhotos +
        " total)";


}


// =========================================
// SELECT NEW PHOTO
// =========================================

photoSelector.addEventListener(

    "change",

    function() {


        const photo =
            this.files[0];


        if (!photo) {

            return;

        }


        // =========================================
        // MAXIMUM 10 TOTAL
        // =========================================

        if (
            existingPhotoCount +
            selectedPhotos.length
            >=
            maxPhotos
        ) {

            alert(
                "Maximum 10 photos allowed in total."
            );


            this.value = "";

            return;

        }


        // =========================================
        // PHOTO SIZE
        // =========================================

        if (
            photo.size >
            5 * 1024 * 1024
        ) {

            alert(
                "Each photo must be smaller than 5 MB."
            );


            this.value = "";

            return;

        }


        // =========================================
        // PHOTO TYPE
        // =========================================

        const allowedTypes = [

            "image/jpeg",

            "image/png",

            "image/webp"

        ];


        if (
            !allowedTypes.includes(
                photo.type
            )
        ) {

            alert(
                "Only JPG, PNG, and WEBP photos are allowed."
            );


            this.value = "";

            return;

        }


        // =========================================
        // ADD PHOTO
        // =========================================

        selectedPhotos.push(
            photo
        );


        // =========================================
        // UPDATE LIST
        // =========================================

        updatePhotoList();


        // =========================================
        // RESET SELECTOR
        // =========================================

        this.value = "";

    }

);


// =========================================
// FORM SUBMISSION CHECK
// =========================================

editHallForm.addEventListener(

    "submit",

    function(event) {


        const checkedRemovePhotos =
            document.querySelectorAll(
                'input[name="remove_photos[]"]:checked'
            ).length;


        const finalPhotoCount =
            existingPhotoCount -
            checkedRemovePhotos +
            selectedPhotos.length;


        if (
            finalPhotoCount > maxPhotos
        ) {

            event.preventDefault();


            alert(
                "You can have a maximum of 10 photos."
            );


            return;

        }

    }

);


// =========================================
// INITIALIZE
// =========================================

updatePhotoList();


</script>


</body>

</html>