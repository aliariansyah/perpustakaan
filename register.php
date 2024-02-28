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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate a random 4-digit ID
    $random_id = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Retrieve user input
    $nis = $_POST["nis"];
    $full_name = $_POST["full_name"];
    $gender = $_POST["gender"];
    $place_of_birth = $_POST["place_of_birth"];
    $date_of_birth = $_POST["date_of_birth"];
    $address = $_POST["address"];
    $telephone = $_POST["telephone"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Validate NIS format (10 random numbers)
    if (!preg_match('/^[0-9]{10}$/', $nis)) {
        $error_message = "Invalid NIS format";
    } else {
        // Validate other fields as needed

        // Check if the data already exists in the database (except for password and email)
        $check_query = "SELECT * FROM users WHERE nis = '$nis' OR full_name = '$full_name' OR email = '$email'";
        $result = $conn->query($check_query);

        if ($result->num_rows > 0) {
            $error_message = "Data already in use. Please check your information.";
        } else {
            // Validate password
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);

            if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 12) {
                $error_message = "Invalid password format. It must have at least 12 characters, including 1 uppercase letter, 1 lowercase letter, 1 digit, and 1 special character.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Set the default role to "user"
                $user_role = "user";

                // Insert user details into the database with hashed password and default role
                $insert_query = "INSERT INTO users (id, nis, full_name, gender, place_of_birth, date_of_birth, address, telephone, email, password, role)
                                VALUES ('$random_id', '$nis', '$full_name', '$gender', '$place_of_birth', '$date_of_birth', '$address', '$telephone', '$email', '$hashed_password', '$user_role')";

                if ($conn->query($insert_query) === TRUE) {
                    // Display success message using JavaScript
                    echo '<script>alert("Pendaftaran Berhasil");</script>';

                    // Redirect to login page upon successful registration after a short delay
                    echo '<script>
                            setTimeout(function() {
                                window.location.href = "index.php";
                            }, 1000);
                          </script>';
                    exit();
                } else {
                    $error_message = "Error: " . $conn->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="register.css">
    <title>User Registration - Digital Library</title>
</head>

<body>
    <div class="registration-container">
        <h2>User Registration</h2>

        <!-- Display error message if registration fails -->
        <?php if (isset($error_message)) : ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="nis">NIS</label>
            <input type="text" placeholder="nis must contain 10 number of your id" id="nis" name="nis" pattern="[0-9]{10}" required>

            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>

            <label for="place_of_birth">Place of Birth:</label>
            <input type="text" id="place_of_birth" name="place_of_birth" required>

            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" required>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="telephone">Telephone:</label>
            <input type="tel" id="telephone" name="telephone"required>

            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" placeholder="It must have at least 12 unique characters" name="password" required>

            <button type="submit">Register</button>

            <p>Already have an account? <a href="index.php">Login here</a></p>
        </form>
    </div>
</body>

</html>
