<?php
session_start();
require_once '../config/database.php';
require_once '../models/Certificate.php';
require_once '../models/Organization.php';

$database = new Database();
$db = $database->getConnection();
$cert = new Certificate($db);
$org = new Organization($db);

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF Token Validation Failed.");
    }

    if (isset($_POST['delete_certificate'])) {
        $cert->id = $_POST['id'];
        if ($cert->delete()) {
            $_SESSION['message'] = "Certificate deleted successfully.";
        }
    } elseif (isset($_POST['update_status'])) {
        $cert->id = $_POST['id'];
        $cert->status = $_POST['status'];
        if ($cert->updateStatus()) {
            $_SESSION['message'] = "Certificate status updated to " . htmlspecialchars($_POST['status']) . ".";
        }
    } else {
        $cert->organization_id = $_POST['organization_id'];
        $cert->main_type = $_POST['main_type'];
        $cert->iso_standard = $_POST['iso_standard'];
        $cert->scope = $_POST['scope'];
        $cert->issue_date = $_POST['issue_date'];
        $cert->expiry_date = $_POST['expiry_date'];
        $cert->status = $_POST['status'] ?? 'Draft';

        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $cert->id = $_POST['id'];
            if ($cert->update()) {
                $_SESSION['message'] = "Certificate updated successfully.";
            }
        } else {
            $cert->certificate_number = $cert->generateCertificateNumber();
            if ($cert->create()) {
                $_SESSION['message'] = "Certificate created successfully. Please generate the PDF.";
                header("Location: generate_certificate_file.php?id=" . $cert->id);
                exit();
            } else {
                $_SESSION['error'] = "Failed to create certificate. Ensure all fields are valid and the database schema is updated.";
            }
        }
    }
    header("Location: certificates.php");
    exit();
}

require_once 'header.php';

if ($action == 'edit' || $action == 'add') {
    if ($action == 'edit' && isset($_GET['id'])) {
        $cert->id = $_GET['id'];
        $cert->readOne();
    }
    $orgs_stmt = $org->readAll();
?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= $action == 'edit' ? 'Edit' : 'Create' ?> Certificate</h5>
            <a href="certificates.php" class="btn btn-sm btn-secondary">Back to List</a>
        </div>
        <div class="card-body">
            <form method="POST" action="certificates.php">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <?php if($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($cert->id) ?>">
                    <div class="mb-3">
                        <label class="form-label text-muted">Certificate Number: <strong><?= htmlspecialchars($cert->certificate_number) ?></strong></label>
                    </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Organization *</label>
                        <select name="organization_id" class="form-select" required>
                            <option value="">Select Organization</option>
                            <?php while($org_row = $orgs_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?= $org_row['id'] ?>" <?= (isset($cert->organization_id) && $cert->organization_id == $org_row['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($org_row['company_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Main Type *</label>
                        <select name="main_type" class="form-select" required>
                            <option value="IAF" <?= (isset($cert->main_type) && $cert->main_type == 'IAF') ? 'selected' : '' ?>>IAF</option>
                            <option value="Non-IAF" <?= (isset($cert->main_type) && $cert->main_type == 'Non-IAF') ? 'selected' : '' ?>>Non-IAF</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ISO Standard *</label>
                        <select name="iso_standard" class="form-select" required>
                            <option value="">Select Standard</option>
                            <optgroup label="General & Quality Management">
                                <option value="ISO 9001:2015" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 9001:2015') ? 'selected' : '' ?>>ISO 9001:2015 — Quality Management Systems (QMS)</option>
                                <option value="ISO 10002:2018" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 10002:2018') ? 'selected' : '' ?>>ISO 10002:2018 — Customer Satisfaction and Complaint Handling</option>
                            </optgroup>
                            <optgroup label="IT, Security & Technology">
                                <option value="ISO/IEC 27001:2022" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO/IEC 27001:2022') ? 'selected' : '' ?>>ISO/IEC 27001:2022 — Information Security Management Systems (ISMS)</option>
                                <option value="ISO/IEC 27017" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO/IEC 27017') ? 'selected' : '' ?>>ISO/IEC 27017 — Cloud Security Controls</option>
                                <option value="ISO/IEC 27018" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO/IEC 27018') ? 'selected' : '' ?>>ISO/IEC 27018 — Cloud Privacy Protection</option>
                                <option value="ISO/IEC 20000-1:2018" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO/IEC 20000-1:2018') ? 'selected' : '' ?>>ISO/IEC 20000-1:2018 — IT Service Management (ITSM)</option>
                                <option value="ISO/IEC 42001:2023" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO/IEC 42001:2023') ? 'selected' : '' ?>>ISO/IEC 42001:2023 — Artificial Intelligence Management Systems (AIMS)</option>
                            </optgroup>
                            <optgroup label="Operations & Risk Management">
                                <option value="ISO 14001:2015/2026" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 14001:2015/2026') ? 'selected' : '' ?>>ISO 14001:2015/2026 — Environmental Management Systems (EMS)</option>
                                <option value="ISO 45001:2018" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 45001:2018') ? 'selected' : '' ?>>ISO 45001:2018 — Occupational Health and Safety Management Systems (OH&S)</option>
                                <option value="ISO 22301:2019" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 22301:2019') ? 'selected' : '' ?>>ISO 22301:2019 — Business Continuity Management Systems (BCMS)</option>
                                <option value="ISO 37001:2025" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 37001:2025') ? 'selected' : '' ?>>ISO 37001:2025 — Anti-Bribery Management Systems (ABMS)</option>
                                <option value="ISO 50001:2018" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 50001:2018') ? 'selected' : '' ?>>ISO 50001:2018 — Energy Management Systems (EnMS)</option>
                            </optgroup>
                            <optgroup label="Specialized Industry Standards">
                                <option value="ISO 22000:2018" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 22000:2018') ? 'selected' : '' ?>>ISO 22000:2018 — Food Safety Management Systems (FSMS)</option>
                                <option value="FSSC 22000" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'FSSC 22000') ? 'selected' : '' ?>>FSSC 22000 — Food Safety System Certification (often used with ISO 22000)</option>
                                <option value="ISO 13485:2016" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 13485:2016') ? 'selected' : '' ?>>ISO 13485:2016 — Quality Management Systems for Medical Devices</option>
                                <option value="ISO 21001:2018/2025" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 21001:2018/2025') ? 'selected' : '' ?>>ISO 21001:2018/2025 — Educational Organizations Management Systems (EOMS)</option>
                                <option value="ISO 37101:2016" <?= (isset($cert->iso_standard) && $cert->iso_standard == 'ISO 37101:2016') ? 'selected' : '' ?>>ISO 37101:2016 — Sustainable Development for Communities and Cities</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Certification Scope *</label>
                    <textarea name="scope" class="form-control" rows="3" required><?= htmlspecialchars($cert->scope ?? '') ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Issue Date *</label>
                        <input type="date" name="issue_date" class="form-control" value="<?= htmlspecialchars($cert->issue_date ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiry Date *</label>
                        <input type="date" name="expiry_date" class="form-control" value="<?= htmlspecialchars($cert->expiry_date ?? date('Y-m-d', strtotime('+3 years'))) ?>" required>
                    </div>
                    <?php if($action == 'edit'): ?>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Draft" <?= ($cert->status == 'Draft') ? 'selected' : '' ?>>Draft</option>
                            <option value="Published" <?= ($cert->status == 'Published') ? 'selected' : '' ?>>Published</option>
                            <option value="Expired" <?= ($cert->status == 'Expired') ? 'selected' : '' ?>>Expired</option>
                            <option value="Revoked" <?= ($cert->status == 'Revoked') ? 'selected' : '' ?>>Revoked</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary"><?= $action == 'edit' ? 'Save Changes' : 'Create Certificate' ?></button>
            </form>
        </div>
    </div>
<?php
} else {
    $stmt = $cert->readAll();
?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Certificates</h4>
        <a href="certificates.php?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Create New</a>
    </div>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Cert #</th>
                            <th>Organization</th>
                            <th>Standard</th>
                            <th>Issue/Expiry</th>
                            <th>Status</th>
                            <th>Files</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['certificate_number']) ?></strong></td>
                            <td><?= htmlspecialchars($row['company_name']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['iso_standard']) ?></span></td>
                            <td>
                                <small>
                                    Iss: <?= date('M d, Y', strtotime($row['issue_date'])) ?><br>
                                    Exp: <?= date('M d, Y', strtotime($row['expiry_date'])) ?>
                                </small>
                            </td>
                            <td>
                                <?php
                                $badge_class = 'secondary';
                                if($row['status'] == 'Published') $badge_class = 'success';
                                if($row['status'] == 'Expired') $badge_class = 'warning text-dark';
                                if($row['status'] == 'Revoked') $badge_class = 'danger';
                                ?>
                                <span class="badge bg-<?= $badge_class ?>"><?= $row['status'] ?></span>
                            </td>
                            <td>
                                <?php if($row['pdf_file']): ?>
                                    <a href="../uploads/certificates/<?= htmlspecialchars($row['pdf_file']) ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="View PDF"><i class="fas fa-file-pdf"></i></a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="certificates.php?action=edit&id=<?= $row['id'] ?>"><i class="fas fa-edit"></i> Edit Details</a></li>
                                        <li><a class="dropdown-item" href="generate_certificate_file.php?id=<?= $row['id'] ?>"><i class="fas fa-sync"></i> Regenerate PDF/QR</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="certificates.php" class="px-3 py-1">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <div class="input-group input-group-sm">
                                                    <select name="status" class="form-select form-select-sm">
                                                        <option value="Draft" <?= $row['status']=='Draft'?'selected':'' ?>>Draft</option>
                                                        <option value="Published" <?= $row['status']=='Published'?'selected':'' ?>>Publish</option>
                                                        <option value="Revoked" <?= $row['status']=='Revoked'?'selected':'' ?>>Revoke</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-outline-secondary">Go</button>
                                                </div>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="certificates.php" class="px-3 py-1" onsubmit="return confirm('Are you sure you want to delete this certificate?');">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="delete_certificate" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="fas fa-trash"></i> Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<script>
    document.querySelector('.sidebar a.active').classList.remove('active');
    document.querySelector('.sidebar a[href="certificates.php"]').classList.add('active');
</script>

<?php require_once 'footer.php'; ?>
