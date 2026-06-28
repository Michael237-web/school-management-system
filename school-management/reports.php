<?php
// reports.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Reports';
$school = new SchoolManagement();

$reportType = $_GET['type'] ?? 'students';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$classId = $_GET['class_id'] ?? '';

$classes = $school->getAllClasses();
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="h3 mb-4">Reports</h1>
        
        <!-- Report Navigation -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $reportType == 'students' ? 'active' : ''; ?>" 
                   href="?type=students">Student Report</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $reportType == 'fees' ? 'active' : ''; ?>" 
                   href="?type=fees">Fee Report</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $reportType == 'attendance' ? 'active' : ''; ?>" 
                   href="?type=attendance">Attendance Report</a>
            </li>
        </ul>
        
        <!-- Report Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="type" value="<?php echo $reportType; ?>">
                    
                    <?php if ($reportType == 'students'): ?>
                        <div class="col-md-3">
                            <select name="class_id" class="form-select">
                                <option value="">All Classes</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" 
                                        <?php echo $classId == $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control" 
                                   value="<?php echo $startDate; ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control" 
                                   value="<?php echo $endDate; ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-file-alt"></i> Generate
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-success w-100" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-info w-100" onclick="exportReport()">
                            <i class="fas fa-file-excel"></i> Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Report Content -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0">
                    <?php echo ucfirst($reportType); ?> Report
                    <?php if ($reportType != 'students'): ?>
                        (<?php echo date('d M Y', strtotime($startDate)); ?> - <?php echo date('d M Y', strtotime($endDate)); ?>)
                    <?php endif; ?>
                </h6>
            </div>
            <div class="card-body">
                <?php if ($reportType == 'students'): ?>
                    <?php
                    $students = $school->getAllStudents(1, '', $classId);
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Admission No.</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Parent</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; ?>
                                <?php foreach ($students['data'] as $student): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $student['gender'] ?? 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($student['parent_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $student['is_active'] ? getStatusBadge('active') : getStatusBadge('inactive'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end">
                                        <strong>Total Students: <?php echo $students['total']; ?></strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                <?php elseif ($reportType == 'fees'): ?>
                    <?php
                    $fees = $school->getAllFees(1, null, null);
                    $totalAmount = 0;
                    $totalPaid = 0;
                    $totalDue = 0;
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Student</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fees['data'] as $fee): 
                                    $totalAmount += $fee['amount'];
                                    $totalPaid += $fee['paid_amount'];
                                    $totalDue += $fee['due_amount'];
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fee['invoice_number']); ?></td>
                                        <td><?php echo htmlspecialchars($fee['student_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo formatMoney($fee['amount']); ?></td>
                                        <td><?php echo formatMoney($fee['paid_amount']); ?></td>
                                        <td><?php echo formatMoney($fee['due_amount']); ?></td>
                                        <td><?php echo getStatusBadge($fee['status']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($fee['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="2">Totals</td>
                                    <td><?php echo formatMoney($totalAmount); ?></td>
                                    <td><?php echo formatMoney($totalPaid); ?></td>
                                    <td><?php echo formatMoney($totalDue); ?></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                <?php elseif ($reportType == 'attendance'): ?>
                    <?php
                    $students = $school->getAllStudents(1);
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                    <th>Excused</th>
                                    <th>Total</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students['data'] as $student): 
                                    $stats = $school->getAttendanceStats($student['id'], $startDate, $endDate);
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $stats['present'] ?? 0; ?></td>
                                        <td><?php echo $stats['absent'] ?? 0; ?></td>
                                        <td><?php echo $stats['late'] ?? 0; ?></td>
                                        <td><?php echo $stats['excused'] ?? 0; ?></td>
                                        <td><?php echo $stats['total']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $stats['rate'] >= 80 ? 'success' : ($stats['rate'] >= 60 ? 'warning' : 'danger'); ?>">
                                                <?php echo $stats['rate']; ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport() {
    // Simple export functionality
    var table = document.querySelector('.table');
    var html = table.outerHTML;
    var blob = new Blob([html], {type: 'text/html'});
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'report.html';
    link.click();
}
</script>

<?php require_once 'footer.php'; ?>