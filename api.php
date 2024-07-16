<?php
// api.php

header('Content-Type: application/json');

$apiKey = "d1845658f92b31c64bd94f06f7188c9c"; // OpenWeather API Key

if (isset($_GET['lat']) && isset($_GET['lon'])) {
    $lat = $_GET['lat'];
    $lon = $_GET['lon'];
    $url = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=$apiKey&units=metric";
} elseif (isset($_GET['city'])) {
    $city = $_GET['city'];
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";
} else {
    echo json_encode(['error' => 'Invalid parameters']);
    exit();
}

$weatherData = file_get_contents($url);
if ($weatherData === FALSE) {
    echo json_encode(['error' => 'Failed to retrieve weather data']);
    exit();
}

echo $weatherData;
?>
