<?php
// classes.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Class Management';
$school = new SchoolManagement();

$classes = $school->getAllClasses();
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Class Management</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                <i class="fas fa-plus"></i> Add Class
            </button>
        </div>
        
        <!-- Classes Grid -->
        <div class="row">
            <?php if (empty($classes)): ?>
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-body text-center text-muted py-5">
                            <i class="fas fa-book fa-3x mb-3"></i>
                            <p>No classes created yet.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($classes as $class): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Teacher:</strong> <?php echo htmlspecialchars($class['teacher_name'] ?? 'Not assigned'); ?></p>
                                <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($class['academic_year'] ?? 'N/A'); ?></p>
                                <p><strong>Capacity:</strong> <?php echo $class['capacity']; ?> students</p>
                            </div>
                            <div class="card-footer">
                                <a href="students.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-users"></i> View Students
                                </a>
                                <a href="attendance.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-clipboard"></i> Attendance
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="class-add.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Class Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Numeric Name *</label>
                        <input type="number" name="numeric_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" name="academic_year" class="form-control" 
                               value="<?php echo date('Y'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-control" value="30">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>