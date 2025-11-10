<?php
class Auth {
    private $conn;
    private $headers;

    public function __construct($db) {
        $this->conn = $db;
        $this->headers = getallheaders();
    }

    public function checkKey() {
        if (!isset($this->headers['X-API-Key'])) {
            http_response_code(401);
            echo json_encode(["error" => "Missing API key"]);
            exit;
        }

        $key = $this->headers['X-API-Key'];
        $query = "SELECT api_key, is_active FROM api_keys WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($keys as $row) {
            if (password_verify($key, $row['api_key'])) {
                return true;
            }
        }

        http_response_code(401);
        echo json_encode(["error" => "Invalid API key"]);
        exit;
    }
}
