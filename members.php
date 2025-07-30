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

if (!isset($_SESSION['email'])){
    echo json_encode(['status' => 'Authorization failed']);
    exit;
}

$stmt = $conn->prepare("SELECT firstname, lastname, email FROM USERS WHERE section = ? AND team_id IS NULL");
$stmt->bind_param("s", $_SESSION['section']);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
if(!$result || !$rows){
    echo json_encode(['status'=> "Failed, Some error occured."]);
}
echo json_encode(['status'=> "success", "rows" => $rows]);
exit;