<?php
// attendance-mark.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Mark Attendance';
$school = new SchoolManagement();

// Check if user is admin or teacher (attendance can be marked by both)
$isAdmin = $auth->isAdmin();
$isTeacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';

// Allow both admin and teachers to mark attendance
if (!$isAdmin && !$isTeacher) {
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
                    <h4 class="text-danger">Unauthorized Access</h4>
                    <p class="text-muted mb-4">
                        Only administrators and teachers are authorized to mark student attendance.
                    </p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Attendance marking requires special classroom management 
                        privileges. Please contact the school administrator if you need access.
                    </div>
                    <a href="dashboard.php" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-left"></i> Return to Dashboard
                    </a>
                    <a href="attendance.php" class="btn btn-secondary mt-3">
                        <i class="fas fa-clipboard-list"></i> View Attendance
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
$selectedClass = $_GET['class_id'] ?? $_POST['class_id'] ?? '';
$selectedDate = $_GET['date'] ?? $_POST['date'] ?? date('Y-m-d');

$students = [];
if ($selectedClass) {
    $students = $school->getClassStudents($selectedClass);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
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
                        <i class="fas fa-ban text-danger"></i>
                    </div>
                    <h4 class="modal-title-custom text-danger">Attendance Marking Restricted</h4>
                    <p class="modal-text">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        You do not have permission to mark attendance for this class. 
                        This action requires teacher or administrative privileges.
                    </p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Attendance records are critical for student tracking. 
                        Please ensure you have the correct authorization to mark attendance.
                    </div>
                    <a href="attendance.php" class="btn btn-primary btn-custom">
                        <i class="fas fa-arrow-left"></i> Return to Attendance
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
    
    $success = true;
    foreach ($_POST['attendance'] as $studentId => $data) {
        $attendanceData = [
            'student_id' => $studentId,
            'class_id' => $selectedClass,
            'date' => $selectedDate,
            'status' => $data['status'] ?? 'absent',
            'remark' => $data['remark'] ?? ''
        ];
        
        if (!$school->markAttendance($attendanceData)) {
            $success = false;
        }
    }
    
    if ($success) {
        $_SESSION['success'] = 'Attendance marked successfully';
        header('Location: attendance.php?class_id=' . $selectedClass . '&date=' . $selectedDate);
        exit();
    } else {
        $error = displayMessage('Some attendance records failed to save', 'danger');
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-clipboard-check"></i> Mark Attendance
        </h1>
        
        <!-- Filter -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" 
                                    <?php echo $selectedClass == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" 
                               value="<?php echo $selectedDate; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-users"></i> Load
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="attendance.php" class="btn btn-secondary w-100">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($selectedClass && !empty($students)): ?>
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-day"></i> 
                        Mark Attendance - <?php echo date('d M Y', strtotime($selectedDate)); ?>
                        <span class="badge bg-primary ms-2">
                            <?php echo count($students); ?> Students
                        </span>
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="class_id" value="<?php echo $selectedClass; ?>">
                        <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
                        
                        <!-- Security Key Field -->
                        <div class="alert alert-warning">
                            <i class="fas fa-key"></i> 
                            <strong>Security Key Required:</strong> Please enter the security key to mark attendance.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Security Key *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" name="secret_key" class="form-control" 
                                       placeholder="Enter security key" required>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Contact the system administrator for the security key
                            </small>
                        </div>
                        
                        <hr>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Admission No.</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td>
                                                <select name="attendance[<?php echo $student['id']; ?>][status]" 
                                                        class="form-select form-select-sm">
                                                    <option value="present">
                                                        <i class="fas fa-check-circle text-success"></i> Present
                                                    </option>
                                                    <option value="absent" selected>
                                                        <i class="fas fa-times-circle text-danger"></i> Absent
                                                    </option>
                                                    <option value="late">
                                                        <i class="fas fa-clock text-warning"></i> Late
                                                    </option>
                                                    <option value="excused">
                                                        <i class="fas fa-check text-info"></i> Excused
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="attendance[<?php echo $student['id']; ?>][remark]" 
                                                       class="form-control form-control-sm" 
                                                       placeholder="Optional remark">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Quick Action Buttons -->
                        <div class="mb-3">
                            <label class="form-label">Quick Actions</label>
                            <div>
                                <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">
                                    <i class="fas fa-check-circle"></i> All Present
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                                    <i class="fas fa-times-circle"></i> All Absent
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" onclick="markAll('late')">
                                    <i class="fas fa-clock"></i> All Late
                                </button>
                                <button type="button" class="btn btn-sm btn-info" onclick="markAll('excused')">
                                    <i class="fas fa-check"></i> All Excused
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="attendance.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Attendance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($selectedClass): ?>
            <div class="card shadow">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-user-graduate fa-4x mb-3"></i>
                    <h5>No Students Found</h5>
                    <p>There are no students enrolled in this class.</p>
                    <a href="students.php?class_id=<?php echo $selectedClass; ?>" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Students to this Class
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-hand-pointer fa-4x mb-3"></i>
                    <h5>Select a Class</h5>
                    <p>Please select a class from the dropdown above and click Load to mark attendance.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function markAll(status) {
    var selects = document.querySelectorAll('select[name^="attendance"]');
    selects.forEach(function(select) {
        var options = select.options;
        for (var i = 0; i < options.length; i++) {
            if (options[i].value === status) {
                select.selectedIndex = i;
                break;
            }
        }
    });
}
</script>

<?php require_once 'footer.php'; ?>