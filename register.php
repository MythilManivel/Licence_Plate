<?php
// Database connection
$servername = "localhost";
$username = "root";  // your MySQL username (default for XAMPP is 'root')
$password = "";      // your MySQL password (default for XAMPP is an empty string)
$dbname = "vehicle_database";  // your actual database name
$table_name = "vehicle_owners";  // your actual table name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $license_plate = $_POST['license_plate'];
    $owner_name = $_POST['owner_name'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO $table_name (license_plate, owner_name, phone_number, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $license_plate, $owner_name, $phone_number, $address);

    if ($stmt->execute()) {
        // Set session flag for success
        session_start();
        $_SESSION['registration_success'] = true;

        // Redirect to success page
        header("Location: success.php");
        exit();
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
