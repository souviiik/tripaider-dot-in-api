<?php
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
  exit; // OPTIONS request wants only the policy, we can stop here
}

ini_set('display_errors', 0);

// include database and object files
include_once '../config/database.php';
include_once '../objects/project.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// initialize object
$project = new Project($db);

$request_method = $_SERVER["REQUEST_METHOD"];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

switch ($request_method) {
  case 'POST':
    // get posted data
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($uri[3]) && $uri[3] === 'post-project') {
      if (!empty($data->username) && !empty($data->password) && !empty($data->firstname) && !empty($data->lastname)) {
        $project->projectName = $data->projectName;
        $project->startCity = md5($data->startCity);
        $project->destination = $data->destination;
        $project->startDate = $data->startDate;
        $project->numDays = $data->numDays;
        $project->numPeople = $data->numPeople;
        $project->desc = $data->desc;

        if ($project->saveproject()) {
        } else {
          http_response_code(400);
          echo json_encode(array(
            "message" => "Error saving project", "data" => "", "error" => true, "code" => "103", "status" => 400
          ));
        }
      }
    } else {
      http_response_code(400);
      echo json_encode(array("message" => "Invalid data, all field are reqiuired", "data" => "", "error" => true, "code" => "104", "status" => 400));
    }
    break;
  default:
    // Invalid Request Method
    //header("HTTP/1.0 405 Method Not Allowed");
    http_response_code(404);
    echo "Not Found";
    var_dump(encrypt_decrypt("admin", "encrypt"));
    break;
}
