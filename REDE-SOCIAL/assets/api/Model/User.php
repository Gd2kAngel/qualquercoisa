<?php

class User
{
    private $conn;
    public $id;
    public $username;
    public $password;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function loadByUsername($username)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $this->id = $userData['id'];
            $this->username = $userData['username'];
            $this->password = $userData['password'];
            return true;
        } else {
            return false;
        }
    }
    public function getUsernameById($id)
    {
        $stmt = $this->conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            return $userData['username'];
        } else {
            return null;
        }
    }
}
