<?php
session_start();
require_once 'header.php';
require_once 'config/database.php';
require_once 'models/Inquiry.php';

$success_msg = '';
$error_msg = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $database = new Database();
    $db = $database->getConnection();
    $inq = new Inquiry($db);

    $inq->name = $_POST['name'];
    $inq->email = $_POST['email'];
    $inq->phone = $_POST['phone'];
    $inq->message = $_POST['message'];

    if($inq->create()){
        $success_msg = "Thank you! Your inquiry has been submitted successfully. Our team will contact you shortly.";
    } else {
        $error_msg = "Something went wrong. Please try again.";
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="text-center mb-4">Contact Us</h1>
            <p class="text-center text-muted mb-5">Have questions about ISO certification? Fill out the form below and our experts will get back to you.</p>

            <?php if($success_msg): ?>
                <div class="alert alert-success"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div class="alert alert-danger"><?= $error_msg ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="contact.php">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">How can we help you? *</label>
                            <textarea name="message" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Submit Inquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
