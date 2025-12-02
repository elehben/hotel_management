<?php
// File untuk testing API connection
include "client.php";

echo "<h2>Test API Connection</h2>";

// Test data
$test_data = array(
    "aksi" => "tambah_tamu",
    "jwt" => isset($_COOKIE['jwt']) ? $_COOKIE['jwt'] : "TOKEN_TIDAK_ADA",
    "guest_id" => "TEST001",
    "full_name" => "Test User",
    "email" => "test@test.com",
    "phone_number" => "08123456789"
);

echo "<h3>Data yang dikirim:</h3>";
echo "<pre>";
print_r($test_data);
echo "</pre>";

// Kirim ke API
$response = $abc->crud_general($test_data);

echo "<h3>Response dari API:</h3>";
echo "<pre>";
var_dump($response);
echo "</pre>";

// Cek error CURL
echo "<h3>Test CURL langsung:</h3>";
$url = 'http://10.90.34.117/hotel_management/server/server.php';
$payload = json_encode($test_data);

$c = curl_init();
curl_setopt($c, CURLOPT_URL, $url);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_POST, true);
curl_setopt($c, CURLOPT_POSTFIELDS, $payload);
curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response_raw = curl_exec($c);
$error = curl_error($c);
$info = curl_getinfo($c);
curl_close($c);

echo "CURL Error: " . ($error ? $error : "Tidak ada error") . "<br>";
echo "HTTP Code: " . $info['http_code'] . "<br>";
echo "Response Raw: <pre>" . htmlspecialchars($response_raw) . "</pre>";
?>
