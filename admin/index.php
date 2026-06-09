<?php
require_once 'header.php';
require_once '../config/database.php';
require_once '../models/Certificate.php';
require_once '../models/Organization.php';
require_once '../models/VerificationLog.php';

$database = new Database();
$db = $database->getConnection();

$cert = new Certificate($db);
$org = new Organization($db);
$log = new VerificationLog($db);

$total_certs = $cert->countByStatus();
$published_certs = $cert->countByStatus('Published');
$draft_certs = $cert->countByStatus('Draft');
$expired_certs = $cert->countByStatus('Expired');
$revoked_certs = $cert->countByStatus('Revoked');
$total_orgs = $org->count();

$recent_logs_stmt = $log->readAll();
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Certificates</h5>
                <h2 class="card-text"><?= $total_certs ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Published</h5>
                <h2 class="card-text"><?= $published_certs ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-dark bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Drafts</h5>
                <h2 class="card-text"><?= $draft_certs ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title">Expired/Revoked</h5>
                <h2 class="card-text"><?= $expired_certs + $revoked_certs ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                Recent Verification Logs
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Certificate #</th>
                            <th>IP Address</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 0;
                        while ($row = $recent_logs_stmt->fetch(PDO::FETCH_ASSOC)) {
                            if($count++ >= 5) break;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['certificate_number']) ?></td>
                            <td><?= htmlspecialchars($row['ip_address']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($row['verified_at'])) ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                System Overview
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Organizations
                        <span class="badge bg-primary rounded-pill"><?= $total_orgs ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
