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

// Check if the form is submitted and the request_id is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm"]) && isset($_POST["request_id"])) {
    $request_id = $_POST["request_id"];

    // Retrieve data from borrow_requests
    $select_query = "SELECT * FROM borrow_requests WHERE request_id = '$request_id'";
    $result = $conn->query($select_query);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Insert data into book_borrow
        $insert_query = "INSERT INTO book_borrow (request_id, book_id, user_id, request_date, returning_date, stat)
                         VALUES ( '{$row['request_id']}','{$row['book_id']}', '{$row['user_id']}', '{$row['request_date']}', '{$row['returning_date']}', 'borrowed')";

        if ($conn->query($insert_query) === TRUE) {
            // Update the confirmed status in the borrow_requests table
            $update_query = "UPDATE borrow_requests SET confirmed = 1 WHERE request_id = '$request_id'";

            if ($conn->query($update_query) === TRUE) {
                // Decrease the quantity in the books table
                $book_id = $row['book_id'];
                $update_quantity_query = "UPDATE books SET quantity = quantity - 1 WHERE book_id = '$book_id'";

                if ($conn->query($update_quantity_query) === FALSE) {
                    echo "Error updating quantity: " . $conn->error;
                    exit();
                }

                echo "<script>alert('Confirmation successful');</script>";

                echo '<script>
                        setTimeout(function() {
                            window.location.href = "officer_dashboard.php";
                        }, 1000);
                      </script>';
                exit();
            } else {
                echo "Error updating confirmation: " . $conn->error;
            }
        } else {
            echo "Error inserting into book_borrow: " . $conn->error;
        }
    } else {
        echo "Error retrieving data from borrow_requests";
    }
} else {
    // Redirect to the officer dashboard if the form is not submitted properly
    header("Location: officer_dashboard.php");
    exit();
}
?>
