<?php
session_start();

if (!isset($_SESSION['registration_success'])) {
    // If no success flag is set, redirect back to the registration page
    header("Location: register.php");
    exit();
}

unset($_SESSION['registration_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #00aaff;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }

        h1 {
            margin: 0;
            font-size: 2.5rem;
            letter-spacing: 1px;
        }

        .success-container {
            text-align: center;
            padding: 40px 20px;
        }

        .success-container h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 20px;
        }

        .success-container p {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            margin: 10px;
            padding: 12px 25px;
            font-size: 1rem;
            color: #fff;
            background-color: #00aaff;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0077cc;
        }

        footer {
            background-color: #222;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Registration Successful</h1>
    </header>

    <section class="success-container">
        <h2>Thank you for registering your vehicle!</h2>
        <p>Your vehicle registration has been successfully completed.</p>
        <p>If you have any questions, please feel free to contact us.</p>

        <a href="register.php" class="btn">Register Another Vehicle</a>
        <a href="lookup.php" class="btn">Lookup Vehicle</a>
    </section>

    <footer>
        <p>&copy; 2025 Vehicle Registration. All rights reserved.</p>
    </footer>
</body>
</html>



