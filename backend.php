<?php

require "config.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $query = $_GET["query"];
        if ($query == 'last10') {
            $result = $con->query("SELECT * FROM Readings ORDER BY timestamp DESC LIMIT 10");
            $data = array();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            http_response_code(200);
            echo json_encode($data);
        } else if ($query == 'preferences') {
            // Get sleep preferences
            $result = $con->query("SELECT sleepType FROM Preferences");
            $data = "";
            while ($row = $result->fetch_assoc()) {
                $data = $row['sleepType'];
            }
            http_response_code(200);
            echo ($data);
        }

        break;

    case 'POST':
        $query = $_GET['query'];

        $data = json_decode(file_get_contents('php://input'), true);

        if ($query == 'sensor') {
            $humidity = $data['humidity'];
            $temperature = $data['temperature'];

            $stmt = $con->prepare("INSERT INTO Readings (humidity, temperature) VALUES (?, ?)");
            $stmt->bind_param('ss', $humidity, $temperature);
            if ($stmt->execute()) {
                echo json_encode(['success_message' => 'Data added successfully']);
            } else {
                echo json_encode(['error_message' => 'Problem in Adding New Record']);
            }
        } elseif ($query == 'preferences') {
            $sleep_mode = $data['sleepType'];
            $stmt = $con->prepare("UPDATE Preferences SET sleepType=?");
            $stmt->bind_param('d', $sleep_mode);
            if ($stmt->execute()) {
                echo json_encode(['success_message' => 'Data updated successfully']);
            } else {
                echo json_encode(['error_message' => 'Problem in Adding New Record']);
            }
        } else if ($query == 'spiffs') {
            require "config.php"; // Assuming your database connection is defined in config.php

            // Prepare SQL statement
            $sql = "INSERT INTO Readings (temperature, humidity) VALUES ";

            $values = array();
            foreach ($data as $reading) {
                $temperatureVal = $reading['temperature'];
                $humidityVal = $reading['humidity'];

                // Add values to the values array
                $values[] = "('" . $temperatureVal . "', '" . $humidityVal . "')";
            }

            // Combine to a single string
            $sql .= implode(", ", $values);

            // Execute 
            if ($con->query($sql) === TRUE) {
                echo json_encode(array("message" => "Batch data posted"));
            } else {
                echo json_encode(array("error" => "Error inserting data into database."));
            }
        }

        break;
    case 'PUT':
        $power_mode = $data['sleepType'];
           
            $stmt = $con->prepare("UPDATE Preferences SET sleepType=?");
            $stmt->bind_param('d', $power_mode);
            if ($stmt->execute()) {
                echo json_encode(['success_message' => 'Data updated successfully']);
            } else {
                echo json_encode(['error_message' => 'Problem in Adding New Record']);
            }
    break;
    default:
        // Invalid method
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
