<?php

include "../config/database.php";
include "../config/session.php";

// Only citizens can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "citizen") {
    header("Location: ../auth/login.php");
    exit();
}

// Get current user's bookings
$userId = new MongoDB\BSON\ObjectId($_SESSION["user_id"]);

$bookings = $db->bookings->find(
    [
        "user_id" => $userId
    ],
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

    <title>My Bookings - Community Hall System</title>

    <!-- Connect CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .booking-table {
            margin-top: 25px;
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
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

        .view-btn {
            padding: 7px 12px;
            font-size: 14px;
        }

        .empty-bookings {
            background-color: white;
            padding: 30px;
            margin-top: 25px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        @media (max-width: 768px) {

            .page-header {
                display: block;
            }

            .page-header .btn {
                margin-top: 15px;
            }

        }

    </style>

</head>

<body>

    <div class="container">

        <div class="page-header">

            <div>

                <h1>My Bookings</h1>

                <p>
                    View and manage your community hall booking requests.
                </p>

            </div>

            <a href="dashboard.php" class="btn">
                Back to Dashboard
            </a>

        </div>

        <hr>

        <?php if ($db->bookings->countDocuments([
            "user_id" => $userId
        ]) === 0): ?>

            <div class="empty-bookings">

                <h2>No Bookings Found</h2>

                <p>
                    You have not submitted any hall booking requests yet.
                </p>

                <br>

                <a href="view-halls.php" class="btn">
                    Browse Available Halls
                </a>

            </div>

        <?php else: ?>

            <div style="overflow-x: auto;">

                <table class="booking-table">

                    <tr>

                        <th>Hall</th>

                        <th>Booking Date</th>

                        <th>Event Type</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                    <?php foreach ($bookings as $booking): ?>

                        <?php

                        // Find hall details
                        $hall = $db->halls->findOne([
                            "_id" => $booking["hall_id"]
                        ]);

                        $status = strtolower($booking["status"]);

                        ?>

                        <tr>

                            <td>

                                <?php

                                echo $hall
                                    ? htmlspecialchars($hall["hall_name"])
                                    : "Hall not found";

                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $booking["booking_date"]
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $booking["event_type"]
                                );
                                ?>

                            </td>

                            <td>

                                <span class="status status-<?php echo htmlspecialchars($status); ?>">

                                    <?php
                                    echo htmlspecialchars(
                                        $booking["status"]
                                    );
                                    ?>

                                </span>

                            </td>

                            <td>

                                <a
                                    href="booking-details.php?id=<?php echo $booking["_id"]; ?>"
                                    class="btn view-btn"
                                >
                                    View Details
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </table>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>