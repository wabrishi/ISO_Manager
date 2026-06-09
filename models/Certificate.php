<?php
class Certificate {
    private $conn;
    private $table_name = "certificates";

    public $id;
    public $certificate_number;
    public $organization_id;
    public $iso_standard;
    public $scope;
    public $issue_date;
    public $expiry_date;
    public $qr_code;
    public $pdf_file;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET certificate_number=:certificate_number, organization_id=:organization_id, iso_standard=:iso_standard,
                    scope=:scope, issue_date=:issue_date, expiry_date=:expiry_date, qr_code=:qr_code, pdf_file=:pdf_file, status=:status";

        $stmt = $this->conn->prepare($query);

        $this->certificate_number=htmlspecialchars(strip_tags($this->certificate_number));
        $this->organization_id=htmlspecialchars(strip_tags($this->organization_id));
        $this->iso_standard=htmlspecialchars(strip_tags($this->iso_standard));
        $this->scope=htmlspecialchars(strip_tags($this->scope));
        $this->issue_date=htmlspecialchars(strip_tags($this->issue_date));
        $this->expiry_date=htmlspecialchars(strip_tags($this->expiry_date));
        if ($this->qr_code !== null) {
            $this->qr_code=htmlspecialchars(strip_tags($this->qr_code));
        }
        if ($this->pdf_file !== null) {
            $this->pdf_file=htmlspecialchars(strip_tags($this->pdf_file));
        }
        $this->status=htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":certificate_number", $this->certificate_number);
        $stmt->bindParam(":organization_id", $this->organization_id);
        $stmt->bindParam(":iso_standard", $this->iso_standard);
        $stmt->bindParam(":scope", $this->scope);
        $stmt->bindParam(":issue_date", $this->issue_date);
        $stmt->bindParam(":expiry_date", $this->expiry_date);

        $qr = $this->qr_code ?? null;
        $pdf = $this->pdf_file ?? null;

        $stmt->bindParam(":qr_code", $qr);
        $stmt->bindParam(":pdf_file", $pdf);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT c.*, o.company_name FROM " . $this->table_name . " c
                  LEFT JOIN organizations o ON c.organization_id = o.id
                  ORDER BY c.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT c.*, o.company_name, o.address FROM " . $this->table_name . " c
                  LEFT JOIN organizations o ON c.organization_id = o.id
                  WHERE c.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->certificate_number = $row['certificate_number'];
            $this->organization_id = $row['organization_id'];
            $this->company_name = $row['company_name'];
            $this->address = $row['address'];
            $this->iso_standard = $row['iso_standard'];
            $this->scope = $row['scope'];
            $this->issue_date = $row['issue_date'];
            $this->expiry_date = $row['expiry_date'];
            $this->qr_code = $row['qr_code'];
            $this->pdf_file = $row['pdf_file'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    public function verify($cert_number) {
        $query = "SELECT c.*, o.company_name, o.address FROM " . $this->table_name . " c
                  LEFT JOIN organizations o ON c.organization_id = o.id
                  WHERE c.certificate_number = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);

        $cert_number = htmlspecialchars(strip_tags($cert_number));
        $stmt->bindParam(1, $cert_number);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET organization_id=:organization_id, iso_standard=:iso_standard, scope=:scope,
                    issue_date=:issue_date, expiry_date=:expiry_date, status=:status
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->organization_id=htmlspecialchars(strip_tags($this->organization_id));
        $this->iso_standard=htmlspecialchars(strip_tags($this->iso_standard));
        $this->scope=htmlspecialchars(strip_tags($this->scope));
        $this->issue_date=htmlspecialchars(strip_tags($this->issue_date));
        $this->expiry_date=htmlspecialchars(strip_tags($this->expiry_date));
        $this->status=htmlspecialchars(strip_tags($this->status));
        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":organization_id", $this->organization_id);
        $stmt->bindParam(":iso_standard", $this->iso_standard);
        $stmt->bindParam(":scope", $this->scope);
        $stmt->bindParam(":issue_date", $this->issue_date);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->status=htmlspecialchars(strip_tags($this->status));
        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function countByStatus($status = null) {
        if ($status) {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
        } else {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function generateCertificateNumber() {
        return "ISO-" . date("Y") . "-" . strtoupper(substr(uniqid(), -6));
    }
}
?>
