<?php
session_start();
require "../config/db.php";

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $password_confirm) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing_user = $stmt->fetch();

        if ($existing_user) {
            $error = "User with this email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $hashed_password])) {
                $success = "Registration successful! You can now <a href='" . BASE_URL . "auth/login.php'>login</a>.";
            } else {
                $error = "An error occurred during registration. Please try again.";
            }
        }
    }
}
?>

<?php include "../partials/header.php"; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Register
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php else: ?>
                    <form method="post">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../partials/footer.php"; ?>
