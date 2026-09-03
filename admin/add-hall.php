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


$message = "";
$messageType = "";


// =========================================
// FORM SUBMISSION
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


    // =========================================
    // VIDEO VALIDATION
    // =========================================

    } elseif (

        !isset($_FILES["hall_video"]) ||

        $_FILES["hall_video"]["error"]
        === UPLOAD_ERR_NO_FILE

    ) {

        $message =
            "Please upload a hall video.";

        $messageType =
            "error";


    // =========================================
    // PHOTO VALIDATION
    // =========================================

    } elseif (

        !isset($_FILES["hall_photos"]) ||

        !isset($_FILES["hall_photos"]["name"]) ||

        count($_FILES["hall_photos"]["name"]) === 0

    ) {

        $message =
            "Please upload at least one hall photo.";

        $messageType =
            "error";


    } else {


        // =========================================
        // GET VIDEO
        // =========================================

        $video =
            $_FILES["hall_video"];


        // =========================================
        // GET PHOTOS
        // =========================================

        $photos =
            $_FILES["hall_photos"];


        // =========================================
        // COUNT VALID PHOTOS
        // =========================================

        $validPhotoIndexes = [];


        foreach (
            $photos["name"]
            as
            $index => $photoName
        ) {

            if (

                $photoName !== "" &&

                isset($photos["error"][$index]) &&

                $photos["error"][$index]
                === UPLOAD_ERR_OK

            ) {

                $validPhotoIndexes[] =
                    $index;

            }

        }


        $photoCount =
            count($validPhotoIndexes);


        // =========================================
        // CHECK PHOTO COUNT
        // =========================================

        if ($photoCount === 0) {

            $message =
                "Please upload at least one hall photo.";

            $messageType =
                "error";


        } elseif ($photoCount > 10) {

            $message =
                "You can upload a maximum of 10 photos.";

            $messageType =
                "error";


        // =========================================
        // VIDEO ERROR
        // =========================================

        } elseif (

            $video["error"]
            !== UPLOAD_ERR_OK

        ) {

            $message =
                "There was a problem uploading the video.";

            $messageType =
                "error";


        // =========================================
        // VIDEO SIZE
        // =========================================

        } elseif (

            $video["size"]
            > 50 * 1024 * 1024

        ) {

            $message =
                "Video must not be larger than 50 MB.";

            $messageType =
                "error";


        } else {


            // =========================================
            // OPEN FILE INFO
            // =========================================

            $finfo =
                finfo_open(FILEINFO_MIME_TYPE);


            // =========================================
            // CHECK VIDEO TYPE
            // =========================================

            $videoMimeType =
                finfo_file(

                    $finfo,

                    $video["tmp_name"]

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

                finfo_close($finfo);

                $message =
                    "Invalid video format. Only MP4, WebM and OGG are allowed.";

                $messageType =
                    "error";


            } else {


                // =========================================
                // CREATE VIDEO DIRECTORY
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
                // CREATE VIDEO NAME
                // =========================================

                $videoExtension =

                    $allowedVideoTypes[
                        $videoMimeType
                    ];


                $videoName =

                    "hall_video_" .

                    uniqid() .

                    "_" .

                    time() .

                    "." .

                    $videoExtension;


                $videoPath =

                    $videoUploadDirectory .
                    $videoName;


                // =========================================
                // UPLOAD VIDEO
                // =========================================

                if (

                    !move_uploaded_file(

                        $video["tmp_name"],

                        $videoPath

                    )

                ) {

                    finfo_close($finfo);

                    $message =
                        "Unable to save the hall video.";

                    $messageType =
                        "error";


                } else {


                    // =========================================
                    // ALLOWED PHOTO TYPES
                    // =========================================

                    $allowedImageTypes = [

                        "image/jpeg" => "jpg",

                        "image/png" => "png",

                        "image/webp" => "webp"

                    ];


                    $uploadedPhotos = [];

                    $photoUploadError = false;


                    // =========================================
                    // UPLOAD ALL PHOTOS
                    // =========================================

                    foreach (

                        $validPhotoIndexes

                        as

                        $i

                    ) {


                        // =========================================
                        // PHOTO SIZE CHECK
                        // =========================================

                        if (

                            $photos["size"][$i]
                            > 5 * 1024 * 1024

                        ) {

                            $photoUploadError = true;

                            $message =
                                "Each photo must not be larger than 5 MB.";

                            break;

                        }


                        // =========================================
                        // GET PHOTO MIME TYPE
                        // =========================================

                        $photoMimeType =

                            finfo_file(

                                $finfo,

                                $photos["tmp_name"][$i]

                            );


                        // =========================================
                        // CHECK PHOTO TYPE
                        // =========================================

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
                        // CREATE PHOTO NAME
                        // =========================================

                        $photoExtension =

                            $allowedImageTypes[
                                $photoMimeType
                            ];


                        $photoName =

                            "hall_photo_" .

                            uniqid() .

                            "_" .

                            time() .

                            "_" .

                            $i .

                            "." .

                            $photoExtension;


                        $photoPath =

                            $photoUploadDirectory .
                            $photoName;


                        // =========================================
                        // MOVE PHOTO
                        // =========================================

                        if (

                            move_uploaded_file(

                                $photos["tmp_name"][$i],

                                $photoPath

                            )

                        ) {

                            $uploadedPhotos[] =
                                $photoName;

                        } else {

                            $photoUploadError = true;

                            $message =
                                "Unable to save one of the hall photos.";

                            break;

                        }

                    }


                    // =========================================
                    // CLOSE FILE INFO
                    // =========================================

                    finfo_close($finfo);


                    // =========================================
                    // IF PHOTO UPLOAD FAILED
                    // =========================================

                    if ($photoUploadError) {


                        // DELETE UPLOADED PHOTOS

                        foreach (

                            $uploadedPhotos

                            as

                            $uploadedPhoto

                        ) {

                            $filePath =

                                $photoUploadDirectory .
                                $uploadedPhoto;


                            if (
                                file_exists($filePath)
                            ) {

                                unlink($filePath);

                            }

                        }


                        // DELETE VIDEO

                        if (
                            file_exists($videoPath)
                        ) {

                            unlink($videoPath);

                        }


                        $messageType =
                            "error";


                    } else {


                        // =========================================
                        // SAVE HALL IN MONGODB
                        // =========================================

                        $db->halls->insertOne([


                            // =========================================
                            // BASIC HALL INFORMATION
                            // =========================================

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


                            // =========================================
                            // MEDIA
                            // =========================================

                            "video" =>
                                $videoName,

                            "photos" =>
                                $uploadedPhotos,


                            // =========================================
                            // CREATED DATE
                            // =========================================

                            "created_at" =>

                                new MongoDB\BSON\UTCDateTime()

                        ]);


                        $message =
                            "Community hall added successfully with video, photos and payment information!";


                        $messageType =
                            "success";

                    }

                }

            }

        }

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
        Add Hall - Community Hall System
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        .form-card {

            background-color: white;

            max-width: 700px;

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

            margin-bottom: 18px;

        }


        .form-group label {

            display: block;

            font-weight: bold;

            color: #444;

            margin-bottom: 6px;

        }


        .form-help {

            color: #777;

            font-size: 13px;

            margin-top: 7px;

            line-height: 1.6;

        }


        .upload-box {

            background-color: #f8f9fc;

            border: 2px dashed #cfd6e4;

            padding: 20px;

            border-radius: 8px;

        }


        .upload-box input {

            background-color: white;

            width: 100%;

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


        /* =========================================
           PHOTO LIST
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

            margin-top: 15px;

            font-weight: bold;

            color: #444;

        }


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


        .success {

            background-color: #d4edda;

            color: #155724;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            border-left: 4px solid #198754;

        }


        .error {

            background-color: #f8d7da;

            color: #721c24;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            border-left: 4px solid #dc3545;

        }


        @media (max-width: 600px) {

            .form-card {

                padding: 25px;

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

            Add Community Hall

        </h1>


        <p class="form-description">

            Enter hall details, payment information,
            upload one video and up to 10 photos.

        </p>


        <!-- MESSAGE -->

        <?php if ($message !== ""): ?>

            <div
                class="<?php echo htmlspecialchars($messageType); ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form

            method="POST"

            class="admin-form"

            enctype="multipart/form-data"

            id="hallForm"

        >


            <!-- =========================================
                 HALL NAME
            ========================================= -->

            <div class="form-group">

                <label for="hall_name">

                    Hall Name

                </label>

                <input

                    type="text"

                    id="hall_name"

                    name="hall_name"

                    placeholder="Enter hall name"

                    required

                >

            </div>


            <!-- =========================================
                 LOCATION
            ========================================= -->

            <div class="form-group">

                <label for="location">

                    Location

                </label>

                <input

                    type="text"

                    id="location"

                    name="location"

                    placeholder="Enter hall location"

                    required

                >

            </div>


            <!-- =========================================
                 CAPACITY
            ========================================= -->

            <div class="form-group">

                <label for="capacity">

                    Capacity

                </label>

                <input

                    type="number"

                    id="capacity"

                    name="capacity"

                    min="1"

                    placeholder="Enter maximum capacity"

                    required

                >

            </div>


            <!-- =========================================
                 PRICE
            ========================================= -->

            <div class="form-group">

                <label for="price">

                    Total Price / Price per Day (₹)

                </label>

                <input

                    type="number"

                    id="price"

                    name="price"

                    min="0"

                    step="0.01"

                    placeholder="Enter total price"

                    required

                >

            </div>


            <!-- =========================================
                 DESCRIPTION
            ========================================= -->

            <div class="form-group">

                <label for="description">

                    Description

                </label>

                <textarea

                    id="description"

                    name="description"

                    placeholder="Describe the hall"

                    required

                ></textarea>

            </div>


            <!-- =========================================
                 FACILITIES
            ========================================= -->

            <div class="form-group">

                <label for="facilities">

                    Facilities

                </label>

                <textarea

                    id="facilities"

                    name="facilities"

                    placeholder="Example: Parking, AC, Chairs, Tables, Stage"

                    required

                ></textarea>

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

                    ></textarea>

                    <p class="form-help">

                        Clearly explain all charges included
                        in the total price. Citizens will be
                        able to view this before payment.

                    </p>

                </div>

            </div>


            <!-- =========================================
                 VIDEO
            ========================================= -->

            <div class="form-group">

                <label for="hall_video">

                    Hall Video

                </label>


                <div class="upload-box">

                    <input

                        type="file"

                        id="hall_video"

                        name="hall_video"

                        accept="video/mp4,video/webm,video/ogg"

                        required

                    >

                </div>


                <p class="form-help">

                    Supported formats:
                    MP4, WebM, OGG.

                    <br>

                    Maximum video size:
                    <strong>50 MB</strong>.

                </p>

            </div>


            <!-- =========================================
                 HALL PHOTOS
            ========================================= -->

            <div class="form-group">


                <label for="photoSelector">

                    Hall Photos

                </label>


                <div class="upload-box">


                    <!-- ONE PERMANENT INPUT -->

                    <input

                        type="file"

                        id="photoSelector"

                        accept="image/jpeg,image/png,image/webp"

                    >


                    <!-- PHOTO LIST -->

                    <div

                        class="selected-photo-list"

                        id="selectedPhotoList"

                    ></div>


                    <!-- COUNTER -->

                    <div

                        class="photo-counter"

                        id="photoCounter"

                    >

                        0 / 10 photos selected

                    </div>


                </div>


                <p class="form-help">

                    Select one photo at a time.

                    <br>

                    Every selected photo will appear
                    in the list above.

                    <br>

                    Maximum:
                    <strong>10 photos</strong>.

                    <br>

                    You can remove any photo before
                    submitting.

                    <br>

                    Supported formats:
                    JPG, PNG, WEBP.

                    <br>

                    Maximum size:
                    <strong>5 MB per photo</strong>.

                </p>


                <!-- HIDDEN CONTAINER -->

                <div
                    id="hiddenPhotoInputs"
                ></div>


            </div>


            <!-- =========================================
                 BUTTONS
            ========================================= -->

            <div class="action-buttons">


                <button type="submit">

                    Add Hall

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
    // GET ELEMENTS
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


    const maxPhotos = 10;


    // =========================================
    // STORE SELECTED FILES
    // =========================================

    let selectedPhotos = [];


    // =========================================
    // UPDATE PHOTO LIST
    // =========================================

    function updatePhotoList() {


        selectedPhotoList.innerHTML = "";

        hiddenPhotoInputs.innerHTML = "";


        selectedPhotos.forEach(

            function(photo, index) {


                // CREATE DISPLAY ROW

                const item =

                    document.createElement(
                        "div"
                    );


                item.className =
                    "selected-photo-item";


                // PHOTO NAME

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


                // REMOVE BUTTON

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
                // CREATE HIDDEN FILE INPUT
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


                // CREATE FILE LIST

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

        photoCounter.textContent =

            selectedPhotos.length +

            " / " +

            maxPhotos +

            " photos selected";


    }


    // =========================================
    // SELECT PHOTO
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
            // MAXIMUM 10 PHOTOS
            // =========================================

            if (

                selectedPhotos.length
                >=
                maxPhotos

            ) {

                alert(
                    "Maximum 10 photos allowed."
                );


                this.value = "";

                return;

            }


            // =========================================
            // PHOTO SIZE
            // =========================================

            if (

                photo.size
                >
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


            // UPDATE LIST

            updatePhotoList();


            // RESET PERMANENT FIELD

            this.value = "";

        }

    );


    // =========================================
    // FORM VALIDATION
    // =========================================

    document.getElementById(
        "hallForm"
    ).addEventListener(

        "submit",

        function(event) {


            if (

                selectedPhotos.length === 0

            ) {

                event.preventDefault();


                alert(
                    "Please select at least one hall photo."
                );

            }

        }

    );


    // =========================================
    // INITIAL LIST
    // =========================================

    updatePhotoList();


</script>


</body>

</html>