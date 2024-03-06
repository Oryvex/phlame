<?php
header('Content-Type: application/json');

$array = array();

$current_date = date("Y-m-d H:i:s");

$message = "Page not found";
$status = 404; 

$array['message'] = $message;
$array['status'] = $status;
$array['timestamp'] = $current_date;
$array['endpoint'] = $request_uri = $_SERVER['REQUEST_URI'];

$json_output = json_encode($array);

http_response_code($status); 

echo $json_output;
?>
