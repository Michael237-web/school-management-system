<?php
// staff-edit.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole('admin');

$pageTitle = 'Edit Staff';
$school = new SchoolManagement();

$id = $_GET['id'] ?? 0;

// Get staff details - FIXED: Use table prefix
$conn = Database::getInstance()->getConnection();
$staffTable = Database::table('staff');
$usersTable = Database::table('users');

$sql = "SELECT s.*, u.full_name, u.email, u.phone, u.role 
        FROM $staffTable s 
        LEFT JOIN $usersTable u ON s.user_id = u.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    header('Location: staff.php?error=Staff not found');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $designation = $conn->real_escape_string($_POST['designation']);
    $department = $conn->real_escape_string($_POST['department']);
    $qualification = $conn->real_escape_string($_POST['qualification']);
    $salary = (float)$_POST['salary'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $date_of_birth = $_POST['date_of_birth'] ?: null;
    $address = $conn->real_escape_string($_POST['address']);
    
    // Update staff
    $updateSql = "UPDATE $staffTable SET 
                    designation = ?, department = ?, qualification = ?,
                    salary = ?, gender = ?, date_of_birth = ?,
                    address = ?, phone = ?, updated_at = NOW()
                  WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssdssssi",
        $designation,
        $department,
        $qualification,
        $salary,
        $gender,
        $date_of_birth,
        $address,
        $phone,
        $id
    );
    
    if ($updateStmt->execute()) {
        // Update user
        $userSql = "UPDATE $usersTable SET full_name = ?, email = ?, phone = ? WHERE id = ?";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bind_param("sssi", $full_name, $email, $phone, $staff['user_id']);
        $userStmt->execute();
        
        $_SESSION['success'] = 'Staff updated successfully';
        header('Location: staff.php');
        exit();
    } else {
        $error = displayMessage('Failed to update staff', 'danger');
    }
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Staff</h5>
            </div>
            <div class="card-body">
                <?php echo $error ?? ''; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['phone']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Staff ID</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['staff_id']); ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation *</label>
                            <input type="text" name="designation" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['designation']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['department']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control" 
                                   value="<?php echo htmlspecialchars($staff['qualification']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Salary</label>
                            <input type="number" name="salary" class="form-control" step="0.01" 
                                   value="<?php echo $staff['salary']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male" <?php echo $staff['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $staff['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $staff['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" 
                                   value="<?php echo $staff['date_of_birth']; ?>">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($staff['address']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="staff.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>