<?php
require_once 'header.php';
require_once '../config/database.php';
require_once '../models/Inquiry.php';

$database = new Database();
$db = $database->getConnection();
$inq = new Inquiry($db);
$stmt = $inq->readAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Customer Inquiries</h4>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                        <small>
                            E: <?= htmlspecialchars($row['email']) ?><br>
                            P: <?= htmlspecialchars($row['phone']) ?>
                        </small>
                    </td>
                    <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.querySelector('.sidebar a.active').classList.remove('active');
    document.querySelector('.sidebar a[href="inquiries.php"]').classList.add('active');
</script>

<?php require_once 'footer.php'; ?>
