<?php
require_once 'header.php';
require_once '../config/database.php';
require_once '../models/Organization.php';

$database = new Database();
$db = $database->getConnection();
$org = new Organization($db);

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_organization'])) {
        $org->id = $_POST['id'];
        if ($org->delete()) {
            $_SESSION['message'] = "Organization deleted successfully.";
        }
    } else {
        $org->company_name = $_POST['company_name'];
        $org->address = $_POST['address'];
        $org->contact_person = $_POST['contact_person'];
        $org->email = $_POST['email'];
        $org->phone = $_POST['phone'];
        $org->status = $_POST['status'];

        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $org->id = $_POST['id'];
            if ($org->update()) {
                $_SESSION['message'] = "Organization updated successfully.";
            }
        } else {
            if ($org->create()) {
                $_SESSION['message'] = "Organization created successfully.";
            }
        }
    }
    header("Location: organizations.php");
    exit();
}

if ($action == 'edit' || $action == 'add') {
    if ($action == 'edit' && isset($_GET['id'])) {
        $org->id = $_GET['id'];
        $org->readOne();
    }
?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= $action == 'edit' ? 'Edit' : 'Add' ?> Organization</h5>
            <a href="organizations.php" class="btn btn-sm btn-secondary">Back to List</a>
        </div>
        <div class="card-body">
            <form method="POST" action="organizations.php">
                <?php if($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($org->id) ?>">
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Company Name *</label>
                        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($org->company_name ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($org->contact_person ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($org->address ?? '') ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($org->email ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($org->phone ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= (isset($org->status) && $org->status == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (isset($org->status) && $org->status == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Organization</button>
            </form>
        </div>
    </div>
<?php
} else {
    $stmt = $org->readAll();
?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Organizations</h4>
        <a href="organizations.php?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Add New</a>
    </div>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= htmlspecialchars($row['contact_person']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['status'] == 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="organizations.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="organizations.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this organization?');">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="delete_organization" value="1">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>

<script>
    // Adjust Sidebar Active State
    document.querySelector('.sidebar a.active').classList.remove('active');
    document.querySelector('.sidebar a[href="organizations.php"]').classList.add('active');
</script>

<?php require_once 'footer.php'; ?>
