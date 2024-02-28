<?php
// Include the database connection code here
$servername = "localhost";
$username = "root";
$password = "";
$database = "perpustakaan_db";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted and the reject button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reject"])) {
    $request_id = $_POST["request_id"];

    // Delete the rejected borrow request
    $delete_query = "DELETE FROM borrow_requests WHERE request_id = '$request_id'";
    $result = $conn->query($delete_query);

    // Check for query execution errors
    if ($result === FALSE) {
        echo "Error deleting borrow request: " . $conn->error;
        exit();
    }

    // Display a pop-up message after successfully rejecting the request
    echo '<script>alert("Request rejected successfully!");</script>';

    // Redirect back to the officer dashboard
    echo '<script>window.location.href = "user_request.php";</script>';
    exit();
} else {
    // If the form is not submitted or reject button is not clicked, redirect to the officer dashboard
    header("Location: officer_dashboard.php");
    exit();
}
?>
