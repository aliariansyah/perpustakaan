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

// Check if the form is submitted and the return_id is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["return"]) && isset($_POST["request_id"])) {
    $request_id = $_POST["request_id"];

    // Update the status in the borrow_requests table to 'returned'
    $update_return_query = "UPDATE borrow_requests SET confirmed = 2 WHERE request_id = ?";

    if ($stmt = $conn->prepare($update_return_query)) {
        $stmt->bind_param("i", $request_id);

        if ($stmt->execute()) {
            // Increase the quantity in the books table
            $update_quantity_query = "UPDATE books SET quantity = quantity + 1 WHERE book_id = 
                (SELECT book_id FROM borrow_requests WHERE request_id = ?)";

            if ($stmt1 = $conn->prepare($update_quantity_query)) {
                $stmt1->bind_param("i", $request_id);

                if ($stmt1->execute()) {
                    // Update the status in the book_borrow table to 'returned'
                    $update_status_query = "UPDATE book_borrow SET stat = 'returned' WHERE book_id = 
                        (SELECT book_id FROM borrow_requests WHERE request_id = ?)";

                    if ($stmt2 = $conn->prepare($update_status_query)) {
                        $stmt2->bind_param("i", $request_id);

                        if ($stmt2->execute()) {
// Calculate and update fine if returning date has exceeded the limit
$calculate_fine_query = "SELECT returning_date FROM borrow_requests WHERE request_id = ?";
$stmt3 = $conn->prepare($calculate_fine_query);

if ($stmt3) {
    $stmt3->bind_param("i", $request_id);
    $stmt3->execute();
    $stmt3->bind_result($returning_date);
    $stmt3->fetch();
    $stmt3->close();

    $current_date = date("Y-m-d");
    if ($current_date > $returning_date) {
        // Calculate fine amount (1000 for each day exceeded)
        $fine_amount = (strtotime($current_date) - strtotime($returning_date)) / (60 * 60 * 24) * 1000;

        // Insert fine into the book_borrow table
        $insert_fine_query = "UPDATE book_borrow SET fine = ? WHERE request_id = ?";
        $stmt4 = $conn->prepare($insert_fine_query);

        if ($stmt4) {
            $stmt4->bind_param("ii", $fine_amount, $request_id);

            if ($stmt4->execute()) {
                echo "<script>alert('Book returned successfully with fine: $fine_amount');</script>";
            } else {
                echo "Error updating fine: " . $stmt4->error;
            }

            $stmt4->close();
        } else {
            echo "Error preparing fine update statement: " . $conn->error;
        }
    } else {
        echo "<script>alert('Book returned successfully');</script>";
    }

    echo '<script>
            setTimeout(function() {
                window.location.href = "officer_dashboard.php";
            }, 1000);
          </script>';
    exit();
} else {
    echo "Error preparing fine query: " . $conn->error;
}
                        }

                    } else {
                        echo "Error preparing status update statement: " . $conn->error;
                    }
                } else {
                    echo "Error updating quantity: " . $stmt1->error;
                }

                $stmt1->close();
            } else {
                echo "Error preparing quantity update statement: " . $conn->error;
            }
        } else {
            echo "Error updating return: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing return update statement: " . $conn->error;
    }
} else {
    // Redirect to the officer dashboard if the form is not submitted properly
    header("Location: officer_dashboard.php");
    exit();
}
?>
