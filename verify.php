<?php
session_start();
require_once 'header.php';
require_once 'config/database.php';
require_once 'models/Certificate.php';
require_once 'models/VerificationLog.php';

$database = new Database();
$db = $database->getConnection();
$cert = new Certificate($db);
$log = new VerificationLog($db);

$verification_result = null;
$error = null;

if (isset($_GET['cert']) || isset($_POST['cert'])) {
    $cert_number = $_GET['cert'] ?? $_POST['cert'];
    $cert_number = trim($cert_number);

    if (!empty($cert_number)) {
        $result = $cert->verify($cert_number);

        if ($result && $result['status'] != 'Draft') {
            $verification_result = $result;

            // Log verification
            $log->certificate_id = $result['id'];
            $log->ip_address = $_SERVER['REMOTE_ADDR'];
            $log->create();
        } else {
            $error = "Certificate not found or not published.";
        }
    } else {
        $error = "Please enter a certificate number.";
    }
}
?>

<div class="container py-5 min-vh-100">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-5">
                <h1 class="display-5">Verify Certificate</h1>
                <p class="text-muted">Enter the certificate number or scan the QR code to verify authenticity.</p>
            </div>

            <div class="card shadow mb-5">
                <div class="card-body p-4">
                    <form method="POST" action="verify.php" class="d-flex">
                        <input type="text" name="cert" class="form-control form-control-lg me-2" placeholder="e.g. ISO-2023-ABCDEF" value="<?= htmlspecialchars($_GET['cert'] ?? $_POST['cert'] ?? '') ?>" required>
                        <button type="submit" class="btn btn-primary btn-lg px-4">Verify</button>
                    </form>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger text-center shadow-sm">
                    <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                    <strong>Verification Failed:</strong> <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($verification_result): ?>
                <div class="card shadow border-top border-primary border-4">
                    <div class="card-header bg-white text-center py-4">
                        <?php if ($verification_result['status'] == 'Published'): ?>
                            <div class="text-success mb-2"><i class="fas fa-check-circle fa-4x"></i></div>
                            <h3 class="text-success mb-0">Certificate is Valid</h3>
                        <?php elseif ($verification_result['status'] == 'Expired'): ?>
                            <div class="text-warning mb-2"><i class="fas fa-exclamation-triangle fa-4x"></i></div>
                            <h3 class="text-warning mb-0">Certificate has Expired</h3>
                        <?php elseif ($verification_result['status'] == 'Revoked'): ?>
                            <div class="text-danger mb-2"><i class="fas fa-times-circle fa-4x"></i></div>
                            <h3 class="text-danger mb-0">Certificate has been Revoked</h3>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless">
                            <tr>
                                <th width="35%" class="text-muted text-end pe-4">Certificate Number</th>
                                <td><strong><?= htmlspecialchars($verification_result['certificate_number']) ?></strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted text-end pe-4">Organization Name</th>
                                <td><?= htmlspecialchars($verification_result['company_name']) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted text-end pe-4">ISO Standard</th>
                                <td><span class="badge bg-info text-dark fs-6"><?= htmlspecialchars($verification_result['iso_standard']) ?></span></td>
                            </tr>
                            <tr>
                                <th class="text-muted text-end pe-4">Scope</th>
                                <td><?= nl2br(htmlspecialchars($verification_result['scope'])) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted text-end pe-4">Issue Date</th>
                                <td><?= date('F d, Y', strtotime($verification_result['issue_date'])) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted text-end pe-4">Expiry Date</th>
                                <td><?= date('F d, Y', strtotime($verification_result['expiry_date'])) ?></td>
                            </tr>
                        </table>
                    </div>
                    <?php if ($verification_result['pdf_file'] && $verification_result['status'] == 'Published'): ?>
                    <div class="card-footer bg-light text-center py-3">
                        <a href="uploads/certificates/<?= htmlspecialchars($verification_result['pdf_file']) ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i> Download Certificate PDF
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
