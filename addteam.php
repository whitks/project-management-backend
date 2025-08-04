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
if (!$details || !isset($details['team'])){
    return json_encode(['status' => 'Some Error occured']);
}
if (!isset($_SESSION['email'])){
    echo json_encode(['status' => 'Authorization failed']);
    exit;
}
$stmt = $conn->prepare("INSERT INTO `teams`(`id`, `team_lead`, `team_member_1`, `team_member_2`, `team_member_3`, `project_id`, `mentor_id`, `pending_approvals_id`) VALUES ('[value-1]','[value-2]','[value-3]','[value-4]','[value-5]','[value-6]','[value-7]','[value-8]')");
$stmt->bind_param("s", $_SESSION['section']);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
