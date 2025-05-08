<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfjdb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = null;


if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    $update_sql = "UPDATE users SET username = '$username', full_name = '$full_name', email = '$email', role = '$role', status = '$status' WHERE id = $user_id";
    
    if ($conn->query($update_sql) === TRUE) {
        header("Location: manage_users.php"); 
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit User - MFJ Airconditioning</title>
    </head>
    <body>

    <h2>Edit User</h2>

    <?php if ($user): ?>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" value="<?php echo $user['username']; ?>" required><br>

            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo $user['email']; ?>" required><br>

            <label for="role">Role:</label>
            <input type="text" name="role" value="<?php echo $user['role']; ?>" required><br>

            <label for="status">Status:</label>
            <input type="text" name="status" value="<?php echo $user['status']; ?>" required><br>

            <button type="submit">Save Changes</button>
        </form>
    <?php else: ?>
        <p>User not found.</p>
    <?php endif; ?>

    </body>
</html>
