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

$vehicle_info = null;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['license_plate'])) {
    $license_plate = $_GET['license_plate'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM $table_name WHERE license_plate = ?");
    $stmt->bind_param("s", $license_plate);
    $stmt->execute();
    
    // Store the result
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Fetch the result into an associative array
        $vehicle_info = $result->fetch_assoc();
    } else {
        $vehicle_info = false;  // If no results found
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Lookup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Vehicle Lookup</h1>
        <p>Find vehicle details by entering the license plate.</p>
    </header>

    <section class="lookup-container">
        <form action="lookup.php" method="GET" class="lookup-form">
            <div class="input-container">
                <label for="license_plate">License Plate:</label>
                <input type="text" id="license_plate" name="license_plate" required placeholder="Enter vehicle's license plate">
            </div>

            <button type="submit" class="submit-btn">Search</button>
        </form>
    </section>

    <?php if ($vehicle_info === false): ?>
        <section class="lookup-results">
            <p>No vehicle found with this license plate.</p>
        </section>
    <?php elseif ($vehicle_info !== null): ?>
        <section class="lookup-results">
            <h2>Vehicle Details</h2>
            <p><strong>License Plate:</strong> <?php echo htmlspecialchars($vehicle_info['license_plate']); ?></p>
            <p><strong>Owner Name:</strong> <?php echo htmlspecialchars($vehicle_info['owner_name']); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($vehicle_info['phone_number']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($vehicle_info['address']); ?></p>
        </section>
    <?php endif; ?>

    <footer>
        <p>&copy; 2025 Vehicle Registration. All rights reserved.</p>
    </footer>
</body>
</html>
