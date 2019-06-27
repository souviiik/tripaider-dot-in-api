<?php
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
	exit; // OPTIONS request wants only the policy, we can stop here
}
ini_set('display_errors', 0);


// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';

include_once '../libs/php-jwt/src/BeforeValidException.php';
include_once '../libs/php-jwt/src/ExpiredException.php';
include_once '../libs/php-jwt/src/SignatureInvalidException.php';
include_once '../libs/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;

require_once '../libs/PHPMailer/src/Exception.php';
require_once '../libs/PHPMailer/src/PHPMailer.php';
require_once '../libs/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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


// set response code - 201 created
//http_response_code(201);
//$data = json_decode(file_get_contents("php://input"));
//echo json_encode(array("message" => $data,"url" => $uri,"request_method"=>$request_method));
//var_dump($_SERVER);


switch($request_method) {
		case 'POST':
			// get posted data
			$data = json_decode(file_get_contents("php://input"));			
			if(!empty($uri[3]) && $uri[3] === 'login') {
				if(
					!empty($data->username) &&
					!empty($data->password)
				){		
					$user->username = $data->username;
					$user->password = md5($data->password);
					
					$stmt = $user->login();					
					$num = $stmt->rowCount();
					if($num === 0){
						http_response_code(400);
						echo json_encode(array(
							"message" => "Invalid Username or Password","data"=> "","error"=> true,"code"=>"100","status"=> 400
						));
						
					} else {
						
						$userdata = $stmt->fetch(PDO::FETCH_ASSOC);						
						if( $userdata['status'] != 1 ) {
							http_response_code(400);
							echo json_encode(array("message" => "Your account is not verified. Please verify your account","data"=> "","error"=> true,"code"=>"101","status"=> 400));
							
						}else if( md5($data->password) === $userdata['password'] ) {
							
							$token = array(
							   "iss" => $iss,
							   "aud" => $aud,
							   "iat" => $iat,
							   "nbf" => $nbf,
							   "data" => array(
								   "id" => $userdata['id'],
								   "firstname" =>$userdata['firstname'],
								   "lastname" => $userdata['lastname'],
								   "username" => $userdata['username']
							   )
							);					 
												 
							// generate jwt
							$jwt = JWT::encode($token, $key);
							
							//$jwt = JWT::encode($token, $key);
							//$decoded = JWT::decode($jwt, $key, array('HS256'));
							
							echo json_encode(array(
								"data" => array(
								   "id" => $userdata['id'],
								   "firstname" =>$userdata['firstname'],
								   "lastname" => $userdata['lastname'],
								   "username" => $userdata['username']
							   ),
								"token" =>$jwt,
								"error" => false,
								"status"=>200
							));
						} else {
							http_response_code(400);
							echo json_encode(array("message" => "Invalid Username or Password","data"=> "","error"=> true,"code"=>"100","status"=> 400));
						}
					}
				} else {
					http_response_code(400);
					echo json_encode(array("message" => "Invalid Username or Password","data"=> "","error"=> true,"code"=>"100","status"=> 400));
				}
				
			} else if(!empty($uri[3]) && $uri[3] === 'signup') {
				if(
					!empty($data->username) &&
					!empty($data->password) &&
					!empty($data->firstname) &&
					!empty($data->lastname)					
				){		
					$user->username = $data->username;
					$user->password = md5($data->password);
					$user->firstname = $data->firstname;
					$user->lastname = $data->lastname;
					/*$user->address = $data->address;
					$user->country = $data->country;
					$user->state = $data->state;
					$user->pincode = $data->pincode;
					$user->mobile = $data->mobile;*/
					
					if( $user->isAlreadyExist() ){
						http_response_code(400);
						echo json_encode(array(
							"message" => "Username Already exist","data"=> "","error"=> true,"code"=>"102","status"=> 400
						));
					} else {
						if($user->signup()){

							http_response_code(201);
							echo json_encode(array(
								"message" => "Record Saved successfully","data"=> "","error"=> false,"code"=>"105","status"=> 201
							));
							
							// Instantiation and passing `true` enables exceptions
							$mail = new PHPMailer(true); //From email address and name 
							$mail->From = "admin@tripaider.in"; 
							$mail->FromName = "Tripaider"; //To address and name 
							$mail->addAddress($data->username, $data->firstname." ".$data->lastname);//Recipient name is optional							
							$mail->addReplyTo("admin@tripaider.in", "Tripaider"); //CC and BCC 							 
							$mail->isHTML(true); 
							$mail->Subject = "Welcome to tripaider.in!"; 
							$mail_body = "
								<p>Hi ".$data->firstname.",</p>
								<p>You have successfully created your tripaider.in account with the following email address: ".$data->username.". In order to access all areas of the site you must activate your account by clicking below button:</p>
								<a style='background: #007bff; color: #fff; text-decoration: none; padding: 10px 25px; border-radius: 5px; margin: 10px auto;' href=\"".$dvl."/verification/".$data->username."\">ACTIVATE YOUR ACCOUNT</a>
								<p>If you have any queries or comments just email support@tripaider.in. We would love to hear from you!</p>

								<p>Cheers,<br />Team tripaider.in</p>
							";	

							$mail->Body = $mail_body;
							//$mail->send();				
							
							
							
							  //Server settings
							/*$mail->SMTPDebug = 2;                                       // Enable verbose debug output
							$mail->isSMTP();                                            // Set mailer to use SMTP
							$mail->Host       = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
							$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
							$mail->Username   = 'user@example.com';                     // SMTP username
							$mail->Password   = 'secret';                               // SMTP password
							$mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
							$mail->Port       = 587;                                    // TCP port to connect to*/
	
							try {
								$mail->send();
							}catch(\Exception $e){
								
							};
							
						} else{
							http_response_code(400);
							echo json_encode(array(
								"message" => "Error saving record","data"=> "","error"=> true,"code"=>"103","status"=> 400
							));
						}
					}					
					
				} else {
					http_response_code(400);
					echo json_encode(array("message" => "Invalid data, all field are reqiuired","data"=> "","error"=> true,"code"=>"104","status"=> 400));
				}
				
			} else {
				http_response_code(404);
				echo "Not Found1";
			}
			break;
			
		case 'GET':
			if(!empty($uri[3]) && $uri[3] === 'verify') {
				$username = htmlspecialchars(strip_tags($uri[4]));
				$user->username = $username;
				//http_response_code(200);
				if($user->verify() ){
					http_response_code(200);
					echo json_encode(array(
						"message" => "Account Verified successfully","data"=> "","error"=> false,"code"=>"110","status"=> 200
					));
					
					$stmt = $user->getUser();
					$userdata = $stmt->fetch(PDO::FETCH_ASSOC);		
					
					
					$mail = new PHPMailer(true); //From email address and name 
					$mail->From = "admin@tripaider.in"; 
					$mail->FromName = "Tripaider"; //To address and name 
					$mail->addAddress($userdata['username'], $userdata['firstname']." ".$userdata['lastname']);//Recipient name is optional							
					$mail->addReplyTo("admin@tripaider.in", "Tripaider"); //CC and BCC 							 
					$mail->isHTML(true); 
					$mail->Subject = "Account Verified Successfully"; 
					$mail_body = "
						<p>Hi ".$userdata['firstname'].",</p><br/>
						<p>Congratulations! Your tripaider.in account is verified and live.<p>
						
						<p>Team tripaider.in</p>
					";	

					$mail->Body = $mail_body;
					try {
						$mail->send();
					}catch(\Exception $e){
						
					};
					
					
				} else {
					http_response_code(400);
					echo json_encode(array("message" => "Verification Error","data"=> "","error"=> true,"code"=>"111","status"=> 400));
				}
				
				
			} else if(!empty($uri[3]) && $uri[3] === 'forgetpassword') {
				$username = htmlspecialchars(strip_tags($uri[4]));
				$user->username = $username;				
				
				$stmt = $user->getUser();					
				$num = $stmt->rowCount();
				
				if($num === 0){
					http_response_code(400);
					echo json_encode(array(
						"message" => "Invalid Username","data"=> "","error"=> true,"code"=>"120","status"=> 400
					));
						
				} else {
					
					http_response_code(200);
					echo json_encode(array(
						"message" => "Mail send successfully","data"=> "","error"=> false,"code"=>"121","status"=> 200
					));					
					
					$userdata = $stmt->fetch(PDO::FETCH_ASSOC);		
					
					
					$mail = new PHPMailer(true); //From email address and name 
					$mail->From = "admin@tripaider.in"; 
					$mail->FromName = "Tripaider"; //To address and name 
					$mail->addAddress($userdata['username'], $userdata['firstname']." ".$userdata['lastname']);//Recipient name is optional							
					$mail->addReplyTo("admin@tripaider.in", "Tripaider"); //CC and BCC 							 
					$mail->isHTML(true); 
					$mail->Subject = "Reset Password"; 
					$mail_body = "
						<p>Hi ".$userdata['firstname'].",</p><br/>
						
						<p>Click <a href=\"".$iss."/resetpassword/".$data->username."\">here</a> tp reset your password</p>

												
						<p>Tripaider.in team</p>
					";	

					$mail->Body = $mail_body;
					try {
						$mail->send();
					}catch(\Exception $e){
						
					};
					
				}
				
			
			} else{
				http_response_code(404);
				echo "Not Found";
			}
			break;			
		
		case 'PUT':
			if(!empty($uri[3]) && $uri[3] === 'resetpassword') {
				
				$data = json_decode(file_get_contents("php://input"));	
				$user->username = $data->username;
				$user->password = md5($data->password);
				
				$stmt = $user->getUser();					
				$num = $stmt->rowCount();
				
				if($num === 0){
					http_response_code(400);
					echo json_encode(array(
						"message" => "Invalid Username","data"=> "","error"=> true,"code"=>"120","status"=> 400
					));
						
				} else {
					
					if($user->resetpassword() ){
						http_response_code(200);
						echo json_encode(array(
							"message" => "Password changed successfully","data"=> "","error"=> false,"code"=>"131","status"=> 200
						));
					} else {
						http_response_code(400);
						echo json_encode(array(
							"message" => "Error changing passworde","data"=> "","error"=> true,"code"=>"122","status"=> 400
						));
							
					}
					
				}			
				
				
			}else if(!empty($uri[3]) && $uri[3] === 'changepassword') {
				
				$header = apache_request_headers();
				$jwt = $header['Authorization'] ? $header['Authorization'] : $header['authorization'];		
				// if jwt is not empty
				if($jwt){
					try {
						// decode jwt
						$decoded = JWT::decode($jwt, $key, array('HS256'));	
				 
					}// if decode fails, it means jwt is invalid
					catch (\Exception $e) { // Also tried JwtException				 
						// set response code
						http_response_code(401);					 
						// tell the user access denied  & show error message
						echo json_encode(array(
							"message" => "Access denied.",
							"errormsg" =>$e->getMessage(),
							"error" => true,
							"code"=>"150",
							"status"=> 401
						));
					}
				}// show error message if jwt is empty
				else{
					// set response code
					http_response_code(401);				 
					// tell the user access denied
					echo json_encode(array("message" => "Access denied.","data"=> "","error"=> true,"code"=>"150","status"=> 401));
				}
				
												
				$data = json_decode(file_get_contents("php://input"));	
				$user->username = $decoded->data->username;	
				$old_password = md5($data->old_password);
				$user->password = md5($data->new_password);
				
				$stmt = $user->getUser();					
				$num = $stmt->rowCount();

				
				if($num === 0){
					http_response_code(401);
					echo json_encode(array(
						"message" => "Invalid Authorization token","data"=> "","error"=> true,"code"=>"151","status"=> 401
					));
						
				} else {
					$userdata = $stmt->fetch(PDO::FETCH_ASSOC);	
					
					if( md5($data->old_password) != $userdata['password'] ) {
						http_response_code(401);
						echo json_encode(array(
							"message" => "Invalid Old Password","data"=> "","error"=> true,"code"=>"152","status"=> 401
						));
					}
					else if($user->resetpassword() ){
						http_response_code(200);
						echo json_encode(array(
							"message" => "Password changed successfully","data"=> "","error"=> false,"code"=>"131","status"=> 200
						));
					} else {
						http_response_code(400);
						echo json_encode(array(
							"message" => "Error changing passworde","data"=> "","error"=> true,"code"=>"153","status"=> 400
						));
							
					}
					
				}		
				
				
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
			break;
}
?>
