<?php
class Project
{

  // database connection and table name
  private $conn;
  private $table_name = "projects";

  // object properties
  public $id;
  public $projectName;
  public $startCity;
  public $destination;
  public $startDate;
  public $numDays;
  public $numPeople;
  public $desc;
  public $created;

  // constructor with $db as database connection
  public function __construct($db)
  {
    $this->conn = $db;
  }

  // INSERT, UPDATE, DELETE, GET_ALL, GET_ONE

  // signup user
  function saveProject()
  {
    // query to insert record
    $query = "INSERT INTO
					" . $this->table_name . "
				SET
					projectName=:projectName, 
					startCity=:startCity, 
					destination=:destination, 
					startDate=:startDate,					
					numDays=:numDays,
          numPeople=:numPeople,
          desc=:desc,
					created=:created";
    // prepare query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->projectName = htmlspecialchars(strip_tags($this->projectName));
    $this->startCity = htmlspecialchars(strip_tags($this->startCity));
    $this->destination = htmlspecialchars(strip_tags($this->destination));
    $this->startDate = htmlspecialchars(strip_tags($this->startDate));
    $this->numDays = htmlspecialchars(strip_tags($this->numDays));
    $this->numPeople = htmlspecialchars(strip_tags($this->numPeople));
    $this->desc = htmlspecialchars(strip_tags($this->desc));
    $this->created = htmlspecialchars(strip_tags($this->created));
    // bind values
    $stmt->bindParam(":projectName", $this->projectName);
    $stmt->bindParam(":startCity", $this->startCity);
    $stmt->bindParam(":destination", $this->destination);
    $stmt->bindParam(":startDate", $this->startDate);
    $stmt->bindParam(":numDays", $this->numDays);
    $stmt->bindParam(":numPeople", $this->numPeople);
    $stmt->bindParam(":desc", $this->desc);
    $stmt->bindParam(":created", $this->created);
    // execute query
    if ($stmt->execute()) {
      $this->id = $this->conn->lastInsertId();
      return true;
    }
    return false;
  }
}
