<?php
// student-edit.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole('admin');

$pageTitle = 'Edit Student';
$school = new SchoolManagement();

$id = $_GET['id'] ?? 0;
$student = $school->getStudent($id);

if (!$student) {
    header('Location: students.php?error=Student not found');
    exit();
}

$classes = $school->getAllClasses();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'class_id' => $_POST['class_id'] ?? null,
        'section' => $_POST['section'] ?? '',
        'academic_year' => $_POST['academic_year'] ?? date('Y'),
        'gender' => $_POST['gender'] ?? '',
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'address' => $_POST['address'] ?? '',
        'emergency_contact' => $_POST['emergency_contact'] ?? '',
        'parent_name' => $_POST['parent_name'] ?? '',
        'parent_phone' => $_POST['parent_phone'] ?? '',
        'parent_email' => $_POST['parent_email'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? ''
    ];
    
    $result = $school->updateStudent($id, $data);
    if ($result['success']) {
        $_SESSION['success'] = 'Student updated successfully';
        header('Location: students.php');
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
                <h5 class="card-title mb-0">Edit Student</h5>
            </div>
            <div class="card-body">
                <?php echo $error ?? ''; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['phone']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admission Number</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['admission_number']); ?>" disabled>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Class *</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" 
                                        <?php echo $student['class_id'] == $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" name="section" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['section']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="text" name="academic_year" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['academic_year']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender *</label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $student['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" 
                                   value="<?php echo $student['date_of_birth']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['emergency_contact']); ?>">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($student['address']); ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Parent Information</h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parent Name</label>
                            <input type="text" name="parent_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['parent_name']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parent Phone</label>
                            <input type="text" name="parent_phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['parent_phone']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parent Email</label>
                            <input type="email" name="parent_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($student['parent_email']); ?>">
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="students.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>