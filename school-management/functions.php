<?php
// functions.php
require_once 'database.php';

class SchoolManagement {
    private $db;
    private $tables;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->tables = [
            'users' => Database::table('users'),
            'students' => Database::table('students'),
            'staff' => Database::table('staff'),
            'classes' => Database::table('classes'),
            'attendance' => Database::table('attendance'),
            'fee_payments' => Database::table('fee_payments'),
            'fee_structures' => Database::table('fee_structures'),
        ];
    }
    
    // ============ STUDENT FUNCTIONS ============
    
    public function getAllStudents($page = 1, $search = '', $classId = null) {
        $conn = $this->db->getConnection();
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $conditions = [];
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $search = $conn->real_escape_string($search);
            $conditions[] = "(s.admission_number LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "sss";
        }
        
        if ($classId !== null && $classId !== '') {
            $conditions[] = "s.class_id = ?";
            $params[] = $classId;
            $types .= "i";
        }
        
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM {$this->tables['students']} s 
                    LEFT JOIN {$this->tables['users']} u ON s.user_id = u.id 
                    $whereClause";
        $countStmt = $conn->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];
        
        // Get data
        $sql = "SELECT s.*, u.full_name, u.email, u.phone, c.name as class_name 
                FROM {$this->tables['students']} s 
                LEFT JOIN {$this->tables['users']} u ON s.user_id = u.id 
                LEFT JOIN {$this->tables['classes']} c ON s.class_id = c.id 
                $whereClause 
                ORDER BY s.id DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $params[] = ITEMS_PER_PAGE;
        $params[] = $offset;
        $types .= "ii";
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $students = [];
        
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        return [
            'data' => $students,
            'total' => $total,
            'current_page' => $page,
            'per_page' => ITEMS_PER_PAGE,
            'total_pages' => ceil($total / ITEMS_PER_PAGE)
        ];
    }
    
    public function getStudent($id) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT s.*, u.full_name, u.email, u.phone, c.name as class_name, c.numeric_name 
                FROM {$this->tables['students']} s 
                LEFT JOIN {$this->tables['users']} u ON s.user_id = u.id 
                LEFT JOIN {$this->tables['classes']} c ON s.class_id = c.id 
                WHERE s.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    public function addStudent($data) {
        $conn = $this->db->getConnection();
        
        // Generate admission number
        $admissionNumber = $this->generateAdmissionNumber();
        
        // Create user account if not exists
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            $auth = new Auth();
            $userData = [
                'username' => $data['email'],
                'email' => $data['email'],
                'password' => $data['password'] ?? 'password',
                'full_name' => $data['full_name'],
                'phone' => $data['phone'] ?? '',
                'role' => 'student'
            ];
            
            $result = $auth->register($userData);
            if (!$result['success']) {
                return $result;
            }
            $userId = $result['user_id'];
        } else {
            $userId = $data['user_id'];
        }
        
        // Prepare data with default values
        $admissionDate = !empty($data['admission_date']) ? $data['admission_date'] : date('Y-m-d');
        $classId = isset($data['class_id']) ? (int)$data['class_id'] : null;
        $section = $data['section'] ?? '';
        $academicYear = $data['academic_year'] ?? date('Y');
        $gender = $data['gender'] ?? '';
        $address = $data['address'] ?? '';
        $emergencyContact = $data['emergency_contact'] ?? '';
        $parentName = $data['parent_name'] ?? '';
        $parentPhone = $data['parent_phone'] ?? '';
        $parentEmail = $data['parent_email'] ?? '';
        
        // Validate and sanitize date of birth
        $dateOfBirth = null;
        if (!empty($data['date_of_birth'])) {
            $dob = trim($data['date_of_birth']);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                $dateParts = explode('-', $dob);
                $year = (int)$dateParts[0];
                $month = (int)$dateParts[1];
                $day = (int)$dateParts[2];
                if ($year >= 1900 && $year <= date('Y') && checkdate($month, $day, $year)) {
                    $dateOfBirth = $dob;
                }
            }
        }
        
        // Insert student
        $sql = "INSERT INTO {$this->tables['students']} (
                    user_id, admission_number, admission_date, class_id, section, 
                    academic_year, gender, date_of_birth, address, emergency_contact,
                    parent_name, parent_phone, parent_email
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ississsssssss",
            $userId,
            $admissionNumber,
            $admissionDate,
            $classId,
            $section,
            $academicYear,
            $gender,
            $dateOfBirth,
            $address,
            $emergencyContact,
            $parentName,
            $parentPhone,
            $parentEmail
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'student_id' => $conn->insert_id];
        }
        
        return ['success' => false, 'message' => 'Failed to add student: ' . $conn->error];
    }
    
    public function updateStudent($id, $data) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE {$this->tables['students']} SET 
                    class_id = ?, section = ?, academic_year = ?, 
                    gender = ?, date_of_birth = ?, address = ?, 
                    emergency_contact = ?, parent_name = ?, parent_phone = ?, 
                    parent_email = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssssssi",
            $data['class_id'],
            $data['section'] ?? '',
            $data['academic_year'] ?? date('Y'),
            $data['gender'],
            $data['date_of_birth'],
            $data['address'] ?? '',
            $data['emergency_contact'] ?? '',
            $data['parent_name'] ?? '',
            $data['parent_phone'] ?? '',
            $data['parent_email'] ?? '',
            $id
        );
        
        if ($stmt->execute()) {
            // Update user if needed
            if (isset($data['full_name']) || isset($data['email']) || isset($data['phone'])) {
                $userSql = "UPDATE {$this->tables['users']} SET ";
                $updates = [];
                $params = [];
                $types = "";
                
                if (isset($data['full_name'])) {
                    $updates[] = "full_name = ?";
                    $params[] = $data['full_name'];
                    $types .= "s";
                }
                if (isset($data['email'])) {
                    $updates[] = "email = ?";
                    $params[] = $data['email'];
                    $types .= "s";
                }
                if (isset($data['phone'])) {
                    $updates[] = "phone = ?";
                    $params[] = $data['phone'];
                    $types .= "s";
                }
                
                if (!empty($updates)) {
                    $userSql .= implode(", ", $updates) . " WHERE id = (SELECT user_id FROM {$this->tables['students']} WHERE id = ?)";
                    $params[] = $id;
                    $types .= "i";
                    
                    $userStmt = $conn->prepare($userSql);
                    $userStmt->bind_param($types, ...$params);
                    $userStmt->execute();
                }
            }
            
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Failed to update student: ' . $conn->error];
    }
    
    public function deleteStudent($id) {
        $conn = $this->db->getConnection();
        
        // Get user_id
        $student = $this->getStudent($id);
        if (!$student) {
            return ['success' => false, 'message' => 'Student not found'];
        }
        
        // Delete student
        $sql = "DELETE FROM {$this->tables['students']} WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete user if not linked to other records
            $checkSql = "SELECT COUNT(*) as count FROM {$this->tables['students']} WHERE user_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("i", $student['user_id']);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $count = $checkResult->fetch_assoc()['count'];
            
            if ($count == 0) {
                $userSql = "DELETE FROM {$this->tables['users']} WHERE id = ?";
                $userStmt = $conn->prepare($userSql);
                $userStmt->bind_param("i", $student['user_id']);
                $userStmt->execute();
            }
            
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Failed to delete student'];
    }
    
    private function generateAdmissionNumber() {
        $conn = $this->db->getConnection();
        $year = date('Y');
        
        $sql = "SELECT MAX(CAST(SUBSTRING(admission_number, 6) AS UNSIGNED)) as max_num 
                FROM {$this->tables['students']} 
                WHERE admission_number LIKE 'STU$year%'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $num = ($row['max_num'] ?? 0) + 1;
        
        return 'STU' . $year . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
    
    // ============ FEE FUNCTIONS ============
    
    public function getAllFees($page = 1, $status = null, $studentId = null) {
        $conn = $this->db->getConnection();
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $conditions = [];
        $params = [];
        $types = "";
        
        if ($status !== null && $status !== '') {
            $conditions[] = "f.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if ($studentId !== null && $studentId !== '') {
            $conditions[] = "f.student_id = ?";
            $params[] = $studentId;
            $types .= "i";
        }
        
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM {$this->tables['fee_payments']} f $whereClause";
        $countStmt = $conn->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];
        
        // Get data
        $sql = "SELECT f.*, s.admission_number, u.full_name as student_name, 
                       c.name as class_name, fs.name as fee_structure_name
                FROM {$this->tables['fee_payments']} f
                LEFT JOIN {$this->tables['students']} s ON f.student_id = s.id
                LEFT JOIN {$this->tables['users']} u ON s.user_id = u.id
                LEFT JOIN {$this->tables['classes']} c ON s.class_id = c.id
                LEFT JOIN {$this->tables['fee_structures']} fs ON f.fee_structure_id = fs.id
                $whereClause
                ORDER BY f.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $params[] = ITEMS_PER_PAGE;
        $params[] = $offset;
        $types .= "ii";
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $fees = [];
        
        while ($row = $result->fetch_assoc()) {
            $fees[] = $row;
        }
        
        return [
            'data' => $fees,
            'total' => $total,
            'current_page' => $page,
            'per_page' => ITEMS_PER_PAGE,
            'total_pages' => ceil($total / ITEMS_PER_PAGE)
        ];
    }
    
    public function getFee($id) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT f.*, s.admission_number, u.full_name as student_name,
                       c.name as class_name, fs.* as fee_structure
                FROM {$this->tables['fee_payments']} f
                LEFT JOIN {$this->tables['students']} s ON f.student_id = s.id
                LEFT JOIN {$this->tables['users']} u ON s.user_id = u.id
                LEFT JOIN {$this->tables['classes']} c ON s.class_id = c.id
                LEFT JOIN {$this->tables['fee_structures']} fs ON f.fee_structure_id = fs.id
                WHERE f.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    public function generateFee($data) {
        $conn = $this->db->getConnection();
        
        // Get fee structure
        $feeStructure = $this->getFeeStructure($data['fee_structure_id']);
        if (!$feeStructure) {
            return ['success' => false, 'message' => 'Fee structure not found'];
        }
        
        $totalAmount = $feeStructure['tuition_fee'] + 
                       $feeStructure['admission_fee'] + 
                       $feeStructure['transport_fee'] + 
                       $feeStructure['library_fee'] + 
                       $feeStructure['sports_fee'] + 
                       $feeStructure['other_fee'];
        
        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();
        
        $sql = "INSERT INTO {$this->tables['fee_payments']} (
                    student_id, fee_structure_id, invoice_number,
                    amount, paid_amount, due_amount, due_date,
                    status, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissdsss",
            $data['student_id'],
            $data['fee_structure_id'],
            $invoiceNumber,
            $totalAmount,
            0,
            $totalAmount,
            $data['due_date'],
            'pending',
            $data['remarks'] ?? ''
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'fee_id' => $conn->insert_id];
        }
        
        return ['success' => false, 'message' => 'Failed to generate fee: ' . $conn->error];
    }
    
    public function processPayment($feeId, $amount, $paymentMethod, $transactionId = null) {
        $conn = $this->db->getConnection();
        
        // Get current fee
        $fee = $this->getFee($feeId);
        if (!$fee) {
            return ['success' => false, 'message' => 'Fee not found'];
        }
        
        if ($amount > $fee['due_amount']) {
            return ['success' => false, 'message' => 'Payment amount exceeds due amount'];
        }
        
        $newPaidAmount = $fee['paid_amount'] + $amount;
        $newDueAmount = $fee['due_amount'] - $amount;
        $status = $newDueAmount <= 0 ? 'paid' : 'partial';
        
        $sql = "UPDATE {$this->tables['fee_payments']} SET 
                    paid_amount = ?,
                    due_amount = ?,
                    payment_date = NOW(),
                    payment_method = ?,
                    transaction_id = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddsssi",
            $newPaidAmount,
            $newDueAmount,
            $paymentMethod,
            $transactionId,
            $status,
            $feeId
        );
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Failed to process payment: ' . $conn->error];
    }
    
    private function generateInvoiceNumber() {
        $conn = $this->db->getConnection();
        $year = date('Y');
        
        $sql = "SELECT COUNT(*) as count FROM {$this->tables['fee_payments']} WHERE YEAR(created_at) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $num = $row['count'] + 1;
        
        return 'INV-' . $year . '-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
    
    public function getFeeStructure($id) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM {$this->tables['fee_structures']} WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    public function getAllFeeStructures() {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT fs.*, c.name as class_name 
                FROM {$this->tables['fee_structures']} fs
                LEFT JOIN {$this->tables['classes']} c ON fs.class_id = c.id
                ORDER BY fs.name";
        $result = $conn->query($sql);
        $feeStructures = [];
        
        while ($row = $result->fetch_assoc()) {
            $feeStructures[] = $row;
        }
        
        return $feeStructures;
    }
    
    public function getFeeStatistics() {
        $conn = $this->db->getConnection();
        
        $stats = [];
        
        // Total revenue
        $sql = "SELECT SUM(paid_amount) as total FROM {$this->tables['fee_payments']} WHERE status = 'paid'";
        $result = $conn->query($sql);
        $stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Total due
        $sql = "SELECT SUM(due_amount) as total FROM {$this->tables['fee_payments']} WHERE status IN ('pending', 'partial')";
        $result = $conn->query($sql);
        $stats['total_due'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Payment counts
        $sql = "SELECT status, COUNT(*) as count FROM {$this->tables['fee_payments']} GROUP BY status";
        $result = $conn->query($sql);
        $stats['payment_counts'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['payment_counts'][$row['status']] = $row['count'];
        }
        
        // Total payments count
        $sql = "SELECT COUNT(*) as count FROM {$this->tables['fee_payments']} WHERE status = 'paid'";
        $result = $conn->query($sql);
        $stats['total_payments'] = $result->fetch_assoc()['count'] ?? 0;
        
        // Pending payments
        $sql = "SELECT COUNT(*) as count FROM {$this->tables['fee_payments']} WHERE status = 'pending'";
        $result = $conn->query($sql);
        $stats['pending_payments'] = $result->fetch_assoc()['count'] ?? 0;
        
        return $stats;
    }
    
    // ============ STAFF FUNCTIONS ============
    
    public function getAllStaff($page = 1, $search = '') {
        $conn = $this->db->getConnection();
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $conditions = [];
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $search = $conn->real_escape_string($search);
            $conditions[] = "(s.staff_id LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "sss";
        }
        
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM {$this->tables['staff']} s 
                    LEFT JOIN {$this->tables['users']} u ON s.user_id = u.id 
                    $whereClause";
        $countStmt = $conn->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];
        
        // Get data
        $sql = "SELECT s.*, u.full_name, u.email, u.phone 
                FROM {$this->tables['staff']} s 
                LEFT JOIN {$this->tables['users']} u ON s.user_id = u.id 
                $whereClause 
                ORDER BY s.id DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $params[] = ITEMS_PER_PAGE;
        $params[] = $offset;
        $types .= "ii";
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $staff = [];
        
        while ($row = $result->fetch_assoc()) {
            $staff[] = $row;
        }
        
        return [
            'data' => $staff,
            'total' => $total,
            'current_page' => $page,
            'per_page' => ITEMS_PER_PAGE,
            'total_pages' => ceil($total / ITEMS_PER_PAGE)
        ];
    }
    
    public function addStaff($data) {
        $conn = $this->db->getConnection();
        
        // Generate staff ID
        $staffId = $this->generateStaffId();
        
        // Create user account
        $auth = new Auth();
        $userData = [
            'username' => $data['email'],
            'email' => $data['email'],
            'password' => $data['password'] ?? 'password',
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? '',
            'role' => $data['role'] ?? 'teacher'
        ];
        
        $result = $auth->register($userData);
        if (!$result['success']) {
            return $result;
        }
        $userId = $result['user_id'];
        
        // Insert staff
        $sql = "INSERT INTO {$this->tables['staff']} (
                    user_id, staff_id, joining_date, designation, department,
                    qualification, salary, gender, date_of_birth, address, phone
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdsssssss",
            $userId,
            $staffId,
            $data['joining_date'],
            $data['designation'],
            $data['department'] ?? '',
            $data['qualification'] ?? '',
            $data['salary'] ?? 0,
            $data['gender'],
            $data['date_of_birth'],
            $data['address'] ?? '',
            $data['phone'] ?? ''
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'staff_id' => $conn->insert_id];
        }
        
        return ['success' => false, 'message' => 'Failed to add staff: ' . $conn->error];
    }
    
    private function generateStaffId() {
        $conn = $this->db->getConnection();
        $year = date('Y');
        
        $sql = "SELECT MAX(CAST(SUBSTRING(staff_id, 5) AS UNSIGNED)) as max_num 
                FROM {$this->tables['staff']} 
                WHERE staff_id LIKE 'STA$year%'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $num = ($row['max_num'] ?? 0) + 1;
        
        return 'STA' . $year . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
    
    // ============ CLASS FUNCTIONS ============
    
    public function getAllClasses() {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT c.*, u.full_name as teacher_name 
                FROM {$this->tables['classes']} c
                LEFT JOIN {$this->tables['staff']} s ON c.teacher_id = s.id
                LEFT JOIN {$this->tables['users']} u ON s.user_id = u.id
                ORDER BY c.numeric_name";
        $result = $conn->query($sql);
        $classes = [];
        
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
        
        return $classes;
    }
    
    public function getClassStudents($classId) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT s.*, u.full_name, u.email, u.phone 
                FROM {$this->tables['students']} s
                LEFT JOIN {$this->tables['users']} u ON s.user_id = u.id
                WHERE s.class_id = ? AND s.is_active = 1
                ORDER BY u.full_name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = [];
        
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        return $students;
    }
    
    // ============ ATTENDANCE FUNCTIONS ============
    
    public function markAttendance($data) {
        $conn = $this->db->getConnection();
        
        $sql = "INSERT INTO {$this->tables['attendance']} (student_id, class_id, date, status, remark) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = ?, remark = ?, updated_at = NOW()";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissss",
            $data['student_id'],
            $data['class_id'],
            $data['date'],
            $data['status'],
            $data['remark'] ?? '',
            $data['status'],
            $data['remark'] ?? ''
        );
        
        return $stmt->execute();
    }
    
    public function getAttendance($classId, $date) {
        $conn = $this->db->getConnection();
        
        // First, get all students in the class
        $studentsSql = "SELECT s.id as student_id, s.admission_number, u.full_name as student_name
                        FROM {$this->tables['students']} s
                        JOIN {$this->tables['users']} u ON s.user_id = u.id
                        WHERE s.class_id = ? AND s.is_active = 1
                        ORDER BY u.full_name";
        $studentsStmt = $conn->prepare($studentsSql);
        $studentsStmt->bind_param("i", $classId);
        $studentsStmt->execute();
        $studentsResult = $studentsStmt->get_result();
        
        // Get existing attendance records for this class and date
        $attendanceSql = "SELECT a.*, s.admission_number, u.full_name as student_name
                          FROM {$this->tables['attendance']} a
                          JOIN {$this->tables['students']} s ON a.student_id = s.id
                          JOIN {$this->tables['users']} u ON s.user_id = u.id
                          WHERE a.class_id = ? AND a.date = ?";
        $attendanceStmt = $conn->prepare($attendanceSql);
        $attendanceStmt->bind_param("is", $classId, $date);
        $attendanceStmt->execute();
        $attendanceResult = $attendanceStmt->get_result();
        
        // Create an associative array of existing attendance records
        $attendanceMap = [];
        while ($row = $attendanceResult->fetch_assoc()) {
            $attendanceMap[$row['student_id']] = $row;
        }
        
        // Build the final attendance array
        $attendance = [];
        while ($student = $studentsResult->fetch_assoc()) {
            if (isset($attendanceMap[$student['student_id']])) {
                $attendance[] = $attendanceMap[$student['student_id']];
            } else {
                $attendance[] = [
                    'id' => null,
                    'student_id' => $student['student_id'],
                    'class_id' => $classId,
                    'date' => $date,
                    'status' => 'not_marked',
                    'remark' => 'Attendance not marked',
                    'admission_number' => $student['admission_number'],
                    'student_name' => $student['student_name']
                ];
            }
        }
        
        return $attendance;
    }
    
    public function getAttendanceStats($studentId, $startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT status, COUNT(*) as count 
                FROM {$this->tables['attendance']} 
                WHERE student_id = ? AND date BETWEEN ? AND ?
                GROUP BY status";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $studentId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = [];
        
        while ($row = $result->fetch_assoc()) {
            $stats[$row['status']] = $row['count'];
        }
        
        $total = array_sum($stats);
        $present = ($stats['present'] ?? 0) + ($stats['late'] ?? 0);
        $rate = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'present' => $present,
            'absent' => $stats['absent'] ?? 0,
            'late' => $stats['late'] ?? 0,
            'excused' => $stats['excused'] ?? 0,
            'rate' => $rate
        ];
    }
    
    // ============ DASHBOARD FUNCTIONS ============
    
    public function getDashboardStats() {
        $conn = $this->db->getConnection();
        
        $stats = [];
        
        // Total students
        $result = $conn->query("SELECT COUNT(*) as count FROM {$this->tables['students']} WHERE is_active = 1");
        $stats['total_students'] = $result->fetch_assoc()['count'] ?? 0;
        
        // Total staff
        $result = $conn->query("SELECT COUNT(*) as count FROM {$this->tables['staff']} WHERE is_active = 1");
        $stats['total_staff'] = $result->fetch_assoc()['count'] ?? 0;
        
        // Total teachers
        $result = $conn->query("SELECT COUNT(*) as count FROM {$this->tables['staff']} WHERE designation LIKE '%teacher%' AND is_active = 1");
        $stats['teachers'] = $result->fetch_assoc()['count'] ?? 0;
        
        // Total classes
        $result = $conn->query("SELECT COUNT(*) as count FROM {$this->tables['classes']}");
        $stats['total_classes'] = $result->fetch_assoc()['count'] ?? 0;
        
        // Fee statistics
        $feeStats = $this->getFeeStatistics();
        $stats['total_revenue'] = $feeStats['total_revenue'];
        $stats['total_due'] = $feeStats['total_due'];
        
        // Today's attendance
        $today = date('Y-m-d');
        $result = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM {$this->tables['attendance']} WHERE date = '$today' AND status IN ('present', 'late')");
        $stats['today_attendance'] = $result->fetch_assoc()['count'] ?? 0;
        
        // Attendance rate (this month)
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        $result = $conn->query("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as present
            FROM {$this->tables['attendance']} 
            WHERE date BETWEEN '$startDate' AND '$endDate'
        ");
        $attendanceData = $result->fetch_assoc();
        $stats['attendance_rate'] = $attendanceData['total'] > 0 ? 
            round(($attendanceData['present'] / $attendanceData['total']) * 100, 2) : 0;
        
        return $stats;
    }
    
    public function getRecentActivities() {
        $conn = $this->db->getConnection();
        
        $activities = [];
        
        // Recent students
        $sql = "SELECT CONCAT('New student: ', u.full_name) as description, s.created_at 
                FROM {$this->tables['students']} s 
                JOIN {$this->tables['users']} u ON s.user_id = u.id 
                ORDER BY s.created_at DESC LIMIT 5";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        
        // Recent payments
        $sql = "SELECT CONCAT('Payment received: ', u.full_name, ' - $', f.paid_amount) as description, f.created_at 
                FROM {$this->tables['fee_payments']} f 
                JOIN {$this->tables['students']} s ON f.student_id = s.id 
                JOIN {$this->tables['users']} u ON s.user_id = u.id 
                WHERE f.status = 'paid'
                ORDER BY f.created_at DESC LIMIT 5";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        
        // Sort by created_at
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }
    
    public function getUpcomingBirthdays($days = 7) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT s.*, u.full_name 
                FROM {$this->tables['students']} s 
                JOIN {$this->tables['users']} u ON s.user_id = u.id 
                WHERE DATE_ADD(s.date_of_birth, 
                    INTERVAL YEAR(CURDATE()) - YEAR(s.date_of_birth) + 
                    IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(s.date_of_birth), 1, 0) YEAR) 
                    BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY MONTH(s.date_of_birth), DAY(s.date_of_birth)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $days);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = [];
        
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        return $students;
    }
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function displayMessage($message, $type = 'success') {
    $class = $type === 'success' ? 'alert-success' : 'alert-danger';
    return "<div class='alert $class alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

function formatMoney($amount) {
    return '$' . number_format($amount, 2);
}

function getStatusBadge($status) {
    $colors = [
        'active' => 'success',
        'inactive' => 'danger',
        'pending' => 'warning',
        'paid' => 'success',
        'partial' => 'info',
        'overdue' => 'danger',
        'present' => 'success',
        'absent' => 'danger',
        'late' => 'warning',
        'excused' => 'info',
        'not_marked' => 'secondary'
    ];
    
    $color = $colors[$status] ?? 'secondary';
    $displayName = $status === 'not_marked' ? 'Not Marked' : ucfirst($status);
    
    return "<span class='badge bg-$color'>$displayName</span>";
}