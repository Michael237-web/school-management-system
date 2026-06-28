<?php
// student-add.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Add Student';
$school = new SchoolManagement();

// Check if user is admin
if (!$auth->isAdmin()) {
    ?>
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lock"></i> Access Restricted
                    </h5>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-user-shield text-danger" style="font-size: 64px;"></i>
                    </div>
                    <h4 class="text-danger">Administrator Access Required</h4>
                    <p class="text-muted mb-4">
                        Only administrators with the appropriate permissions can add new students to the system.
                    </p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Student records require proper foreign key associations with classes, 
                        sections, and user accounts. Only administrators have the necessary privileges to create 
                        and manage these relationships.
                    </div>
                    <a href="dashboard.php" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-left"></i> Return to Dashboard
                    </a>
                    <a href="students.php" class="btn btn-secondary mt-3">
                        <i class="fas fa-users"></i> View Students
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once 'footer.php';
    exit();
}

$classes = $school->getAllClasses();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify secret key for security
    $secretKey = $_POST['secret_key'] ?? '';
    $validSecretKey = 'SCHOOL_ADMIN_2024'; // Change this to your desired secret key
    
    if ($secretKey !== $validSecretKey) {
        // Show error popup message
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                }
                .modal-content-custom {
                    background: white;
                    border-radius: 15px;
                    padding: 40px;
                    max-width: 500px;
                    width: 90%;
                    text-align: center;
                    animation: slideIn 0.3s ease-out;
                }
                @keyframes slideIn {
                    from {
                        transform: translateY(-100px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                .modal-icon {
                    font-size: 70px;
                    margin-bottom: 20px;
                }
                .modal-title-custom {
                    font-size: 24px;
                    font-weight: 700;
                    margin-bottom: 15px;
                }
                .modal-text {
                    color: #6c757d;
                    margin-bottom: 25px;
                    line-height: 1.6;
                }
                .btn-custom {
                    padding: 10px 30px;
                    border-radius: 25px;
                    font-weight: 600;
                }
            </style>
        </head>
        <body>
            <div class="modal-overlay">
                <div class="modal-content-custom">
                    <div class="modal-icon">
                        <i class="fas fa-key text-warning"></i>
                    </div>
                    <h4 class="modal-title-custom text-warning">Security Verification Required</h4>
                    <p class="modal-text">
                        <i class="fas fa-shield-alt text-warning me-2"></i>
                        You don't have the required security keys to add a new student.
                        Please contact the system administrator for assistance.
                    </p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Adding students requires special administrative privileges 
                        and security verification.
                    </div>
                    <a href="students.php" class="btn btn-primary btn-custom">
                        <i class="fas fa-arrow-left"></i> Return to Students
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary btn-custom ms-2">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        exit();
    }
    
    $data = [
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'admission_date' => $_POST['admission_date'] ?? date('Y-m-d'),
        'class_id' => $_POST['class_id'] ?? null,
        'section' => $_POST['section'] ?? '',
        'academic_year' => $_POST['academic_year'] ?? date('Y'),
        'gender' => $_POST['gender'] ?? '',
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'address' => $_POST['address'] ?? '',
        'emergency_contact' => $_POST['emergency_contact'] ?? '',
        'parent_name' => $_POST['parent_name'] ?? '',
        'parent_phone' => $_POST['parent_phone'] ?? '',
        'parent_email' => $_POST['parent_email'] ?? ''
    ];
    
    // Validate
    $errors = [];
    if (empty($data['full_name'])) $errors[] = 'Full name is required';
    if (empty($data['email'])) $errors[] = 'Email is required';
    if (empty($data['class_id'])) $errors[] = 'Class is required';
    if (empty($data['gender'])) $errors[] = 'Gender is required';
    
    if (empty($errors)) {
        $result = $school->addStudent($data);
        if ($result['success']) {
            $_SESSION['success'] = 'Student added successfully';
            header('Location: students.php');
            exit();
        } else {
            $error = displayMessage($result['message'], 'danger');
        }
    } else {
        $error = displayMessage(implode('<br>', $errors), 'danger');
    }
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus"></i> Add New Student
                </h5>
            </div>
            <div class="card-body">
                <?php echo $error ?? ''; ?>
                
                <form method="POST">
                    <!-- Secret Key Field - Added for security -->
                    <div class="alert alert-warning">
                        <i class="fas fa-key"></i> 
                        <strong>Security Key Required:</strong> Please enter the administrator security key to add a new student.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Security Key *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" name="secret_key" class="form-control" 
                                   placeholder="Enter administrator security key" required>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Contact the system administrator for the security key
                        </small>
                    </div>
                    
                    <hr>
                    
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
                            <input type="text" name="password" class="form-control" value="password">
                            <small class="text-muted">Default: password</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admission Date *</label>
                            <input type="date" name="admission_date" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="text" name="academic_year" class="form-control" 
                                   value="<?php echo date('Y'); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Class *</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" name="section" class="form-control" placeholder="e.g., A">
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" 
                                   max="<?php echo date('Y-m-d'); ?>">
                            <small class="text-muted">Format: YYYY-MM-DD</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-control">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Parent Information</h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parent Name</label>
                            <input type="text" name="parent_name" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parent Phone</label>
                            <input type="text" name="parent_phone" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parent Email</label>
                            <input type="email" name="parent_email" class="form-control">
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="students.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>