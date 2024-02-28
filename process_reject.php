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

// Check if the form is submitted and the reject button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reject"]) && isset($_POST["request_id"]) && isset($_POST["reasons"])) {
    $request_id = $_POST["request_id"];
    $reasons = $_POST["reasons"];

    // Retrieve the request details from the borrow_requests table
    $select_request_query = "SELECT * FROM borrow_requests WHERE request_id = ?";
    $stmt = $conn->prepare($select_request_query);

    if ($stmt) {
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request_details = $result->fetch_assoc();
        $stmt->close();

        if ($request_details) {
            // Insert the request into the reject_requests table with reasons
            $insert_reject_query = "INSERT INTO reject_requests (request_id, user_id, book_id, nis, email, phone_number, request_date, returning_date, reasons) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_reject_query);

            if ($stmt) {
                $stmt->bind_param("iiissssss", $request_details['request_id'], $request_details['user_id'], 
                                  $request_details['book_id'], $request_details['nis'], $request_details['email'],
                                  $request_details['phone_number'], $request_details['request_date'],
                                  $request_details['returning_date'], $reasons);
                
                if ($stmt->execute()) {
                    // Successfully inserted into reject_requests, now delete from borrow_requests
                    $delete_request_query = "DELETE FROM borrow_requests WHERE request_id = ?";
                    $stmt = $conn->prepare($delete_request_query);

                    if ($stmt) {
                        $stmt->bind_param("i", $request_id);
                        $stmt->execute();
                        echo "<script>alert('Request rejected with reasons and stored in reject_requests table');</script>";
                    } else {
                        echo "Error preparing delete statement: " . $conn->error;
                    }
                } else {
                    echo "Error inserting into reject_requests: " . $stmt->error;
                }

                $stmt->close();
            } else {
                echo "Error preparing insert statement: " . $conn->error;
            }
        } else {
            echo "Error retrieving request details.";
        }
    } else {
        echo "Error preparing select statement: " . $conn->error;
    }

    // Redirect to the officer dashboard
    echo '<script>
            setTimeout(function() {
                window.location.href = "officer_dashboard.php";
            }, 1000);
          </script>';
    exit();
} else {
    // Redirect to the officer dashboard if the form is not submitted properly
    header("Location: officer_dashboard.php");
    exit();
}
?>
