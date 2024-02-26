<?php
// Include the necessary database connection code here
$servername = "localhost";
$username = "root";
$password = "";
$database = "perpustakaan_db";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session to access session variables
session_start();

// Check if the user is logged in and has a user role
$user_logged_in = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';

// Check if the form is submitted for borrowing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["borrow_submit"]) && $user_logged_in) {
    // Get user data from the form
    $user_id = $_SESSION['user_id'];
    $book_id = $_POST['book_id'];
    $nis = $_POST['nis'];
    $full_name = $_POST['full_name'];
    $gender = $_POST['gender'];
    $place_of_birth = $_POST['place_of_birth'];
    $date_of_birth = $_POST['date_of_birth'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $email_address = $_POST['email_address'];
    $phone_number = $_POST['phone_number'];
    $request_date = $_POST['request_date'];
    $returning_date = $_POST['returning_date'];

    // Validate data (you may want to add more validation)
    if (empty($user_id) || empty($nis) || empty($full_name) || empty($gender) || empty($place_of_birth) || empty($date_of_birth) || empty($address) || empty($password) || empty($email_address) || empty($phone_number) || empty($request_date) || empty($returning_date)) {
        echo "All fields are required.";
        exit();
    }

    // Insert data into borrow_requests table with user information
    $insert_query = "INSERT INTO borrow_requests (user_id, book_id, nis, full_name, gender, place_of_birth, date_of_birth, address, password, email, phone_number, request_date, returning_date, confirmed) VALUES ('$user_id', '$book_id', '$nis', '$full_name', '$gender', '$place_of_birth', '$date_of_birth', '$address', '$password', '$email_address', '$phone_number', '$request_date', '$returning_date', 0)";

    if ($conn->query($insert_query) === TRUE) {
        echo '<script>alert("Book borrowed successfully.");</script>';
        echo '<script>
            setTimeout(function() {
                window.location.href = "user_dashboard.php";
            }, 1000);
          </script>';
        exit();
    } else {
        echo "Error inserting borrower information: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
