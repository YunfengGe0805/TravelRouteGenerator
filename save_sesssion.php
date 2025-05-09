<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$sessionId = bin2hex(random_bytes(16)); // 生成唯一会话ID

try {
    $stmt = $pdo->prepare("
        INSERT INTO session_data 
        (session_id, username, likes, dislikes, reserve, environment, poi_list)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $sessionId,
        $data['username'],
        json_encode($data['likes']),
        json_encode($data['dislikes']),
        $data['reserve'],
        $data['environment'],
        json_encode($data['poi_list']) // 假设前端传递预选POI列表
    ]);
    
    echo json_encode(['session_id' => $sessionId]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>