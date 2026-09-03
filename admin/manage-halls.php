<?php

include "../config/database.php";
include "../config/session.php";

// Only admin can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Get all halls from MongoDB
$halls = $db->halls->find();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Manage Halls - Community Hall System</title>

    <!-- Connect CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .hall-table {
            margin-top: 25px;
        }

        .action-btn {
            padding: 7px 12px;
            font-size: 14px;
        }

        .edit-btn {
            background-color: #198754;
        }

        .edit-btn:hover {
            background-color: #146c43;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .delete-btn:hover {
            background-color: #b02a37;
        }

        .empty-halls {
            background-color: white;
            padding: 30px;
            margin-top: 25px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .price {
            font-weight: bold;
            color: #1f3c88;
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

                <h1>Manage Community Halls</h1>

                <p>
                    Add, edit, or remove community halls from the system.
                </p>

            </div>

            <a href="add-hall.php" class="btn">
                + Add New Hall
            </a>

        </div>

        <hr>


        <?php if ($db->halls->countDocuments() === 0): ?>

            <div class="empty-halls">

                <h2>No Halls Found</h2>

                <p>
                    There are currently no community halls in the system.
                </p>

                <br>

                <a href="add-hall.php" class="btn">
                    Add Your First Hall
                </a>

            </div>

        <?php else: ?>

            <div style="overflow-x: auto;">

                <table class="hall-table">

                    <tr>

                        <th>Hall Name</th>

                        <th>Location</th>

                        <th>Capacity</th>

                        <th>Price per Day</th>

                        <th>Action</th>

                    </tr>


                    <?php foreach ($halls as $hall): ?>

                        <tr>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $hall["hall_name"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $hall["location"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $hall["capacity"]
                                );
                                ?>

                                people

                            </td>


                            <td class="price">

                                ₹<?php
                                echo htmlspecialchars(
                                    $hall["price"]
                                );
                                ?>

                            </td>


                            <td>

                                <a
                                    href="edit-hall.php?id=<?php echo $hall["_id"]; ?>"
                                    class="btn action-btn edit-btn"
                                >
                                    Edit
                                </a>


                                <a
                                    href="delete-hall.php?id=<?php echo $hall["_id"]; ?>"
                                    class="btn action-btn delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this hall?');"
                                >
                                    Delete
                                </a>

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