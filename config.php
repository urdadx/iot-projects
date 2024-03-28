<?php

    // Database connection details
    $servername = "localhost";
    $username = "root";
    $password = ""; 
    $database = "lab_4_sleep";

    // Create connection
    $con = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }
   

?>
    