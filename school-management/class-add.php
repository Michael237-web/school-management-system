<?php
// class-add.php
require_once 'config.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = Database::getInstance()->getConnection();
    $table = Database::table('classes');
    
    $name = $conn->real_escape_string($_POST['name']);
    $numeric_name = (int)$_POST['numeric_name'];
    $academic_year = $conn->real_escape_string($_POST['academic_year'] ?? date('Y'));
    $capacity = (int)($_POST['capacity'] ?? 30);
    
    $sql = "INSERT INTO $table (name, numeric_name, academic_year, capacity) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisi", $name, $numeric_name, $academic_year, $capacity);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Class added successfully';
    } else {
        $_SESSION['error'] = 'Failed to add class: ' . $conn->error;
    }
}

header('Location: classes.php');
exit();
?>