<?php

include "../config/database.php";
include "../config/session.php";

// Only admin can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Get all citizens only
$users = $db->users->find(
    [
        "role" => "citizen"
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

    <title>Manage Users - Community Hall System</title>

    <!-- Connect CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .users-table {
            margin-top: 25px;
        }

        .user-name {
            font-weight: bold;
            color: #1f3c88;
        }

        .email {
            color: #555;
        }

        .phone {
            color: #555;
        }

        .registered-date {
            color: #666;
            font-size: 14px;
        }

        .empty-users {
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

        }

    </style>

</head>

<body>

    <div class="container">

        <div class="page-header">

            <div>

                <h1>Manage Registered Customers</h1>

                <p>
                    View all customers registered in the Community Hall System.
                </p>

            </div>

            <a href="dashboard.php" class="btn">
                Back to Dashboard
            </a>

        </div>

        <hr>


        <?php if ($db->users->countDocuments(["role" => "citizen"]) === 0): ?>

            <div class="empty-users">

                <h2>No Citizens Found</h2>

                <p>
                    There are currently no registered citizens.
                </p>

            </div>

        <?php else: ?>

            <div style="overflow-x: auto;">

                <table class="users-table">

                    <tr>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Phone</th>

                        <th>Registered On</th>

                    </tr>


                    <?php foreach ($users as $user): ?>

                        <tr>

                            <td class="user-name">

                                <?php
                                echo htmlspecialchars(
                                    $user["name"]
                                );
                                ?>

                            </td>


                            <td class="email">

                                <?php
                                echo htmlspecialchars(
                                    $user["email"]
                                );
                                ?>

                            </td>


                            <td class="phone">

                                <?php
                                echo htmlspecialchars(
                                    $user["phone"]
                                );
                                ?>

                            </td>


                            <td class="registered-date">

                                <?php

                                if (isset($user["created_at"])) {

                                    echo $user["created_at"]
                                        ->toDateTime()
                                        ->format("d-m-Y H:i");

                                } else {

                                    echo "Not available";

                                }

                                ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </table>

            </div>

        <?php endif; ?>


        <br>

        <a href="dashboard.php" class="btn">
            Back to Dashboard
        </a>

    </div>

</body>

</html>