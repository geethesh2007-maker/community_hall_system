<?php

include "../config/database.php";
include "../config/session.php";

$message = "";
$messageType = "";


// =========================================
// CHECK WHETHER PHONE EXISTS IN SESSION
// =========================================

if (!isset($_SESSION["reset_phone"])) {

    header("Location: forgot-password.php");
    exit();

}


$phone = $_SESSION["reset_phone"];


// =========================================
// FIND USER BY PHONE NUMBER
// =========================================

$user = $db->users->findOne([
    "phone" => $phone
]);


if (!$user) {

    unset($_SESSION["reset_phone"]);

    header("Location: forgot-password.php");
    exit();

}


// =========================================
// PROCESS PASSWORD RESET
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $newPassword =
        $_POST["new_password"] ?? "";

    $confirmPassword =
        $_POST["confirm_password"] ?? "";


    // Password length validation

    if (strlen($newPassword) < 6) {

        $message =
            "Password must be at least 6 characters long.";

        $messageType = "error";


    // Password match validation

    } elseif ($newPassword !== $confirmPassword) {

        $message =
            "Passwords do not match.";

        $messageType = "error";


    } else {

        // =========================================
        // HASH NEW PASSWORD
        // =========================================

        $hashedPassword =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


        // =========================================
        // UPDATE PASSWORD
        // =========================================

        $db->users->updateOne(

            [
                "_id" => $user["_id"]
            ],

            [
                '$set' => [
                    "password" => $hashedPassword
                ]
            ]

        );


        // =========================================
        // REMOVE RESET PHONE FROM SESSION
        // =========================================

        unset($_SESSION["reset_phone"]);


        // =========================================
        // REDIRECT TO LOGIN
        // =========================================

        header(
            "Location: login.php?reset=success"
        );

        exit();

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
        Reset Password - Community Hall System
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        .auth-page {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 30px 15px;

        }


        .auth-card {

            width: 100%;

            max-width: 450px;

            background-color: white;

            padding: 40px;

            border-radius: 12px;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.10);

        }


        .auth-header {

            text-align: center;

            margin-bottom: 30px;

        }


        .auth-header h1 {

            margin-bottom: 8px;

        }


        .auth-header p {

            color: #6c757d;

        }


        .phone-display {

            background-color: #f8f9fc;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 25px;

            text-align: center;

            font-weight: bold;

            color: #1f3c88;

            word-break: break-word;

        }


        .auth-form {

            max-width: 100%;

            padding: 0;

            box-shadow: none;

            background: transparent;

        }


        .password-wrapper {

            position: relative;

            margin-bottom: 20px;

        }


        .password-wrapper input {

            padding-right: 75px;

            margin-bottom: 0;

        }


        .toggle-password {

            position: absolute;

            right: 10px;

            top: 50%;

            transform: translateY(-50%);

            background: none;

            color: #1f3c88;

            border: none;

            padding: 5px 8px;

            font-size: 13px;

            font-weight: bold;

            cursor: pointer;

        }


        .toggle-password:hover {

            background: none;

            color: #162d66;

            transform: translateY(-50%);

        }


        .password-help {

            background-color: #eef3ff;

            border-left: 4px solid #1f3c88;

            padding: 12px 15px;

            margin-bottom: 20px;

            border-radius: 6px;

            color: #444;

            font-size: 14px;

            line-height: 1.5;

        }


        .password-help strong {

            color: #1f3c88;

        }


        .reset-button {

            width: 100%;

            padding: 13px;

            font-size: 16px;

        }


        .auth-error {

            background-color: #f8d7da;

            color: #721c24;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            border-left: 4px solid #dc3545;

        }


        .back-login {

            text-align: center;

            margin-top: 25px;

        }


        @media (max-width: 600px) {

            .auth-card {

                padding: 30px 25px;

            }

        }

    </style>

</head>


<body>

    <div class="auth-page">

        <div class="auth-card">


            <!-- HEADER -->

            <div class="auth-header">

                <h1>
                    Reset Password
                </h1>

                <p>
                    Create a new password for your account.
                </p>

            </div>


            <!-- PHONE DISPLAY -->

            <div class="phone-display">

                Phone Number:
                <?php echo htmlspecialchars($phone); ?>

            </div>


            <!-- ERROR MESSAGE -->

            <?php if ($message != ""): ?>

                <div class="auth-error">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <!-- PASSWORD HELP -->

            <div class="password-help">

                <strong>
                    💡 Create a strong password:
                </strong>

                <br>

                Use at least
                <strong>6 characters</strong>
                and try to combine

                <strong>
                    letters, numbers, and special characters
                </strong>.

                <br>

                Example:
                <strong>Hall@123</strong>

                <br>

                Avoid simple passwords like
                <strong>123456</strong> or
                <strong>password</strong>.

            </div>


            <!-- RESET FORM -->

            <form
                method="POST"
                class="auth-form"
            >


                <!-- NEW PASSWORD -->

                <label for="new_password">
                    New Password
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        placeholder="Enter new password"
                        minlength="6"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword(
                            'new_password',
                            'toggleNew'
                        )"
                        id="toggleNew"
                    >
                        Show
                    </button>

                </div>


                <!-- CONFIRM PASSWORD -->

                <label for="confirm_password">
                    Confirm New Password
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm new password"
                        minlength="6"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword(
                            'confirm_password',
                            'toggleConfirm'
                        )"
                        id="toggleConfirm"
                    >
                        Show
                    </button>

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    class="reset-button"
                >
                    Change Password
                </button>

            </form>


            <!-- BACK TO LOGIN -->

            <div class="back-login">

                <a href="login.php">
                    ← Back to Login
                </a>

            </div>

        </div>

    </div>


    <script>

        function togglePassword(
            inputId,
            buttonId
        ) {

            const passwordInput =
                document.getElementById(inputId);

            const toggleButton =
                document.getElementById(buttonId);


            if (
                passwordInput.type === "password"
            ) {

                passwordInput.type = "text";

                toggleButton.textContent = "Hide";

            } else {

                passwordInput.type = "password";

                toggleButton.textContent = "Show";

            }

        }

    </script>

</body>

</html>