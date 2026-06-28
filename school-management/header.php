<?php
// header.php
require_once 'config.php';
require_once 'auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() && basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'register.php') {
    header('Location: login.php');
    exit();
}

$currentUser = $auth->isLoggedIn() ? $auth->getCurrentUser() : null;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <?php if ($auth->isLoggedIn()): ?>
    <!-- ============================================ -->
    <!-- TOP NAVIGATION BAR -->
    <!-- ============================================ -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <!-- Brand Logo -->
            <a class="navbar-brand" href="dashboard.php">
                <div class="brand-wrapper">
                    <div class="brand-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="brand-text">
                        <span class="brand-title"><?php echo APP_NAME; ?></span>
                        <span class="brand-subtitle">School Management System</span>
                    </div>
                </div>
            </a>
            
            <!-- Toggler Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Left Side Navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Students Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['students.php', 'student-add.php', 'student-edit.php', 'student-view.php']) ? 'active' : ''; ?>" 
                           href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users"></i>
                            <span>Students</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-animated">
                            <li>
                                <a class="dropdown-item <?php echo $currentPage === 'students.php' ? 'active' : ''; ?>" href="students.php">
                                    <i class="fas fa-list"></i> All Students
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $currentPage === 'student-add.php' ? 'active' : ''; ?>" href="student-add.php">
                                    <i class="fas fa-user-plus"></i> Add Student
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Staff Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['staff.php', 'staff-add.php', 'staff-edit.php']) ? 'active' : ''; ?>" 
                           href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Staff</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-animated">
                            <li>
                                <a class="dropdown-item <?php echo $currentPage === 'staff.php' ? 'active' : ''; ?>" href="staff.php">
                                    <i class="fas fa-list"></i> All Staff
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $currentPage === 'staff-add.php' ? 'active' : ''; ?>" href="staff-add.php">
                                    <i class="fas fa-user-plus"></i> Add Staff
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Classes -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'classes.php' ? 'active' : ''; ?>" href="classes.php">
                            <i class="fas fa-book-open"></i>
                            <span>Classes</span>
                        </a>
                    </li>
                    
                    <!-- Fees Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['fees.php', 'fee-add.php', 'fee-pay.php']) ? 'active' : ''; ?>" 
                           href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Fees</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-animated">
                            <li>
                                <a class="dropdown-item <?php echo $currentPage === 'fees.php' ? 'active' : ''; ?>" href="fees.php">
                                    <i class="fas fa-list"></i> All Fees
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo $currentPage === 'fee-add.php' ? 'active' : ''; ?>" href="fee-add.php">
                                    <i class="fas fa-plus-circle"></i> Generate Fee
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Attendance -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array($currentPage, ['attendance.php', 'attendance-mark.php']) ? 'active' : ''; ?>" href="attendance.php">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Attendance</span>
                        </a>
                    </li>
                    
                    <!-- Reports -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                </ul>
                
                <!-- Right Side - User Profile -->
                <ul class="navbar-nav">
                    <!-- Desktop User Dropdown -->
                    <li class="nav-item dropdown user-dropdown-desktop">
                        <a class="nav-link user-dropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                <span class="avatar-text"><?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 2)); ?></span>
                            </div>
                            <div class="user-info d-none d-lg-block">
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
                                <span class="user-role">
                                    <i class="fas fa-circle <?php echo $_SESSION['role'] === 'admin' ? 'text-success' : 'text-info'; ?>"></i>
                                    <span class="role-badge <?php echo $_SESSION['role'] ?? 'staff'; ?>">
                                        <?php echo ucfirst($_SESSION['role'] ?? 'Staff'); ?>
                                    </span>
                                </span>
                            </div>
                            <i class="fas fa-chevron-down user-chevron"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-animated">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-circle"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </li>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-database"></i> System Settings
                                </a>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Mobile User Dropdown -->
                    <li class="nav-item dropdown user-dropdown-mobile">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-animated">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-circle"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </li>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-database"></i> System Settings
                                </a>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="page-content">
    <?php endif; ?>
    <script src="js/script.js"></script>