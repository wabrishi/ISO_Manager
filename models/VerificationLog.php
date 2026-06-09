<?php
class VerificationLog {
    private $conn;
    private $table_name = "verification_logs";

    public $id;
    public $certificate_id;
    public $ip_address;
    public $verified_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET certificate_id=:certificate_id, ip_address=:ip_address";

        $stmt = $this->conn->prepare($query);

        $this->certificate_id=htmlspecialchars(strip_tags($this->certificate_id));
        $this->ip_address=htmlspecialchars(strip_tags($this->ip_address));

        $stmt->bindParam(":certificate_id", $this->certificate_id);
        $stmt->bindParam(":ip_address", $this->ip_address);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT v.*, c.certificate_number FROM " . $this->table_name . " v
                  LEFT JOIN certificates c ON v.certificate_id = c.id
                  ORDER BY v.id DESC LIMIT 50";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
