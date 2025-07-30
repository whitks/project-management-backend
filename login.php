<?php 
require_once 'includes/db.php';
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
session_start();
$str = file_get_contents('php://input');
$details = json_decode($str, true);


if (!isset($details) || !isset($details["googleLogin"])){
    echo json_encode(['status' => 'Authorization failed']);
    exit;
}
if (isset($details["googleLogin"]) && $details["googleLogin"] == true && isset($details["access_token"])){
    $access_token = htmlspecialchars($details["access_token"]); 
    $userInfo = file_get_contents("https://www.googleapis.com/oauth2/v3/userinfo?access_token=" . $access_token);
    $user = json_decode($userInfo, true);
    if (array_key_exists('email', $user) && str_ends_with($user['email'], '@skit.ac.in')){
        $stmt = $conn->prepare("SELECT * FROM USERS WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $user['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        if (!isset($data) || !isset($data['id'])){
            echo json_encode(['status' => 'Authorization failed']);
            exit;
        }
        $_SESSION["email"] = $user['email'];
        $_SESSION["id"] = $data['id'];
        echo json_encode(['status' => 'success']);
        exit;
    }
    else{
        echo json_encode(['status' => 'Authorization failed', "error"=> "2"]);
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
        $stmt = $conn->prepare("SELECT * FROM USERS WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        if (!isset($data) | !isset($data['id'])){
            echo json_encode(['status' => 'Authorization failed']);
            exit;
        }
        $_SESSION["email"] = $email;
        $_SESSION["id"] = $data['id'];
        echo json_encode(['status' => "success"]);
        exit;
    }
}
?>