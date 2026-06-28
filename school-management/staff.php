<?php
// staff.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Staff Management';
$school = new SchoolManagement();

$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';

$staff = $school->getAllStaff($page, $search);
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Staff Management</h1>
            <a href="staff-add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Staff
            </a>
        </div>
        
        <!-- Search -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name, email or staff ID"
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="staff.php" class="btn btn-secondary w-100">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Staff Table -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Staff ID</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($staff['data'])): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No staff found</td>
                                </tr>
                            <?php else: ?>
                                <?php $counter = ($staff['current_page'] - 1) * $staff['per_page'] + 1; ?>
                                <?php foreach ($staff['data'] as $member): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($member['staff_id']); ?></td>
                                        <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($member['designation']); ?></td>
                                        <td><?php echo htmlspecialchars($member['department'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                                        <td><?php echo $member['is_active'] ? getStatusBadge('active') : getStatusBadge('inactive'); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="staff-edit.php?id=<?php echo $member['id']; ?>" 
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $member['id']; ?>" 
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