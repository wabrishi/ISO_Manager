<?php
require_once 'header.php';
require_once '../config/database.php';
require_once '../models/VerificationLog.php';

$database = new Database();
$db = $database->getConnection();
$log = new VerificationLog($db);
$stmt = $log->readAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Verification Logs</h4>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Certificate Number</th>
                    <th>IP Address</th>
                    <th>Verification Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><strong><?= htmlspecialchars($row['certificate_number']) ?></strong></td>
                    <td><?= htmlspecialchars($row['ip_address']) ?></td>
                    <td><?= date('Y-m-d H:i:s', strtotime($row['verified_at'])) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.querySelector('.sidebar a.active').classList.remove('active');
    document.querySelector('.sidebar a[href="verification_logs.php"]').classList.add('active');
</script>

<?php require_once 'footer.php'; ?>
