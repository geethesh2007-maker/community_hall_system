<?php

include "../config/database.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstName = trim($_POST["first_name"]);
    $middleName = trim($_POST["middle_name"]);
    $lastName = trim($_POST["last_name"]);

    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];



    // =========================================
    // NAME VALIDATION
    // =========================================

    if (
        !preg_match("/^[A-Za-z]+$/", $firstName) ||
        (
            $middleName !== "" &&
            !preg_match("/^[A-Za-z]+$/", $middleName)
        ) ||
        !preg_match("/^[A-Za-z]+$/", $lastName)
    ) {

        $message =
            "First name, middle name, and last name can contain alphabets only.";

        $messageType = "error";



    // =========================================
    // PHONE VALIDATION
    // =========================================

    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $message =
            "Please enter a valid 10-digit phone number.";

        $messageType = "error";



    // =========================================
    // PASSWORD VALIDATION
    // =========================================

    } elseif (
        strlen($password) < 8 ||
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[0-9]/", $password) ||
        !preg_match("/[^A-Za-z0-9]/", $password)
    ) {

        $message =
            "Password must contain at least 8 characters, including uppercase, lowercase, number, and special character.";

        $messageType = "error";



    } else {

        // =========================================
        // CREATE FULL NAME
        // =========================================

        $name = trim(
            $firstName . " " .
            $middleName . " " .
            $lastName
        );



        // =========================================
        // CHECK PHONE ALREADY EXISTS
        // =========================================

        $existingPhone = $db->users->findOne([

            "phone" => $phone

        ]);



        if ($existingPhone) {

            $message =
                "This phone number is already registered.";

            $messageType = "error";



        // =========================================
        // CHECK EMAIL ONLY IF PROVIDED
        // =========================================

        } elseif (

            $email !== "" &&

            $db->users->findOne([

                "email" => $email

            ])

        ) {

            $message =
                "Email already registered!";

            $messageType = "error";



        } else {

            // =========================================
            // ASSIGN ROLE
            // =========================================

            if ($phone === "9380235043") {

                $role = "admin";

            } else {

                $role = "citizen";

            }



            // =========================================
            // HASH PASSWORD
            // =========================================

            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );



            // =========================================
            // INSERT USER INTO MONGODB
            // =========================================

            $db->users->insertOne([

                "name" => $name,

                "email" => $email,

                "phone" => $phone,

                "password" => $hashedPassword,

                "role" => $role,

                "created_at" =>
                    new MongoDB\BSON\UTCDateTime()

            ]);



            if ($role === "admin") {

                $message =
                    "Admin registration successful! You can now login.";

            } else {

                $message =
                    "Registration successful! You can now login using your phone number.";

            }

            $messageType = "success";

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
        Register - Harmony Halls
    </title>



    <!-- Connect Main CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >



    <style>

        /* =================================
           REGISTRATION PAGE
        ================================= */

        .auth-page {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 40px 15px;

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



        /* =================================
           MAIN REGISTRATION LAYOUT
        ================================= */

        .register-layout {

            width: 100%;

            max-width: 1050px;

            display: grid;

            grid-template-columns: minmax(0, 1fr) 320px;

            gap: 25px;

            align-items: start;

        }



        /* =================================
           REGISTRATION CARD
        ================================= */

        .auth-card {

            width: 100%;

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

            margin-bottom: 0;

        }



        .auth-form {

            max-width: 100%;

            padding: 0;

            box-shadow: none;

            background: transparent;

        }



        .auth-form input {

            margin-bottom: 18px;

        }



        /* =================================
           REGISTER BUTTON
        ================================= */

        .register-button {

            width: 100%;

            padding: 13px;

            font-size: 16px;

        }



        .login-link {

            text-align: center;

            margin-top: 25px;

            color: #6c757d;

        }



        .login-link a {

            font-weight: bold;

        }



        /* =================================
           MESSAGES
        ================================= */

        .auth-error {

            background-color: #f8d7da;

            color: #721c24;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            border-left: 4px solid #dc3545;

        }



        .auth-success {

            background-color: #d4edda;

            color: #155724;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            border-left: 4px solid #198754;

        }



        /* =================================
           PASSWORD HELP SIDEBAR
        ================================= */

        .password-sidebar {

            background-color: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.25);

            position: sticky;

            top: 20px;

        }



        .password-sidebar h2 {

            color: #1f3c88;

            margin-top: 0;

            margin-bottom: 15px;

            font-size: 22px;

        }



        .password-sidebar p {

            color: #555;

            line-height: 1.6;

        }



        .password-rules {

            list-style: none;

            padding: 0;

            margin: 20px 0;

        }



        .password-rules li {

            margin-bottom: 12px;

            padding: 10px;

            background-color: #f8f9fc;

            border-radius: 6px;

            color: #555;

            font-size: 14px;

        }



        .good-example {

            background-color: #eaf7ee;

            padding: 15px;

            border-radius: 8px;

            margin-top: 20px;

            border-left: 4px solid #198754;

        }



        .bad-example {

            background-color: #fff0f0;

            padding: 15px;

            border-radius: 8px;

            margin-top: 15px;

            border-left: 4px solid #dc3545;

        }



        .password-example {

            display: block;

            font-weight: bold;

            margin-top: 8px;

            word-break: break-word;

        }



        /* =================================
           LIVE PASSWORD CHECKLIST
        ================================= */

        .password-checklist {

            margin-top: -8px;

            margin-bottom: 18px;

            padding: 15px;

            background-color: #f8f9fc;

            border-radius: 8px;

        }



        .password-checklist p {

            margin-top: 0;

            margin-bottom: 10px;

            font-size: 14px;

            font-weight: bold;

            color: #444;

        }



        .password-check {

            margin-bottom: 7px;

            font-size: 13px;

            color: #777;

        }



        .password-check.valid {

            color: #198754;

            font-weight: bold;

        }



        /* =================================
           MOBILE AND TABLET
        ================================= */

        @media (max-width: 850px) {

            .register-layout {

                grid-template-columns: 1fr;

                max-width: 550px;

            }



            .password-sidebar {

                position: static;

            }

        }



        @media (max-width: 600px) {

            .auth-page {

                padding: 25px 15px;

                background-attachment: scroll;

            }



            .auth-card {

                padding: 30px 25px;

            }



            .password-sidebar {

                padding: 25px;

            }

        }

    </style>

</head>



<body>

    <div class="auth-page">



        <!-- =================================
             MAIN LAYOUT
        ================================= -->

        <div class="register-layout">



            <!-- =================================
                 REGISTRATION FORM
            ================================= -->

            <div class="auth-card">



                <!-- HEADER -->

                <div class="auth-header">

                    <h1>
                        Harmony Halls
                    </h1>

                    <p>
                        Create your account to book the perfect hall for your event
                    </p>

                </div>



                <!-- MESSAGE -->

                <?php if ($message != ""): ?>

                    <div
                        class="<?php
                        echo $messageType === 'success'
                            ? 'auth-success'
                            : 'auth-error';
                        ?>"
                    >

                        <?php
                        echo htmlspecialchars($message);
                        ?>

                    </div>

                <?php endif; ?>



                <!-- REGISTRATION FORM -->

                <form
                    method="POST"
                    class="auth-form"
                >



                    <!-- FIRST NAME -->

                    <label for="first_name">
                        First Name
                    </label>

                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        placeholder="Enter your first name"
                        pattern="[A-Za-z]+"
                        title="Only alphabets are allowed"
                        required
                        oninput="this.value = this.value.replace(/[^A-Za-z]/g, '')"
                    >



                    <!-- MIDDLE NAME -->

                    <label for="middle_name">
                        Middle Name (Optional)
                    </label>

                    <input
                        type="text"
                        id="middle_name"
                        name="middle_name"
                        placeholder="Enter your middle name"
                        pattern="[A-Za-z]*"
                        title="Only alphabets are allowed"
                        oninput="this.value = this.value.replace(/[^A-Za-z]/g, '')"
                    >



                    <!-- LAST NAME -->

                    <label for="last_name">
                        Last Name
                    </label>

                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        placeholder="Enter your last name"
                        pattern="[A-Za-z]+"
                        title="Only alphabets are allowed"
                        required
                        oninput="this.value = this.value.replace(/[^A-Za-z]/g, '')"
                    >



                    <!-- EMAIL -->

                    <label for="email">
                        Email Address (Optional)
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email (optional)"
                    >



                    <!-- PHONE NUMBER -->

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



                    <!-- PASSWORD -->

                    <label for="password">
                        Create Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a strong password"
                        required
                    >



                    <!-- LIVE PASSWORD CHECKLIST -->

                    <div class="password-checklist">

                        <p>
                            Password Requirements:
                        </p>

                        <div
                            class="password-check"
                            id="lengthCheck"
                        >
                            ❌ At least 8 characters
                        </div>

                        <div
                            class="password-check"
                            id="uppercaseCheck"
                        >
                            ❌ At least one uppercase letter
                        </div>

                        <div
                            class="password-check"
                            id="lowercaseCheck"
                        >
                            ❌ At least one lowercase letter
                        </div>

                        <div
                            class="password-check"
                            id="numberCheck"
                        >
                            ❌ At least one number
                        </div>

                        <div
                            class="password-check"
                            id="specialCheck"
                        >
                            ❌ At least one special character
                        </div>

                    </div>



                    <!-- REGISTER BUTTON -->

                    <button
                        type="submit"
                        class="register-button"
                    >
                        Create Account
                    </button>

                </form>



                <!-- LOGIN LINK -->

                <div class="login-link">

                    Already have an account?

                    <a href="login.php">

                        Login here

                    </a>

                </div>

                <!-- BACK TO HOME BUTTON -->

                <div
                    style="
                        text-align: center;
                        margin-top: 15px;
                    "
                >

                    <a
                        href="../index.php"
                        style="
                            display: inline-block;
                            padding: 10px 18px;
                            background-color: #1f3c88;
                            color: white;
                            text-decoration: none;
                            border-radius: 6px;
                            font-weight: bold;
                        "
                    >
                        ← Back to Home
                    </a>

                </div>

            </div>



            <!-- =================================
                 PASSWORD HELP SIDEBAR
            ================================= -->

            <div class="password-sidebar">

                <h2>
                    🔐 Password Help
                </h2>

                <p>
                    Please create a strong password to keep
                    your account safe.
                </p>



                <ul class="password-rules">

                    <li>
                        🔢 Use at least 8 characters
                    </li>

                    <li>
                        🔠 Add one capital letter
                        <br>
                        Example: <strong>A, B, C</strong>
                    </li>

                    <li>
                        🔡 Add one small letter
                        <br>
                        Example: <strong>a, b, c</strong>
                    </li>

                    <li>
                        🔢 Add at least one number
                        <br>
                        Example: <strong>1, 2, 3</strong>
                    </li>

                    <li>
                        ⭐ Add a special symbol
                        <br>
                        Example: <strong>@ # ! $</strong>
                    </li>

                </ul>



                <div class="good-example">

                    <strong>
                        ✅ Good Password Example
                    </strong>

                    <span class="password-example">
                        Ravi@2026
                    </span>

                </div>



                <div class="bad-example">

                    <strong>
                        ❌ Do Not Use
                    </strong>

                    <span class="password-example">
                        1234
                    </span>

                    <span class="password-example">
                        password
                    </span>

                    <span class="password-example">
                        987654321
                    </span>

                </div>



                <p style="margin-top: 20px;">

                    💡 <strong>Tip:</strong>
                    Combine letters, numbers, and symbols
                    to make your password stronger.

                </p>

            </div>



        </div>

    </div>



    <!-- =================================
         PHONE AND PASSWORD VALIDATION
    ================================= -->

    <script>

        /* =================================
           PHONE NUMBER
        ================================= */

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



        /* =================================
           PASSWORD STRENGTH CHECK
        ================================= */

        const passwordInput =
            document.getElementById("password");



        const lengthCheck =
            document.getElementById("lengthCheck");

        const uppercaseCheck =
            document.getElementById("uppercaseCheck");

        const lowercaseCheck =
            document.getElementById("lowercaseCheck");

        const numberCheck =
            document.getElementById("numberCheck");

        const specialCheck =
            document.getElementById("specialCheck");



        passwordInput.addEventListener(
            "input",
            function () {

                const password =
                    passwordInput.value;



                // Check Length

                if (password.length >= 8) {

                    lengthCheck.innerHTML =
                        "✅ At least 8 characters";

                    lengthCheck.classList.add(
                        "valid"
                    );

                } else {

                    lengthCheck.innerHTML =
                        "❌ At least 8 characters";

                    lengthCheck.classList.remove(
                        "valid"
                    );

                }



                // Check Uppercase

                if (/[A-Z]/.test(password)) {

                    uppercaseCheck.innerHTML =
                        "✅ At least one uppercase letter";

                    uppercaseCheck.classList.add(
                        "valid"
                    );

                } else {

                    uppercaseCheck.innerHTML =
                        "❌ At least one uppercase letter";

                    uppercaseCheck.classList.remove(
                        "valid"
                    );

                }



                // Check Lowercase

                if (/[a-z]/.test(password)) {

                    lowercaseCheck.innerHTML =
                        "✅ At least one lowercase letter";

                    lowercaseCheck.classList.add(
                        "valid"
                    );

                } else {

                    lowercaseCheck.innerHTML =
                        "❌ At least one lowercase letter";

                    lowercaseCheck.classList.remove(
                        "valid"
                    );

                }



                // Check Number

                if (/[0-9]/.test(password)) {

                    numberCheck.innerHTML =
                        "✅ At least one number";

                    numberCheck.classList.add(
                        "valid"
                    );

                } else {

                    numberCheck.innerHTML =
                        "❌ At least one number";

                    numberCheck.classList.remove(
                        "valid"
                    );

                }



                // Check Special Character

                if (/[^A-Za-z0-9]/.test(password)) {

                    specialCheck.innerHTML =
                        "✅ At least one special character";

                    specialCheck.classList.add(
                        "valid"
                    );

                } else {

                    specialCheck.innerHTML =
                        "❌ At least one special character";

                    specialCheck.classList.remove(
                        "valid"
                    );

                }

            }
        );

    </script>

</body>

</html>