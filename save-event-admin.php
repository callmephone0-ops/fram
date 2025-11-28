<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['username'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
// ถ้ามีระบบ role: if($_SESSION['role']!=='admin'){ echo json_encode(['status'=>'error','message'=>'Forbidden']); exit; }

include './db.php';

$title = trim($_POST['title'] ?? '');
$start = $_POST['start'] ?? null;
$end   = $_POST['end']   ?? null;
$bg    = $_POST['backgroundColor'] ?? '#6f42c1';
$bd    = $_POST['borderColor']     ?? '#6f42c1';
$aud   = $_POST['audience'] ?? 'global';          // 'global' หรือ 'user'
$target_username = trim($_POST['target_username'] ?? '');

if ($title==='' || !$start) { echo json_encode(['status'=>'error','message'=>'ข้อมูลไม่ครบ']); exit; }

$is_global = ($aud === 'global') ? 1 : 0;
$username  = ($is_global ? null : $target_username);
if(!$is_global && $username===''){ echo json_encode(['status'=>'error','message'=>'กรอก username ผู้ใช้เป้าหมาย']); exit; }

$sql = "INSERT INTO calendar_events (username, title, start_event, end_event, backgroundColor, borderColor, is_global)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if(!$stmt){ echo json_encode(['status'=>'error','message'=>'SQL Error: '.$conn->error]); exit; }
$stmt->bind_param("ssssssi", $username, $title, $start, $end, $bg, $bd, $is_global);
$ok = $stmt->execute();

echo json_encode($ok ? ['status'=>'success','id'=>$stmt->insert_id]
                     : ['status'=>'error','message'=>'DB insert failed']);
$stmt->close(); $conn->close();
