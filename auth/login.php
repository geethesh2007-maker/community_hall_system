<?php

include "../config/database.php";
include "../config/session.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get phone number and password
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];


    // =========================================
    // PHONE NUMBER VALIDATION
    // =========================================

    if (!preg_match("/^[0-9]+$/", $phone)) {

        $message = "Phone number must contain only numbers.";

    } else {

        // =========================================
        // FIND USER BY PHONE NUMBER
        // =========================================

        $user = $db->users->findOne([

            "phone" => $phone

        ]);


        if (!$user) {

            $message = "Phone number not registered!";

        } elseif (!password_verify($password, $user["password"])) {

            $message = "Incorrect password!";

        } else {

            // =========================================
            // STORE USER INFORMATION IN SESSION
            // =========================================

            $_SESSION["user_id"] = (string) $user["_id"];

            $_SESSION["name"] = $user["name"];

            $_SESSION["role"] = $user["role"];


            // =========================================
            // REDIRECT ACCORDING TO ROLE
            // =========================================

            if ($user["role"] === "admin") {

                header(
                    "Location: ../admin/dashboard.php"
                );

                exit();

            } else {

                header(
                    "Location: ../citizen/dashboard.php"
                );

                exit();

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
        Login - Harmony Halls
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        /* =================================
           LOGIN PAGE
        ================================= */

        .auth-page {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 40px 15px;

            /* Community Hall background image */

            background-image:

                linear-gradient(
                    rgba(15, 35, 75, 0.58),
                    rgba(15, 35, 75, 0.58)
                ),

                url("https://img.magnific.com/free-photo/restaurant-hall-with-round-square-tables-some-chairs-plants_140725-8030.jpg?semt=ais_hybrid&w=740&q=80");

            background-size: cover;

            background-position: center;

            background-repeat: no-repeat;

            background-attachment: fixed;

        }


        .auth-card {

            width: 100%;

            max-width: 450px;

            background-color: white;

            padding: 40px;

            border-radius: 12px;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.25);

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


        /* =================================
           PASSWORD WRAPPER
        ================================= */

        .password-wrapper {

            position: relative;

            width: 100%;

        }


        .password-wrapper input {

            padding-right: 75px;

            margin-bottom: 20px;

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

            margin: 0;

            font-size: 13px;

            font-weight: bold;

            cursor: pointer;

        }


        .toggle-password:hover {

            background: none;

            color: #162d66;

            transform: translateY(-50%);

        }


        .forgot-password {

            text-align: right;

            margin-top: -8px;

            margin-bottom: 20px;

        }


        .forgot-password a {

            font-size: 14px;

            font-weight: 600;

        }


        .login-button {

            width: 100%;

            padding: 13px;

            font-size: 16px;

        }


        .register-link {

            text-align: center;

            margin-top: 25px;

            color: #6c757d;

        }


        .register-link a {

            font-weight: bold;

        }


        .auth-error {

            background-color: #f8d7da;

            color: #721c24;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            border-left: 4px solid #dc3545;

        }


        /* =================================
           BACK TO HOME BUTTON
        ================================= */

        .back-home {

            text-align: center;

            margin-top: 15px;

        }


        .back-home a {

            display: inline-block;

            padding: 10px 18px;

            background-color: #1f3c88;

            color: white;

            text-decoration: none;

            border-radius: 6px;

            font-weight: bold;

            transition: 0.2s ease;

        }


        .back-home a:hover {

            background-color: #162d66;

            color: white;

            text-decoration: none;

        }


        /* =================================
           MOBILE
        ================================= */

        @media (max-width: 600px) {

            .auth-page {

                padding: 25px 15px;

                background-attachment: scroll;

            }

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
                    Harmony Halls
                </h1>

                <p>
                    Login to your account
                </p>

            </div>


            <!-- ERROR MESSAGE -->

            <?php if ($message != ""): ?>

                <div class="auth-error">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <!-- LOGIN FORM -->

            <form
                method="POST"
                class="auth-form"
            >


                <!-- PHONE NUMBER -->

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="Enter your phone number"
                    inputmode="numeric"
                    pattern="[0-9]+"
                    required
                >


                <!-- PASSWORD -->

                <label for="password">
                    Password
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword()"
                        id="togglePasswordButton"
                    >
                        Show
                    </button>

                </div>


                <!-- FORGOT PASSWORD -->

                <div class="forgot-password">

                    <a href="forgot-password.php">

                        Forgot Password?

                    </a>

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    class="login-button"
                >
                    Login
                </button>

            </form>


            <!-- REGISTER LINK -->

            <div class="register-link">

                Don't have an account?

                <a href="register.php">

                    Register here

                </a>

            </div>


            <!-- BACK TO HOME -->

            <div class="back-home">

                <a href="../index.php">

                    ← Back to Home

                </a>

            </div>


        </div>

    </div>


    <script>

        /* =================================
           SHOW / HIDE PASSWORD
        ================================= */

        function togglePassword() {

            const passwordInput =
                document.getElementById("password");

            const toggleButton =
                document.getElementById(
                    "togglePasswordButton"
                );


            if (passwordInput.type === "password") {

                passwordInput.type = "text";

                toggleButton.textContent = "Hide";

            } else {

                passwordInput.type = "password";

                toggleButton.textContent = "Show";

            }

        }


        /* =================================
           ALLOW ONLY NUMBERS IN PHONE FIELD
        ================================= */

        document
            .getElementById("phone")
            .addEventListener(
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