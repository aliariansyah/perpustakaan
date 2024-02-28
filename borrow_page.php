<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in and has a user role
$user_logged_in = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';

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

// Check if book_id is set in the URL
if (!isset($_POST['book_id'])) {
    // Redirect to manage_books.php if book_id is not provided
    header("Location: manage_books.php");
    exit();
}

$book_id = $_POST['book_id'];

// Retrieve book information from the database
$select_query = "SELECT * FROM books WHERE book_id = '$book_id'";
$result = $conn->query($select_query);

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
} else {
    // Redirect to manage_books.php if book_id is not found
    header("Location: manage_books.php");
    exit();
}

// Check if user is logged in
if ($user_logged_in) {
    // Retrieve user information from the users table
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT * FROM users WHERE id = '$user_id'";
    $user_result = $conn->query($user_query);

    if ($user_result->num_rows == 1) {
        $user_row = $user_result->fetch_assoc();

        // Set user information for pre-filling the form
        $nis = $user_row['nis'];
        $full_name = $user_row['full_name'];
        $gender = $user_row['gender'];
        $place_of_birth = $user_row['place_of_birth'];
        $date_of_birth = $user_row['date_of_birth'];
        $address = $user_row['address'];
        $password = $user_row['password'];
        $email_address = $user_row['email'];
        $phone_number = $user_row['telephone'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <title>Borrow Page</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .borrow-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            text-align: center;
            color: #333;
        }

        p {
            text-align: right;
            margin: 10px 0 0 0;
        }

        a {
            text-decoration: none;
            color: #3498db;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #3498db;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>
    <div class="borrow-container">
        <h2>Borrow Page:</h2>
        <p><a href="logout.php">Logout</a></p>
        <h3>Book Details:</h3>

        <!-- Display book information -->
        <p><strong>Title:</strong> <?php echo isset($row['title']) ? htmlspecialchars($row['title']) : ''; ?></p>
        <p><strong>Writer:</strong> <?php echo isset($row['writer']) ? htmlspecialchars($row['writer']) : ''; ?></p>

        <!-- Borrow Form -->
        <?php if ($user_logged_in) : ?>
            <form method="post" action="process_borrow.php">
        <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
        <input type="hidden" name="nis" value="<?php echo isset($nis) ? htmlspecialchars($nis) : ''; ?>">
        <input type="hidden" name="full_name" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>">
        <input type="hidden" name="gender" value="<?php echo isset($gender) ? htmlspecialchars($gender) : ''; ?>">
        <input type="hidden" name="place_of_birth" value="<?php echo isset($place_of_birth) ? htmlspecialchars($place_of_birth) : ''; ?>">
        <input type="hidden" name="date_of_birth" value="<?php echo isset($date_of_birth) ? htmlspecialchars($date_of_birth) : ''; ?>">
        <input type="hidden" name="address" value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">
        <input type="hidden" name="password" value="<?php echo isset($password) ? htmlspecialchars($password) : ''; ?>">
        <input type="hidden" name="email_address" value="<?php echo isset($email_address) ? htmlspecialchars($email_address) : ''; ?>">
        <input type="hidden" name="phone_number" value="<?php echo isset($phone_number) ? htmlspecialchars($phone_number) : ''; ?>">

      <label for="request_date">Request Date:</label>
<input type="date" id="request_date" name="request_date" required>

<script>
  // Get today's date in the format YYYY-MM-DD
  const today = new Date().toISOString().split('T')[0];

  // Set the input value to today's date
  document.getElementById('request_date').value = today;

  // Set the min attribute to today's date
  document.getElementById('request_date').setAttribute('min', today);
</script>

<label for="returning_date">Returning Date:</label>
<input type="date" id="returning_date" name="returning_date" required>

<script>
  const requestDateInput = document.getElementById('request_date');
  const returningDateInput = document.getElementById('returning_date');

  // Function to update the min and max attributes of returning_date based on selected request_date
  function updateReturningDateConstraints() {
    const requestDate = new Date(requestDateInput.value);
    const maxReturningDate = new Date(requestDate);
    maxReturningDate.setDate(requestDate.getDate() + 7); // Set max returning date to 7 days ahead
    const minReturningDate = requestDate.toISOString().split('T')[0];
    const maxReturningDateFormatted = maxReturningDate.toISOString().split('T')[0];
    
    returningDateInput.setAttribute('min', minReturningDate);
    returningDateInput.setAttribute('max', maxReturningDateFormatted);
  }

  // Attach the update function to the change event of the request date input
  requestDateInput.addEventListener('change', updateReturningDateConstraints);

  // Set the min and max attributes of returning_date based on today's date
  updateReturningDateConstraints();

  // Function to check if the returning date is within the allowed range
  function validateReturningDate() {
    const requestDate = new Date(requestDateInput.value);
    const returningDate = new Date(returningDateInput.value);

    if (returningDate <= requestDate || returningDate > maxReturningDate) {
      alert('Returning date must be at least 1 day ahead and maximum within 7 days from the request date.');
      returningDateInput.value = ''; // Clear the invalid date
    }
  }

  // Attach the validation function to the change event of the returning date input
  returningDateInput.addEventListener('change', validateReturningDate);
</script>


<button type="submit" name="borrow_submit">Borrow</button>
</form>
<?php else : ?>
  <p>Please log in as a user to borrow a book.</p>
<?php endif; ?>
</div>
</body>
</html>
