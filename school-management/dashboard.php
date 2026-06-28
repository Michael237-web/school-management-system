<?php
// dashboard.php
require_once 'header.php';
require_once 'functions.php';

$pageTitle = 'Dashboard';
$school = new SchoolManagement();
$stats = $school->getDashboardStats();
$recentActivities = $school->getRecentActivities();
$upcomingBirthdays = $school->getUpcomingBirthdays();
?>

<!-- Slideshow Section -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body p-0">
                <div id="schoolSlideshow" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="2000" data-bs-pause="false">
                    <!-- Indicators -->
                    <ol class="carousel-indicators">
                        <li data-bs-target="#schoolSlideshow" data-bs-slide-to="0" class="active"></li>
                        <li data-bs-target="#schoolSlideshow" data-bs-slide-to="1"></li>
                        <li data-bs-target="#schoolSlideshow" data-bs-slide-to="2"></li>
                        <li data-bs-target="#schoolSlideshow" data-bs-slide-to="3"></li>
                        <li data-bs-target="#schoolSlideshow" data-bs-slide-to="4"></li>
                    </ol>

                    <!-- Slides -->
                    <div class="carousel-inner">
                        <!-- Slide 1: School Aerial View -->
                        <div class="carousel-item active">
                            <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1200&h=400&fit=crop&crop=center&q=80" 
                                 class="d-block w-100 slideshow-image" 
                                 alt="School Campus Aerial View" loading="lazy">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Welcome to Our School</h3>
                                <p>Excellence in Education</p>
                            </div>
                        </div>

                        <!-- Slide 2: Students in Classroom -->
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?w=1200&h=400&fit=crop&crop=center&q=80" 
                                 class="d-block w-100 slideshow-image" 
                                 alt="Students Learning" loading="lazy">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Quality Education</h3>
                                <p>Empowering Future Leaders</p>
                            </div>
                        </div>

                        <!-- Slide 3: Library -->
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=1200&h=400&fit=crop&crop=center&q=80" 
                                 class="d-block w-100 slideshow-image" 
                                 alt="School Library" loading="lazy">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Modern Library</h3>
                                <p>Knowledge at Your Fingertips</p>
                            </div>
                        </div>

                        <!-- Slide 4: Sports -->
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=1200&h=400&fit=crop&crop=center&q=80" 
                                 class="d-block w-100 slideshow-image" 
                                 alt="Sports Activities" loading="lazy">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Sports & Activities</h3>
                                <p>Healthy Body, Healthy Mind</p>
                            </div>
                        </div>

                        <!-- Slide 5: Graduation -->
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1531545514256-b1400bc00f31?w=1200&h=400&fit=crop&crop=center&q=80" 
                                 class="d-block w-100 slideshow-image" 
                                 alt="Graduation Celebration" loading="lazy">
                            <div class="carousel-caption d-none d-md-block">
                                <h3>Celebrating Success</h3>
                                <p>Every Student Matters</p>
                            </div>
                        </div>
                    </div>

                    <!-- Controls -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#schoolSlideshow" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#schoolSlideshow" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-primary text-white shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Students</div>
                        <div class="h2 mb-0"><?php echo $stats['total_students']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-graduate fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-success text-white shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Staff</div>
                        <div class="h2 mb-0"><?php echo $stats['total_staff']; ?></div>
                        <small>Teachers: <?php echo $stats['teachers']; ?></small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard-teacher fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-warning text-white shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Revenue</div>
                        <div class="h2 mb-0"><?php echo formatMoney($stats['total_revenue']); ?></div>
                        <small>Due: <?php echo formatMoney($stats['total_due']); ?></small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-info text-white shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Attendance</div>
                        <div class="h2 mb-0"><?php echo $stats['attendance_rate']; ?>%</div>
                        <small>Today: <?php echo $stats['today_attendance']; ?></small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-check fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php if (empty($recentActivities)): ?>
                        <p class="text-muted">No recent activities</p>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <p class="mb-0"><?php echo htmlspecialchars($activity['description']); ?></p>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Upcoming Birthdays</h6>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingBirthdays)): ?>
                    <p class="text-muted">No upcoming birthdays</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($upcomingBirthdays as $student): ?>
                            <div class="list-group-item d-flex align-items-center">
                                <div class="avatar me-3">
                                    <i class="fas fa-user-circle fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($student['full_name']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo date('M d', strtotime($student['date_of_birth'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="student-add.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add New Student
                    </a>
                    <a href="fee-add.php" class="btn btn-success">
                        <i class="fas fa-money-bill"></i> Generate Fee
                    </a>
                    <a href="attendance-mark.php" class="btn btn-warning">
                        <i class="fas fa-clipboard-check"></i> Mark Attendance
                    </a>
                    <a href="reports.php" class="btn btn-info">
                        <i class="fas fa-file-alt"></i> Generate Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Contact Icons - MOVED BEFORE FOOTER -->
<div class="contact-float">
    <!-- WhatsApp -->
    <a href="https://wa.me/254746674121" target="_blank" class="contact-icon whatsapp" title="Chat on WhatsApp">
        <i class="fab fa-whatsapp"></i>
        <span class="contact-tooltip">WhatsApp</span>
    </a>
    
    <!-- Phone -->
    <a href="tel:+254746674121" class="contact-icon phone" title="Call Us">
        <i class="fas fa-phone-alt"></i>
        <span class="contact-tooltip">Call Us</span>
    </a>
    
    <!-- Email -->
    <a href="mailto:michaelmwanzia810@gmail.com" class="contact-icon email" title="Email Us">
        <i class="fas fa-envelope"></i>
        <span class="contact-tooltip">Email Us</span>
    </a>
</div>

<?php require_once 'footer.php'; ?>