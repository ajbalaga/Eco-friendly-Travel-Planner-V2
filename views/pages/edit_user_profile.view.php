<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Eco-Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/edit_user_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body class="profile-page-container">

    <div class="profile-card">
        <div class="profile-header">
            <h1>Edit Profile</h1>
            <p>Keep your travel profile fresh and secure.</p>
        </div>

        <?php if ($success): ?>
            <div class="profile-alert success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php elseif ($info): ?>
            <div class="profile-alert info"><i class="fas fa-info-circle"></i> <?php echo $info; ?></div>
        <?php elseif (!empty($errors)): ?>
            <div class="profile-alert error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo implode('<br>', $errors); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="profile-form" id="profileForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="avatar-edit-section">
                <div class="avatar-circle">
                    <img id="preview" src="<?php echo $display_img; ?>" alt="Profile">
                    <label for="imageUpload" class="camera-overlay" title="Upload New Photo">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
                <input type="file" name="profile_image" id="imageUpload" hidden accept="image/*">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="userName">Full Name</label>
                    <input type="text" name="name" id="userName" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="userEmail">Email Address</label>
                    <input type="email" name="email" id="userEmail" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>

            <div class="password-divider">
                <span>Change Password</span>
                <small>Leave empty to keep current</small>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="newPass">New Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" name="new_password" id="newPass" placeholder="••••••••">
                        <span class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPass">Confirm Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" name="confirm_password" id="confirmPass" placeholder="••••••••">
                        <span class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>

            <button type="submit" class="save-btn" id="submitBtn" disabled>Save Changes</button>
        </form>

        <div class="profile-footer">
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        const form = document.getElementById('profileForm');
        const submitBtn = document.getElementById('submitBtn');
        const imgInput = document.getElementById('imageUpload');

        const originalData = {
            name: document.getElementById('userName').value.trim(),
            email: document.getElementById('userEmail').value.trim()
        };

        const detectChanges = () => {
            const currentName = document.getElementById('userName').value.trim();
            const currentEmail = document.getElementById('userEmail').value.trim();
            const passValue = document.getElementById('newPass').value;
            const hasFile = imgInput.files.length > 0;

            const changed =
                currentName !== originalData.name ||
                currentEmail !== originalData.email ||
                passValue.length > 0 ||
                hasFile;

            submitBtn.disabled = !changed;
        };

        form.addEventListener('input', detectChanges);

        imgInput.onchange = function() {
            const [file] = this.files;
            if (file) {
                document.getElementById('preview').src = URL.createObjectURL(file);
                detectChanges();
            }
        };
    </script>
</body>
</html>
