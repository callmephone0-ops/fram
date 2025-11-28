<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['username'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
include './db.php';

$username = $_SESSION['username'];
$title = trim($_POST['title'] ?? '');
$start = $_POST['start'] ?? null;
$end   = $_POST['end']   ?? null;
$bg    = $_POST['backgroundColor'] ?? '#546BFA';
$bd    = $_POST['borderColor']     ?? '#546BFA';

if ($title==='' || !$start) { echo json_encode(['status'=>'error','message'=>'ข้อมูลไม่ครบ']); exit; }

$sql = "INSERT INTO calendar_events (username, title, start_event, end_event, backgroundColor, borderColor, is_global)
        VALUES (?, ?, ?, ?, ?, ?, 0)";
$stmt = $conn->prepare($sql);
if(!$stmt){ echo json_encode(['status'=>'error','message'=>$conn->error]); exit; }
$stmt->bind_param("ssssss", $username, $title, $start, $end, $bg, $bd);
$ok = $stmt->execute();

echo json_encode($ok ? ['status'=>'success','id'=>$stmt->insert_id]
                     : ['status'=>'error','message'=>'DB insert failed']);
$stmt->close(); $conn->close();
