<?php

include "config/database.php";

$phone = "9380235043";

$password = password_hash(
    "admin@123",
    PASSWORD_DEFAULT
);

/*
|--------------------------------------------------------------------------
| CREATE OR UPDATE ADMIN
|--------------------------------------------------------------------------
|
| This ensures that the account with this phone number
| always has the admin role.
|
*/

$result = $db->users->updateOne(

    [
        "phone" => $phone
    ],

    [
        '$set' => [

            "name" => "Admin",

            "phone" => $phone,

            "email" => "",

            "password" => $password,

            "role" => "admin",

            "created_at" =>
                new MongoDB\BSON\UTCDateTime()

        ]
    ],

    [
        "upsert" => true
    ]

);

echo "Admin account created/updated successfully!";

?>