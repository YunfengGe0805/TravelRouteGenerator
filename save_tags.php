<?php
header('Content-Type: application/json');
require 'db_connect.php'; // 数据库连接文件

$data = json_decode(file_get_contents('php://input'), true);

try {
    // 保存用户标签到数据库（示例表名：user_choices）
    $stmt = $pdo->prepare("
        INSERT INTO user_choices (username, likes, dislikes) 
        VALUES (:username, :likes, :dislikes)
    ");
    $stmt->execute([
        ':username' => $data['username'],
        ':likes' => json_encode($data['likes']),
        ':dislikes' => json_encode($data['dislikes'])
    ]);
    
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>