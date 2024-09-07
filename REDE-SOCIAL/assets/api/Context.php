<?php
class Context
{
    private $conn;

    public function __construct()
    {
        $config = json_decode(file_get_contents(__DIR__ . '/../../config/database.json'), true);

        $hostName = $config['hostName'];
        $dbUser = $config['dbUser'];
        $dbPassword = $config['dbPassword'];
        $dbName = $config['dbName'];

        $this->conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);

        if (!$this->conn) {
            die("Something went wrong" . mysqli_connect_error());
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function closeConnection()
    {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
}
