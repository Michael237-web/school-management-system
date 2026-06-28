<?php
// fee-add.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Generate Fee';
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
                        Only administrators with the appropriate permissions can generate fees for students.
                    </p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Fee generation requires proper financial permissions and 
                        administrative privileges. Only authorized administrators can create fee records.
                    </div>
                    <a href="dashboard.php" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-left"></i> Return to Dashboard
                    </a>
                    <a href="fees.php" class="btn btn-secondary mt-3">
                        <i class="fas fa-money-bill-wave"></i> View Fees
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once 'footer.php';
    exit();
}

$students = $school->getAllStudents(1);
$feeStructures = $school->getAllFeeStructures();

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
                        <i class="fas fa-ban text-danger"></i>
                    </div>
                    <h4 class="modal-title-custom text-danger">Fee Generation Restricted</h4>
                    <p class="modal-text">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        You are not authorized to generate fees for this student. 
                        This action requires special financial administrative privileges.
                    </p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Fee generation is a sensitive financial operation. 
                        Please contact the finance administrator or system administrator for assistance.
                    </div>
                    <a href="fees.php" class="btn btn-primary btn-custom">
                        <i class="fas fa-arrow-left"></i> Return to Fees
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
        'student_id' => $_POST['student_id'] ?? null,
        'fee_structure_id' => $_POST['fee_structure_id'] ?? null,
        'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
        'remarks' => $_POST['remarks'] ?? ''
    ];
    
    $result = $school->generateFee($data);
    if ($result['success']) {
        $_SESSION['success'] = 'Fee generated successfully';
        header('Location: fees.php');
        exit();
    } else {
        $error = displayMessage($result['message'], 'danger');
    }
}
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-money-bill-wave"></i> Generate Fee
                </h5>
            </div>
            <div class="card-body">
                <?php echo $error ?? ''; ?>
                
                <form method="POST">
                    <!-- Security Key Field -->
                    <div class="alert alert-warning">
                        <i class="fas fa-key"></i> 
                        <strong>Security Key Required:</strong> Please enter the administrator security key to generate a fee.
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
                    
                    <div class="mb-3">
                        <label class="form-label">Select Student *</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Choose Student</option>
                            <?php foreach ($students['data'] as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['full_name']); ?> 
                                    (<?php echo htmlspecialchars($student['admission_number']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Fee Structure *</label>
                        <select name="fee_structure_id" class="form-select" required>
                            <option value="">Choose Fee Structure</option>
                            <?php foreach ($feeStructures as $structure): ?>
                                <option value="<?php echo $structure['id']; ?>">
                                    <?php echo htmlspecialchars($structure['name']); ?> 
                                    (<?php echo htmlspecialchars($structure['class_name'] ?? 'All Classes'); ?>)
                                    - <?php echo formatMoney($structure['tuition_fee'] + $structure['admission_fee'] + $structure['transport_fee'] + $structure['library_fee'] + $structure['sports_fee'] + $structure['other_fee']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" 
                               value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" 
                                  placeholder="Optional remarks about this fee"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="fees.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Generate Fee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>