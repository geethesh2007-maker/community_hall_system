<?php

include "../config/session.php";

// Only citizens can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "citizen") {
    header("Location: ../auth/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Citizen Dashboard - Harmony Halls</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .welcome-section {
            background: linear-gradient(
                135deg,
                #1f3c88,
                #315bb5
            );
            color: white;
            padding: 35px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 6px 20px rgba(31, 60, 136, 0.20);
        }

        .welcome-section h1 {
            color: white;
            margin-bottom: 8px;
        }

        .welcome-section p {
            margin: 0;
            color: #e5ecff;
        }

        .dashboard-title {
            margin-bottom: 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-top: 25px;
        }

        .dashboard-card {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #1f3c88;
            transition: 0.2s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.12);
        }

        .dashboard-card h2 {
            color: #1f3c88;
            margin-bottom: 10px;
        }

        .dashboard-card p {
            color: #6c757d;
            margin-bottom: 20px;
        }

        .dashboard-icon {
            font-size: 35px;
            margin-bottom: 15px;
        }

        .quick-info {
            background-color: #eef3ff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            border-left: 4px solid #1f3c88;
        }

        .quick-info h3 {
            margin-top: 0;
        }

        .logout-section {
            margin-top: 35px;
            text-align: center;
        }

        .logout-btn {
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: #b02a37;
        }

        @media (max-width: 768px) {

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .welcome-section {
                padding: 25px;
            }

        }

    </style>

</head>

<body>

    <div class="container">


        <!-- Welcome Section -->

        <div class="welcome-section">

            <h1>
                Welcome,
                <?php echo htmlspecialchars($_SESSION["name"]); ?>!
            </h1>

            <p>
                Welcome to the  Harmony Halls Booking System.
                Find the perfect hall for your event.
            </p>

        </div>


        <!-- Dashboard Heading -->

        <div class="dashboard-title">

            <h2>Customer Dashboard</h2>

            <p>
                What would you like to do today?
            </p>

        </div>


        <!-- Dashboard Cards -->

        <div class="dashboard-grid">


            <!-- View Halls -->

            <div class="dashboard-card">

                <div class="dashboard-icon">
                    🏢
                </div>

                <h2>Available Halls</h2>

                <p>
                    Browse community halls, check their facilities,
                    capacity, location and pricing.
                </p>

                <a
                    href="view-halls.php"
                    class="btn"
                >
                    View Available Halls
                </a>

            </div>


            <!-- My Bookings -->

            <div class="dashboard-card">

                <div class="dashboard-icon">
                    📅
                </div>

                <h2>My Bookings</h2>

                <p>
                    View your booking requests and check their
                    current approval status.
                </p>

                <a
                    href="my-bookings.php"
                    class="btn"
                >
                    View My Bookings
                </a>

            </div>

        </div>


        <!-- Quick Information -->

        <div class="quick-info">

            <h3>How it works</h3>

            <p>
                <strong>1.</strong>
                Browse available community halls.
            </p>

            <p>
                <strong>2.</strong>
                Select a hall and submit your booking request.
            </p>

            <p>
                <strong>3.</strong>
                Wait for the administrator to review your request.
            </p>

            <p>
                <strong>4.</strong>
                Check your booking status under
                <strong>My Bookings</strong>.
            </p>

        </div>


        <!-- Logout -->

        <div class="logout-section">

            <a
                href="../auth/logout.php"
                class="btn logout-btn"
            >
                Logout
            </a>

        </div>

    </div>

</body>

</html>