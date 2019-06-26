<?php
Class Database{
	/* Database connection start */
	var $host = "localhost";
	var $username = "tripaide_banesob";
	var $password = "Na384908";
	var $db_name = "tripaide_recordset";
	var $conn;
	public function getConnection(){
 
        $this->conn = null;
 
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
 
        return $this->conn;
    }
}

?>