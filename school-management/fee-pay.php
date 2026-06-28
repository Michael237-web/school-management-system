<?php
// fee-pay.php
require_once 'header.php';
require_once 'functions.php';
require_once 'auth.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Process Payment';
$school = new SchoolManagement();

$id = $_GET['id'] ?? 0;
$fee = $school->getFee($id);

if (!$fee) {
    header('Location: fees.php?error=Fee not found');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? 0;
    $paymentMethod = $_POST['payment_method'] ?? '';
    $transactionId = $_POST['transaction_id'] ?? '';
    
    if ($amount > 0 && $amount <= $fee['due_amount']) {
        $result = $school->processPayment($id, $amount, $paymentMethod, $transactionId);
        if ($result['success']) {
            $_SESSION['success'] = 'Payment processed successfully';
            header('Location: fees.php');
            exit();
        } else {
            $error = displayMessage($result['message'], 'danger');
        }
    } else {
        $error = displayMessage('Invalid payment amount', 'danger');
    }
}
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Process Payment</h5>
            </div>
            <div class="card-body">
                <!-- Fee Details -->
                <div class="mb-4 p-3 bg-light rounded">
                    <h6>Fee Details</h6>
                    <div class="row">
                        <div class="col-6">
                            <strong>Student:</strong> <?php echo htmlspecialchars($fee['student_name'] ?? 'N/A'); ?>
                        </div>
                        <div class="col-6">
                            <strong>Invoice:</strong> <?php echo htmlspecialchars($fee['invoice_number']); ?>
                        </div>
                        <div class="col-6">
                            <strong>Total Amount:</strong> <?php echo formatMoney($fee['amount']); ?>
                        </div>
                        <div class="col-6">
                            <strong>Due Amount:</strong> <?php echo formatMoney($fee['due_amount']); ?>
                        </div>
                        <div class="col-12">
                            <strong>Status:</strong> <?php echo getStatusBadge($fee['status']); ?>
                        </div>
                    </div>
                </div>
                
                <?php echo $error ?? ''; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Payment Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" class="form-control" 
                                   step="0.01" min="0.01" max="<?php echo $fee['due_amount']; ?>"
                                   placeholder="0.00" required>
                        </div>
                        <small class="text-muted">Maximum: <?php echo formatMoney($fee['due_amount']); ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="card">Card</option>
                            <option value="mobile">Mobile Money</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control" 
                               placeholder="Optional">
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="fees.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Process Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>