<?php
// profile.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'My Profile';
$user = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $result = $auth->changePassword(
            $user['id'],
            $_POST['old_password'] ?? '',
            $_POST['new_password'] ?? ''
        );
        if ($result['success']) {
            $message = displayMessage($result['message']);
        } else {
            $error = displayMessage($result['message'], 'danger');
        }
    } else {
        // Update profile - FIXED: Use table prefix
        $conn = Database::getInstance()->getConnection();
        $usersTable = Database::table('users');
        $fullName = $conn->real_escape_string($_POST['full_name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        
        $sql = "UPDATE $usersTable SET full_name = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $fullName, $phone, $user['id']);
        
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $fullName;
            $message = displayMessage('Profile updated successfully');
            $user = $auth->getCurrentUser();
        } else {
            $error = displayMessage('Failed to update profile: ' . $conn->error, 'danger');
        }
    }
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="avatar-lg mx-auto mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                <p class="text-muted"><?php echo htmlspecialchars($user['role']); ?></p>
                <hr>
                <p class="text-start">
                    <strong><i class="fas fa-envelope"></i> Email:</strong><br>
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>
                <p class="text-start">
                    <strong><i class="fas fa-phone"></i> Phone:</strong><br>
                    <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?>
                </p>
                <p class="text-start">
                    <strong><i class="fas fa-calendar-alt"></i> Member since:</strong><br>
                    <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0">Update Profile</h6>
            </div>
            <div class="card-body">
                <?php echo $message ?? ''; ?>
                <?php echo $error ?? ''; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card shadow mt-4">
            <div class="card-header">
                <h6 class="mb-0">Change Password</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="change_password" value="1">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>