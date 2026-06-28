<?php
// fees.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Fee Management';
$school = new SchoolManagement();

$page = $_GET['page'] ?? 1;
$status = $_GET['status'] ?? '';
$studentId = $_GET['student_id'] ?? '';

$fees = $school->getAllFees($page, $status, $studentId);
$students = $school->getAllStudents(1);
$stats = $school->getFeeStatistics();
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Fee Management</h1>
            <a href="fee-add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Generate Fee
            </a>
        </div>
        
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white shadow">
                    <div class="card-body">
                        <h6>Total Revenue</h6>
                        <h4><?php echo formatMoney($stats['total_revenue']); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white shadow">
                    <div class="card-body">
                        <h6>Total Due</h6>
                        <h4><?php echo formatMoney($stats['total_due']); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow">
                    <div class="card-body">
                        <h6>Paid</h6>
                        <h4><?php echo $stats['total_payments']; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white shadow">
                    <div class="card-body">
                        <h6>Pending</h6>
                        <h4><?php echo $stats['pending_payments']; ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filter -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $status == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="partial" <?php echo $status == 'partial' ? 'selected' : ''; ?>>Partial</option>
                            <option value="overdue" <?php echo $status == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="student_id" class="form-select">
                            <option value="">All Students</option>
                            <?php foreach ($students['data'] as $student): ?>
                                <option value="<?php echo $student['id']; ?>" 
                                    <?php echo $studentId == $student['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="fees.php" class="btn btn-secondary w-100">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="?export=1" class="btn btn-success w-100">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Fee Table -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fees['data'])): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No fee records found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($fees['data'] as $fee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fee['invoice_number']); ?></td>
                                        <td><?php echo htmlspecialchars($fee['student_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($fee['class_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo formatMoney($fee['amount']); ?></td>
                                        <td><?php echo formatMoney($fee['paid_amount']); ?></td>
                                        <td><?php echo formatMoney($fee['due_amount']); ?></td>
                                        <td><?php echo getStatusBadge($fee['status']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($fee['due_date'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="fee-pay.php?id=<?php echo $fee['id']; ?>" 
                                                   class="btn btn-success" title="Pay">
                                                    <i class="fas fa-money-bill"></i>
                                                </a>
                                                <a href="?view=<?php echo $fee['id']; ?>" 
                                                   class="btn btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?delete=<?php echo $fee['id']; ?>" 
                                                   class="btn btn-danger" title="Delete"
                                                   onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>