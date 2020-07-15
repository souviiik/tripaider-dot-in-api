<?php
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
	exit; // OPTIONS request wants only the policy, we can stop here
}

ini_set('display_errors', 0);

// include database and object files
include_once '../config/database.php';
include_once '../objects/projects.php';

// include_once '../libs/php-jwt/src/BeforeValidException.php';
// include_once '../libs/php-jwt/src/ExpiredException.php';
// include_once '../libs/php-jwt/src/SignatureInvalidException.php';
// include_once '../libs/php-jwt/src/JWT.php';
// use \Firebase\JWT\JWT;

// require_once '../libs/PHPMailer/src/Exception.php';
// require_once '../libs/PHPMailer/src/PHPMailer.php';
// require_once '../libs/PHPMailer/src/SMTP.php';
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

$key = "1234789";
$iss = "http://tripaider.in";
$dvl = "http://dvl.tripaider.in";
$aud = "http://tripaider.in";
$iat = 1356999524;
$nbf = 1357000000;
 
// instantiate database and product object
$database = new Database();
$db = $database->getConnection();
 
// initialize object
$user = new User($db);

$request_method=$_SERVER["REQUEST_METHOD"];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

switch($request_method) {
		case 'POST':
			// get posted data
			$data = json_decode(file_get_contents("php://input"));			
			if(!empty($uri[3]) && $uri[3] === 'login') {

      } else if(!empty($uri[3]) && $uri[3] === 'signup') {

      } else {
			}
			break;
			
		case 'GET':
			if(!empty($uri[3]) && $uri[3] === 'verify') {

      } else if(!empty($uri[3]) && $uri[3] === 'forgetpassword') {

      } else{
				http_response_code(404);
				echo "Not Found";
			}
		
		case 'PUT':
			if(!empty($uri[3]) && $uri[3] === 'resetpassword') {

      }else if(!empty($uri[3]) && $uri[3] === 'changepassword') {

      } else{
				http_response_code(404);
				echo "Not Found";
			}
			break;
		default:
			// Invalid Request Method
			//header("HTTP/1.0 405 Method Not Allowed");
			http_response_code(404);
			echo "Not Found";
			var_dump(encrypt_decrypt("admin","encrypt"));
			break;
}
