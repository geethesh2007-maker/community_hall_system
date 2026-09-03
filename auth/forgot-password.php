<?php

include "../config/database.php";
include "../config/session.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $phone = trim($_POST["phone"]);

    // Validate phone number
    if (!preg_match("/^[0-9]{10}$/", $phone)) {

        $message = "Please enter a valid 10-digit phone number.";
        $messageType = "error";

    } else {

        // Find user by phone number
        $user = $db->users->findOne([
            "phone" => $phone
        ]);

        if (!$user) {

            $message = "No account was found with this phone number.";
            $messageType = "error";

        } else {

            // Store phone temporarily in session
            $_SESSION["reset_phone"] = $phone;

            // Go to reset password page
            header("Location: reset-password.php");
            exit();
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

    <title>Forgot Password - Community Hall System</title>

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


        .auth-form {

            max-width: 100%;

            padding: 0;

            box-shadow: none;

            background: transparent;

        }


        .auth-form input {

            margin-bottom: 20px;

        }


        .reset-button {

            width: 100%;

            padding: 13px;

            font-size: 16px;

        }


        .back-login {

            text-align: center;

            margin-top: 25px;

        }


        .auth-error {

            background-color: #f8d7da;

            color: #721c24;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            border-left: 4px solid #dc3545;

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


            <div class="auth-header">

                <h1>
                    Forgot Password?
                </h1>

                <p>
                    Enter your registered phone number.
                </p>

            </div>


            <?php if ($message != ""): ?>

                <div class="auth-error">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="auth-form"
            >

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="Enter your 10-digit phone number"
                    inputmode="numeric"
                    maxlength="10"
                    pattern="[0-9]{10}"
                    title="Please enter a valid 10-digit phone number"
                    required
                >


                <button
                    type="submit"
                    class="reset-button"
                >
                    Continue
                </button>

            </form>


            <div class="back-login">

                <a href="login.php">
                    ← Back to Login
                </a>

            </div>

        </div>

    </div>


    <script>

        const phoneInput =
            document.getElementById("phone");


        phoneInput.addEventListener(
            "input",
            function () {

                this.value =
                    this.value.replace(
                        /[^0-9]/g,
                        ""
                    );

            }
        );

    </script>

</body>

</html>