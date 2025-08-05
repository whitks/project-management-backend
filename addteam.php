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
$team = $details['team'];
$team_lead = $team['team_lead'] ?? null;
$member1 = $team['team_member_1'] ?? null;
$member2 = $team['team_member_2'] ?? null;
$member3 = $team['team_member_3'] ?? null;
$project_id = $team['project_id'] ?? null;
$mentor_id = $team['mentor_id'] ?? null;

// Validate required fields
if (!$team_lead || !$member1 || !$project_id || !$mentor_id) {
    echo json_encode(['status' => 'Missing required fields']);
    exit;
}

// Prepare and execute insert (ID is auto-incremented, so omit it)
$stmt = $conn->prepare("INSERT INTO `teams` (
    `team_lead`, `team_member_1`, `team_member_2`, `team_member_3`, `project_id`, `mentor_id`
) VALUES (?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "iiiiii",
    $team_lead,
    $member1,
    $member2,
    $member3,
    $project_id,
    $mentor_id
);
if ($stmt->execute()) {
    $team_id = $stmt->insert_id;
    $members = [$member1, $member2, $member3];
    $insertPending = $conn->prepare("INSERT INTO `pending_approvals`(`team_member_1`, `team_member_2`, `team_member_3`, `mentor_id`) VALUES (?,?,?,?)");
    $insertPending->bind_param("iiii", $member1, $member2, $member3, $mentor_id);
    $insertPending->execute();
    $insertPendingMembs = $conn->prepare("INSERT INTO `pending_members_approvals`(`user_id`, `team_id`, `status`) VALUES (?,?,?)");
    foreach ($members as $member) {
        $insertPendingMembs->bind_param("iii", $member, $team_id, 1);
        $insertPendingMembs->execute();
    }
    // $insertStmt = $conn->prepare("UPDATE users SET team_id = ?, team_lead = ? WHERE id = ?");

    // foreach ($members as $member) {
    //     if (!empty($member)) {
    //         $is_lead = ($member === $team_lead) ? 1 : 0;
    //         $insertStmt->bind_param("iis", $team_id, $is_lead, $member);
    //         $insertStmt->execute();
    //     }
    // }
    echo json_encode(['status' => 'success', 'team_id' => $team_id]);
} else {
    echo json_encode(['status' => 'error', 'error' => $stmt->error]);
    exit;
}
