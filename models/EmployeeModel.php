<?php
class EmployeeModel {
    private $conn;
    private $table = 'employees';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        try {
            $stmt = $this->conn->query("SELECT * FROM " . $this->table);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch employees");
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch employee by ID");
        }
    }

    public function create($data) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO " . $this->table . " (first_name, last_name, position, salary) VALUES (?, ?, ?, ?)"
            );
            return $stmt->execute([$data['first_name'], $data['last_name'], $data['position'], $data['salary']]);
        } catch (PDOException $e) {
            throw new Exception("Failed to create employee");
        }
    }

    public function update($id, $data) {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE " . $this->table . " SET first_name=?, last_name=?, position=?, salary=? WHERE id=?"
            );
            return $stmt->execute([$data['first_name'], $data['last_name'], $data['position'], $data['salary'], $id]);
        } catch (PDOException $e) {
            throw new Exception("Failed to update employee");
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id=?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Failed to delete employee");
        }
    }
}
