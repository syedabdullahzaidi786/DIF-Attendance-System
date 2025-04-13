<?php
// This is a simple test file to verify the ID card API is working
require_once 'config/database.php';

// Get the first student from the database
$stmt = $pdo->query("SELECT id FROM students LIMIT 1");
$student = $stmt->fetch();

if ($student) {
    $student_id = $student['id'];
    
    // Test the API
    $url = "get_id_card_details.php?id=" . $student_id;
    
    echo "<h1>Testing ID Card API</h1>";
    echo "<p>Testing URL: " . htmlspecialchars($url) . "</p>";
    
    // Make the request
    $response = file_get_contents($url);
    
    echo "<h2>Response:</h2>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Try to decode as JSON
    $json = json_decode($response, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<h2>JSON Decoded:</h2>";
        echo "<pre>";
        print_r($json);
        echo "</pre>";
    } else {
        echo "<h2>JSON Error:</h2>";
        echo "<p>" . json_last_error_msg() . "</p>";
    }
} else {
    echo "<h1>Error</h1>";
    echo "<p>No students found in the database.</p>";
}
?> 