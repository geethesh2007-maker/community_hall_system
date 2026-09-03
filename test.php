<?php

include "config/database.php";

$result = $db->test_collection->insertOne([
    "message" => "Community Hall System database is working"
]);

echo "MongoDB connected and test data inserted successfully!";

?>