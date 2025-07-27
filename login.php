
<?php 
require_once 'includes/db.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

$str = file_get_contents('php://input');
$details = json_decode($str, true);
$access_token = htmlspecialchars($details["access_token"]); 
header('Content-Type: application/json');
if ($details["googleLogin"] == true){
    $userInfo = file_get_contents("https://www.googleapis.com/oauth2/v3/userinfo?access_token=" . $access_token);
    $user = json_decode($userInfo, true);
    if (array_key_exists('email', $user) && str_ends_with($user['email'], '@skit.ac.in')){
        echo json_encode(['status' => 'success']);
        exit;
    }
    else{
        echo json_encode(['status' => 'Authorization failed']);
        exit;
    }
    
}
else{
    $email = htmlspecialchars($details["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "Invalid email format"]);
        exit;
    }
    if (!str_ends_with($email, '@skit.ac.in')){
        echo json_encode(["status" => "Invalid email format"]);
        exit;
    }
    else{
        $stmt = $conn->prepare("SELECT * FROM USERS WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        echo json_encode(['status' => "success", 'result'=>$result]);
    }
}
?>