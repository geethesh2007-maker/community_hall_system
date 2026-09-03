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

    $bookingId = new MongoDB\BSON\ObjectId($_GET["id"]);

} catch (Exception $e) {

    die("Invalid Booking ID!");

}


// =========================================
// FIND BOOKING
// =========================================

$booking = $db->bookings->findOne([
    "_id" => $bookingId
]);

if (!$booking) {
    die("Booking not found!");
}


// =========================================
// ONLY REJECTED BOOKINGS CAN BE EDITED
// =========================================

if (($booking["status"] ?? "") !== "rejected") {
    die("Only rejected bookings can be edited!");
}


$message = "";
$messageType = "";


// =========================================
// CURRENT REFUND SCREENSHOT
// =========================================

$currentRefundScreenshot =
    (string)($booking["refund_screenshot"] ?? "");


// =========================================
// PROCESS FORM
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // =========================================
    // GET REJECTION REASON
    // =========================================

    $rejectionReason =
        trim(
            $_POST["rejection_reason"] ?? ""
        );


    // =========================================
    // GET NEW REFUND SCREENSHOT
    // =========================================

    $refundScreenshot =
        $_FILES["refund_screenshot"] ?? null;


    // =========================================
    // VALIDATE REASON
    // =========================================

    if (empty($rejectionReason)) {

        $message =
            "Please enter a rejection reason.";

        $messageType =
            "error";

    } else {


        // =========================================
        // DEFAULT VALUES
        // =========================================

        $newRefundFileName =
            $currentRefundScreenshot;

        $newRefundFilePath = null;

        $oldRefundFileName =
            $currentRefundScreenshot;


        // =========================================
        // CHECK IF NEW IMAGE WAS UPLOADED
        // =========================================

        $hasNewRefund =
            $refundScreenshot &&
            isset($refundScreenshot["error"]) &&
            $refundScreenshot["error"] !== UPLOAD_ERR_NO_FILE;


        if ($hasNewRefund) {


            // =====================================
            // UPLOAD ERROR
            // =====================================

            if (
                $refundScreenshot["error"] !==
                UPLOAD_ERR_OK
            ) {

                $message =
                    "There was a problem uploading the refund screenshot.";

                $messageType =
                    "error";


            // =====================================
            // FILE SIZE
            // =====================================

            } elseif (
                $refundScreenshot["size"] >
                5 * 1024 * 1024
            ) {

                $message =
                    "Refund screenshot must not be larger than 5 MB.";

                $messageType =
                    "error";


            } else {


                // =====================================
                // CHECK MIME TYPE
                // =====================================

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


                    // =====================================
                    // CREATE REFUND DIRECTORY
                    // =====================================

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


                    // =====================================
                    // CREATE NEW FILE NAME
                    // =====================================

                    $refundExtension =
                        $allowedRefundTypes[
                            $refundMimeType
                        ];


                    $newRefundFileName =
                        "refund_" .
                        (string)$bookingId .
                        "_" .
                        uniqid() .
                        "_" .
                        time() .
                        "." .
                        $refundExtension;


                    $newRefundFilePath =
                        $refundUploadDirectory .
                        $newRefundFileName;


                    // =====================================
                    // SAVE NEW IMAGE
                    // =====================================

                    if (
                        !move_uploaded_file(
                            $refundScreenshot["tmp_name"],
                            $newRefundFilePath
                        )
                    ) {

                        finfo_close($finfo);

                        $message =
                            "Unable to save the new refund screenshot.";

                        $messageType =
                            "error";


                    } else {

                        finfo_close($finfo);


                        // =================================
                        // UPDATE DATABASE
                        // =================================

                        $updateResult =
                            $db->bookings->updateOne(

                                [
                                    "_id" =>
                                        $bookingId
                                ],

                                [
                                    '$set' => [

                                        "rejection_reason" =>
                                            $rejectionReason,

                                        "refund_screenshot" =>
                                            $newRefundFileName,

                                        "refund_updated_at" =>
                                            new MongoDB\BSON\UTCDateTime(),

                                        "refund_updated_by" =>
                                            new MongoDB\BSON\ObjectId(
                                                $_SESSION["user_id"]
                                            )

                                    ]
                                ]

                            );


                        if (
                            $updateResult->getModifiedCount() >= 0
                        ) {


                            // =================================
                            // DELETE OLD REFUND IMAGE
                            // =================================

                            if (
                                !empty($oldRefundFileName) &&
                                $oldRefundFileName !==
                                $newRefundFileName
                            ) {

                                $oldRefundPath =
                                    __DIR__ .
                                    "/../uploads/refunds/" .
                                    basename(
                                        $oldRefundFileName
                                    );


                                if (
                                    file_exists(
                                        $oldRefundPath
                                    )
                                ) {

                                    unlink(
                                        $oldRefundPath
                                    );

                                }

                            }


                            header(
                                "Location: manage-bookings.php"
                            );

                            exit();


                        } else {


                            // Database update failed

                            if (
                                file_exists(
                                    $newRefundFilePath
                                )
                            ) {

                                unlink(
                                    $newRefundFilePath
                                );

                            }


                            $message =
                                "Unable to update the booking.";

                            $messageType =
                                "error";

                        }

                    }

                }

            }


        } else {


            // =========================================
            // NO NEW SCREENSHOT
            // UPDATE ONLY REJECTION REASON
            // =========================================

            $updateResult =
                $db->bookings->updateOne(

                    [
                        "_id" =>
                            $bookingId
                    ],

                    [
                        '$set' => [

                            "rejection_reason" =>
                                $rejectionReason,

                            "refund_updated_at" =>
                                new MongoDB\BSON\UTCDateTime(),

                            "refund_updated_by" =>
                                new MongoDB\BSON\ObjectId(
                                    $_SESSION["user_id"]
                                )

                        ]
                    ]

                );


            if (
                $updateResult->getModifiedCount() >= 0
            ) {

                header(
                    "Location: manage-bookings.php"
                );

                exit();

            } else {

                $message =
                    "Unable to update the booking.";

                $messageType =
                    "error";

            }

        }

    }

}


// =========================================
// GET CITIZEN
// =========================================

$user =
    $db->users->findOne([
        "_id" =>
            $booking["user_id"]
    ]);


// =========================================
// GET HALL
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
        Edit Rejected Booking
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        .edit-card {

            max-width: 750px;

            margin: 40px auto;

            background: white;

            padding: 35px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px
                rgba(0,0,0,0.08);

        }


        .edit-title {

            color: #1f3c88;

            margin-bottom: 10px;

        }


        .booking-summary {

            background: #f8f9fc;

            padding: 20px;

            border-radius: 8px;

            margin: 25px 0;

        }


        .booking-summary p {

            margin: 8px 0;

        }


        .form-label {

            display: block;

            font-weight: bold;

            margin-bottom: 8px;

            color: #333;

        }


        textarea {

            width: 100%;

            box-sizing: border-box;

            min-height: 140px;

            resize: vertical;

        }


        .refund-section {

            margin-top: 25px;

            padding: 22px;

            background: #eef3ff;

            border-left:
                5px solid #1f3c88;

            border-radius: 8px;

        }


        .refund-section h3 {

            color: #1f3c88;

            margin-top: 0;

        }


        .current-refund {

            margin-top: 15px;

            padding: 15px;

            background: white;

            border-radius: 8px;

        }


        .current-refund img {

            display: block;

            max-width: 100%;

            max-height: 400px;

            margin-top: 12px;

            border-radius: 8px;

            border: 1px solid #ddd;

        }


        .no-refund {

            color: #721c24;

            background: #fff5f5;

            padding: 12px;

            border-radius: 6px;

        }


        .file-help {

            color: #666;

            font-size: 13px;

            margin-top: 8px;

        }


        .action-buttons {

            display: flex;

            gap: 12px;

            margin-top: 25px;

            flex-wrap: wrap;

        }


        .save-button {

            background: #198754;

        }


        .save-button:hover {

            background: #146c43;

        }


        .cancel-button {

            background: #6c757d;

        }


        .cancel-button:hover {

            background: #545b62;

        }


        @media (max-width: 600px) {

            .edit-card {

                padding: 25px;

            }

            .action-buttons {

                flex-direction: column;

            }

            .action-buttons a,

            .action-buttons button {

                width: 100%;

                box-sizing: border-box;

                text-align: center;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="edit-card">


        <h1 class="edit-title">

            ✏️ Edit Rejected Booking

        </h1>


        <p>

            Update the rejection reason or replace the
            refund payment screenshot.

            The updated information will be visible
            to the citizen.

        </p>


        <?php if ($message !== ""): ?>

            <div class="<?php echo htmlspecialchars($messageType); ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- =========================================
             BOOKING SUMMARY
        ========================================== -->

        <div class="booking-summary">

            <p>

                <strong>
                    Citizen:
                </strong>

                <?php

                echo $user

                    ? htmlspecialchars(
                        (string)$user["name"]
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
                        (string)$hall["hall_name"]
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
                    (string)($booking["booking_date"] ?? "")
                );

                ?>

            </p>


            <p>

                <strong>
                    Event Type:
                </strong>

                <?php

                echo htmlspecialchars(
                    (string)($booking["event_type"] ?? "")
                );

                ?>

            </p>


            <p>

                <strong>
                    Status:
                </strong>

                <span style="color:#721c24;font-weight:bold;">

                    Rejected

                </span>

            </p>

        </div>


        <!-- =========================================
             EDIT FORM
        ========================================== -->

        <form

            method="POST"

            enctype="multipart/form-data"

        >


            <!-- REJECTION REASON -->

            <label
                for="rejection_reason"
                class="form-label"
            >

                Rejection Reason

            </label>


            <textarea
                id="rejection_reason"
                name="rejection_reason"
                required
            ><?php

                echo htmlspecialchars(
                    (string)(
                        $booking["rejection_reason"]
                        ?? ""
                    )
                );

            ?></textarea>


            <!-- =========================================
                 REFUND
            ========================================== -->

            <div class="refund-section">


                <h3>

                    💰 Refund Payment Proof

                </h3>


                <p>

                    You can keep the current refund
                    screenshot or upload a new one.

                </p>


                <?php if (!empty($currentRefundScreenshot)): ?>


                    <div class="current-refund">

                        <strong>
                            Current Refund Screenshot:
                        </strong>


                        <a
                            href="../uploads/refunds/<?php
                                echo urlencode(
                                    basename(
                                        $currentRefundScreenshot
                                    )
                                );
                            ?>"
                            target="_blank"
                        >

                            <img
                                src="../uploads/refunds/<?php
                                    echo htmlspecialchars(
                                        basename(
                                            $currentRefundScreenshot
                                        ),
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                ?>"
                                alt="Current Refund Screenshot"
                            >

                        </a>

                    </div>


                <?php else: ?>


                    <div class="no-refund">

                        ⚠️ No refund screenshot has
                        been uploaded yet.

                    </div>


                <?php endif; ?>


                <br>


                <label
                    for="refund_screenshot"
                    class="form-label"
                >

                    Replace Refund Screenshot

                </label>


                <input

                    type="file"

                    id="refund_screenshot"

                    name="refund_screenshot"

                    accept="image/jpeg,image/png,image/webp"

                >


                <p class="file-help">

                    Leave this empty to keep the
                    existing screenshot.

                    <br>

                    JPG, PNG or WEBP only.
                    Maximum 5 MB.

                </p>


            </div>


            <!-- =========================================
                 BUTTONS
            ========================================== -->

            <div class="action-buttons">


                <button

                    type="submit"

                    class="btn save-button"

                    onclick="
                        return confirm(
                            'Are you sure you want to save these changes?'
                        );
                    "

                >

                    💾 Save Changes

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


</body>

</html>