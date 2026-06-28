<?php
// student-view.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Student Details';
$school = new SchoolManagement();

$id = $_GET['id'] ?? 0;
$student = $school->getStudent($id);

if (!$student) {
    header('Location: students.php?error=Student not found');
    exit();
}

// Get attendance stats
$attendanceStats = $school->getAttendanceStats($id, date('Y-m-01'), date('Y-m-t'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Student Profile</h1>
            <div>
                <a href="student-edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="students.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Profile Info -->
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <div class="avatar-lg mx-auto mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h5 class="mb-1"><?php echo htmlspecialchars($student['full_name']); ?></h5>
                <p class="text-muted"><?php echo htmlspecialchars($student['admission_number']); ?></p>
                <hr>
                <div class="text-start">
                    <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                    <p><strong><i class="fas fa-phone"></i> Phone:</strong> <?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></p>
                    <p><strong><i class="fas fa-venus-mars"></i> Gender:</strong> <?php echo $student['gender'] ?? 'N/A'; ?></p>
                    <p><strong><i class="fas fa-calendar-alt"></i> DOB:</strong> <?php echo $student['date_of_birth'] ? date('d M Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></p>
                    <p><strong><i class="fas fa-book"></i> Class:</strong> <?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></p>
                    <p><strong><i class="fas fa-layer-group"></i> Section:</strong> <?php echo htmlspecialchars($student['section'] ?? 'N/A'); ?></p>
                    <p><strong><i class="fas fa-calendar-check"></i> Admission:</strong> <?php echo date('d M Y', strtotime($student['admission_date'])); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Parent Info -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">Parent Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($student['parent_name'] ?? 'N/A'); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['parent_phone'] ?? 'N/A'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['parent_email'] ?? 'N/A'); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($student['address'] ?? 'N/A'); ?></p>
                <p><strong>Emergency:</strong> <?php echo htmlspecialchars($student['emergency_contact'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Statistics and Data -->
    <div class="col-md-8">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card bg-primary text-white shadow">
                    <div class="card-body">
                        <h6>Total Fees</h6>
                        <h4><?php echo formatMoney($student['total_fees'] ?? 0); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-success text-white shadow">
                    <div class="card-body">
                        <h6>Paid Fees</h6>
                        <h4><?php echo formatMoney($student['paid_fees'] ?? 0); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-danger text-white shadow">
                    <div class="card-body">
                        <h6>Due Fees</h6>
                        <h4><?php echo formatMoney($student['due_fees'] ?? 0); ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fee Payments -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">Fee Payments</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fees = $school->getAllFees(1, null, $student['id']);
                            if (empty($fees['data'])): ?>
                                <tr><td colspan="6" class="text-center">No fee records found</td></tr>
                            <?php else: ?>
                                <?php foreach ($fees['data'] as $fee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fee['invoice_number']); ?></td>
                                        <td><?php echo formatMoney($fee['amount']); ?></td>
                                        <td><?php echo formatMoney($fee['paid_amount']); ?></td>
                                        <td><?php echo formatMoney($fee['due_amount']); ?></td>
                                        <td><?php echo getStatusBadge($fee['status']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($fee['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Attendance -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">Attendance (This Month)</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <h5><?php echo $attendanceStats['present'] ?? 0; ?></h5>
                        <small class="text-success">Present</small>
                    </div>
                    <div class="col-3">
                        <h5><?php echo $attendanceStats['absent'] ?? 0; ?></h5>
                        <small class="text-danger">Absent</small>
                    </div>
                    <div class="col-3">
                        <h5><?php echo $attendanceStats['late'] ?? 0; ?></h5>
                        <small class="text-warning">Late</small>
                    </div>
                    <div class="col-3">
                        <h5><?php echo $attendanceStats['rate'] ?? 0; ?>%</h5>
                        <small class="text-primary">Rate</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Grades -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0">Grades</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Total</th>
                                <th>Grade</th>
                                <th>Term</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center">Grade module coming soon</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>