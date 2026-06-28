<?php
// staff-add.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole('admin');

$pageTitle = 'Add Staff';
$school = new SchoolManagement();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'joining_date' => $_POST['joining_date'] ?? date('Y-m-d'),
        'designation' => $_POST['designation'] ?? '',
        'department' => $_POST['department'] ?? '',
        'qualification' => $_POST['qualification'] ?? '',
        'salary' => $_POST['salary'] ?? 0,
        'gender' => $_POST['gender'] ?? '',
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'address' => $_POST['address'] ?? '',
        'role' => $_POST['role'] ?? 'teacher'
    ];
    
    $result = $school->addStaff($data);
    if ($result['success']) {
        $_SESSION['success'] = 'Staff added successfully';
        header('Location: staff.php');
        exit();
    } else {
        $error = displayMessage($result['message'], 'danger');
    }
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Add New Staff</h5>
            </div>
            <div class="card-body">
                <?php echo $error ?? ''; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="text" name="password" class="form-control" value="password123">
                            <small class="text-muted">Default: password123</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joining Date *</label>
                            <input type="date" name="joining_date" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation *</label>
                            <input type="text" name="designation" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Salary</label>
                            <input type="number" name="salary" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender *</label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="teacher">Teacher</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="staff.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>