<?php
class Organization {
    private $conn;
    private $table_name = "organizations";

    public $id;
    public $company_name;
    public $address;
    public $contact_person;
    public $email;
    public $phone;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->company_name = $row['company_name'];
            $this->address = $row['address'];
            $this->contact_person = $row['contact_person'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET company_name=:company_name, address=:address, contact_person=:contact_person, email=:email, phone=:phone, status=:status";

        $stmt = $this->conn->prepare($query);

        $this->company_name=htmlspecialchars(strip_tags($this->company_name));
        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->contact_person=htmlspecialchars(strip_tags($this->contact_person));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        $this->status=htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":contact_person", $this->contact_person);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET company_name=:company_name, address=:address, contact_person=:contact_person, email=:email, phone=:phone, status=:status
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->company_name=htmlspecialchars(strip_tags($this->company_name));
        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->contact_person=htmlspecialchars(strip_tags($this->contact_person));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        $this->status=htmlspecialchars(strip_tags($this->status));
        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":contact_person", $this->contact_person);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
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
}
?>
