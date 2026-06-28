<?php
// attendance.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Attendance Management';
$school = new SchoolManagement();

$classes = $school->getAllClasses();
$selectedClass = $_GET['class_id'] ?? '';
$selectedDate = $_GET['date'] ?? date('Y-m-d');

$attendance = [];
if ($selectedClass) {
    $attendance = $school->getAttendance($selectedClass, $selectedDate);
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="h3 mb-4">Attendance Management</h1>
        
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
                            <i class="fas fa-search"></i> View
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="attendance-mark.php" class="btn btn-success w-100">
                            <i class="fas fa-clipboard-check"></i> Mark Attendance
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($selectedClass && !empty($attendance)): ?>
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        Attendance for <?php echo date('d M Y', strtotime($selectedDate)); ?>
                    </h6>
                    <span class="badge bg-primary">
                        Total: <?php echo count($attendance); ?> students
                    </span>
                </div>
                <div class="card-body">
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
                                <?php foreach ($attendance as $record): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($record['admission_number']); ?></td>
                                        <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                        <td><?php echo getStatusBadge($record['status']); ?></td>
                                        <td><?php echo htmlspecialchars($record['remark'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($selectedClass): ?>
            <div class="card shadow">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-clipboard fa-3x mb-3"></i>
                    <p>No attendance records found for this date.</p>
                    <a href="attendance-mark.php?class_id=<?php echo $selectedClass; ?>&date=<?php echo $selectedDate; ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Mark Attendance
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>