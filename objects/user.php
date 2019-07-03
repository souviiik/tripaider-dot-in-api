<?php
class User{
 
    // database connection and table name
    private $conn;
    private $table_name = "users";
 
    // object properties
    public $id;
    public $username;
    public $password;
	public $firstname;
	public $lastname;
	public $address;
	public $country;
	public $state;
	public $pincode;
	public $mobile;
    public $created;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
    // signup user
    function signup(){
		
		if($this->isAlreadyExist()){
			return false;
		}
		// query to insert record
		$query = "INSERT INTO
					" . $this->table_name . "
				SET
					username=:username, 
					password=:password, 
					firstname=:firstname, 
					lastname=:lastname,					
					status=0,
					created=:created";
		// prepare query
		$stmt = $this->conn->prepare($query);
		
		// sanitize
		$this->username=htmlspecialchars(strip_tags($this->username));
		$this->password=$this->password;
		$this->firstname=htmlspecialchars(strip_tags($this->firstname));
		$this->lastname=htmlspecialchars(strip_tags($this->lastname));
		/*$this->address=htmlspecialchars(strip_tags($this->address));
		$this->country=htmlspecialchars(strip_tags($this->country));
		$this->state=htmlspecialchars(strip_tags($this->state));
		$this->pincode=htmlspecialchars(strip_tags($this->pincode));
		$this->mobile=htmlspecialchars(strip_tags($this->mobile));*/
		$this->created=htmlspecialchars(strip_tags($this->created));
		// bind values
		$stmt->bindParam(":username", $this->username);
		$stmt->bindParam(":password", $this->password);
		$stmt->bindParam(":firstname", $this->firstname);
		$stmt->bindParam(":lastname", $this->lastname);
		/*$stmt->bindParam(":address", $this->address);
		$stmt->bindParam(":country", $this->country);
		$stmt->bindParam(":state", $this->state);
		$stmt->bindParam(":pincode", $this->pincode);
		$stmt->bindParam(":mobile", $this->mobile);*/
		$stmt->bindParam(":created", $this->created);
		// execute query
		if($stmt->execute()){
			$this->id = $this->conn->lastInsertId();			
			return true;
		}
		return false;
    
    }
    // login user
    function login(){
		$query = "SELECT
                `id`, `username`,`password`, `firstname`,`lastname`, `address`, `country`, `state`, `pincode`, `created`, `mobile`,status
            FROM
                " . $this->table_name . " 
            WHERE
                username='".htmlspecialchars($this->username)."'";
		// prepare query statement
		$stmt = $this->conn->prepare($query);
		// execute query
		$stmt->execute();
		return $stmt;
    }
	
	// login user
    function verify(){
		$query = "UPDATE              
                " . $this->table_name . " 
			SET status=1
            WHERE
                username='".htmlspecialchars($this->username)."'";
		// prepare query statement
		$stmt = $this->conn->prepare($query);
		;
		// execute query
		if($stmt->execute()){
			return true;
		} else {
			return false;
		}
		
    }
	
	function getUser(){
		$query = "SELECT
                `id`, `username`,`password`, `firstname`,`lastname`,`mobile`,`status`
            FROM
                " . $this->table_name . " 
            WHERE
                username='".htmlspecialchars($this->username)."'";
		// prepare query statement
		$stmt = $this->conn->prepare($query);
		// execute query
		$stmt->execute();
		return $stmt;
    }
	
	function getUserMD5($value,$key){
		echo $query = "SELECT
                `id`, `username`,`password`, `firstname`,`lastname`,`mobile`,`status`
            FROM
                " . $this->table_name . " 
            WHERE
                md5(CONCAT(username,'_','".$key."'))='".htmlspecialchars($value)."'";
		// prepare query statement
		$stmt = $this->conn->prepare($query);
		// execute query
		$stmt->execute();
		return $stmt;
    }
	
	function resetpassword(){
		$query = "UPDATE              
                " . $this->table_name . " 
			SET password='".$this->password."'
            WHERE
                username='".htmlspecialchars($this->username)."'";
		// prepare query statement
		$stmt = $this->conn->prepare($query);
		;
		// execute query
		if($stmt->execute()){
			return true;
		} else {
			return false;
		}
		
    }
    
    // a function to check if username already exists
    function isAlreadyExist(){
		$query = "SELECT *
        FROM
            " . $this->table_name . " 
        WHERE
            username='".htmlspecialchars($this->username)."'";
		// prepare query statement
		$stmt = $this->conn->prepare($query);
		// execute query
		$stmt->execute();
		if($stmt->rowCount() > 0){
			return true;
		}
		else{
			return false;
		}
    }
}
