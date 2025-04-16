<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include('connection.php');

$state = isset($_GET['state']) ? mysqli_real_escape_string($con, $_GET['state']) : '';
$district = isset($_GET['district']) ? mysqli_real_escape_string($con, $_GET['district']) : '';

$current_time = date('Y-m-d H:i:s'); // Get current timestamp

// Query to fetch scheduled videos (future schedule_time)
$sql_scheduled = "SELECT * FROM videos_promote WHERE schedule_time IS NOT NULL AND schedule_time > '$current_time'";
$result_scheduled = mysqli_query($con, $sql_scheduled);

$scheduled_videos = [];
if (mysqli_num_rows($result_scheduled) > 0) {
    while ($row = mysqli_fetch_assoc($result_scheduled)) {
        $scheduled_videos[] = $row;
    }
}

// Query to fetch normal videos (excluding future scheduled videos)
$sql_normal = "SELECT * FROM videos_promote WHERE (schedule_time IS NULL OR schedule_time <= '$current_time')";

$conditions = [];

if (!empty($state)) {
    $conditions[] = "state_name = '$state'";
}
if (!empty($district)) {
    $conditions[] = "district_name = '$district'";
}

if (!empty($conditions)) {
    $sql_normal .= " AND (" . implode(" OR ", $conditions) . ")";
}

$result_normal = mysqli_query($con, $sql_normal);

$normal_videos = [];
if (mysqli_num_rows($result_normal) > 0) {
    while ($row = mysqli_fetch_assoc($result_normal)) {
        $normal_videos[] = $row;
    }
}

// Return structured JSON response
$response = [
    'scheduled_videos' => $scheduled_videos,
    'normal_videos'    => $normal_videos
];

echo json_encode($response);
?>
