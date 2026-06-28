<?php
// students.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Students';
$school = new SchoolManagement();

// Get filter parameters
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$classId = $_GET['class_id'] ?? null;

$students = $school->getAllStudents($page, $search, $classId);
$classes = $school->getAllClasses();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $result = $school->deleteStudent($_GET['delete']);
    if ($result['success']) {
        $message = displayMessage('Student deleted successfully');
    } else {
        $message = displayMessage('Failed to delete student: ' . $result['message'], 'danger');
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Student Management</h1>
            <a href="student-add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Student
            </a>
        </div>
        
        <?php echo $message ?? ''; ?>
        
        <!-- Filter -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name, email or admission number"
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
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
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="students.php" class="btn btn-secondary w-100">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Students Table -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Admission No.</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students['data'])): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No students found</td>
                                </tr>
                            <?php else: ?>
                                <?php $counter = ($students['current_page'] - 1) * $students['per_page'] + 1; ?>
                                <?php foreach ($students['data'] as $student): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                        <td><?php echo $student['is_active'] ? getStatusBadge('active') : getStatusBadge('inactive'); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="student-view.php?id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="student-edit.php?id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $student['id']; ?>" 
                                                   class="btn btn-danger" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this student?')">
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
                
                <!-- Pagination -->
                <?php if ($students['total_pages'] > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($students['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $students['current_page'] - 1; ?>&search=<?php echo urlencode($search); ?>&class_id=<?php echo $classId; ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $students['total_pages']; $i++): ?>
                                <li class="page-item <?php echo $i == $students['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&class_id=<?php echo $classId; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($students['current_page'] < $students['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $students['current_page'] + 1; ?>&search=<?php echo urlencode($search); ?>&class_id=<?php echo $classId; ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>