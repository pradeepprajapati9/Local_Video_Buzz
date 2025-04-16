<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include 'connection.php'; // Include your existing connection file

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['video_name'], $data['video_link'])) {
    $video_id = "VID_" . uniqid(); // Generate a random video_id with VID_
    $video_name = $con->real_escape_string($data['video_name']);
    $video_link = $con->real_escape_string($data['video_link']);
    $created_at = date("Y-m-d H:i:s");

    // Determine which field to insert and set others to NULL
    if (!empty($data['state_id']) && !empty($data['state_name'])) {
        $state_id = "'" . $con->real_escape_string($data['state_id']) . "'";
        $state_name = "'" . $con->real_escape_string($data['state_name']) . "'";
        $district_id = "NULL";
        $district_name = "NULL";
        $schedule_time = "NULL";
    } elseif (!empty($data['district_id']) && !empty($data['district_name'])) {
        $state_id = "NULL";
        $state_name = "NULL";
        $district_id = "'" . $con->real_escape_string($data['district_id']) . "'";
        $district_name = "'" . $con->real_escape_string($data['district_name']) . "'";
        $schedule_time = "NULL";
    } elseif (!empty($data['schedule_time'])) {
        $state_id = "NULL";
        $state_name = "NULL";
        $district_id = "NULL";
        $district_name = "NULL";
        $schedule_time = "'" . $con->real_escape_string($data['schedule_time']) . "'";
    } else {
        echo json_encode(["status" => "error", "message" => "At least one of state, district, or schedule time must be provided"]);
        exit;
    }

    $sql = "INSERT INTO videos_promote (video_id, video_name, video_link, state_id, state_name, district_id, district_name, schedule_time, created_at) 
            VALUES ('$video_id', '$video_name', '$video_link', $state_id, $state_name, $district_id, $district_name, $schedule_time, '$created_at')";

    if ($con->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Record inserted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $con->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing required fields: video_name, video_link"]);
}

$con->close();
?>
