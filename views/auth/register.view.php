<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body class="register-page-container">
    <div class="register-card">
        <div class="register-header">
            <div class="eco-badge">New Explorer</div>
            <h1>Create Account</h1>
            <p>Join our community of sustainable travelers today.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="register-alert error">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i> </span>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="register-alert success">
                <span class="alert-icon">✅</span>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="register-form">
            <div class="register-field">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="John Doe" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="register-field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="explorer@nature.com" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="register-grid">
                <div class="register-field">
                    <label for="password">Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <span class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="register-field">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                        <span class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>

            <button type="submit" class="register-submit-btn">
                Create My Account
            </button>
        </form>

        <div class="register-footer">
            <p>Already a member? <a href="login.php">Log in here</a></p>
            <a href="../index.php" class="register-home-link">← Back to Home</a>
        </div>
    </div>
</body>
</html>
