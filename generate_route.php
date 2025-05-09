<?php
header('Content-Type: application/json');
require 'db_connect.php';

// 1. 接收前端数据
$data = json_decode(file_get_contents('php://input'), true);
$poiIds = $data['poi_list']; // [1,15,24,31]

// 2. 生成动态表名
$tableName = implode('_', $poiIds);

// 3. 创建动态路径表
try {
    $pdo->exec("
        CREATE TABLE \"{$tableName}\" (
            path_id INT,
            path_seq INT,
            geom GEOMETRY(LineString, 4326)
        )
    ");
    
    // 4. 插入用户会话记录
    $stmt = $pdo->prepare("
        INSERT INTO user_sessions 
        (session_id, likes, dislikes, poi_list, route_table_name)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        uniqid(), // 生成唯一session_id
        json_encode($data['likes']),
        json_encode($data['dislikes']),
        $data['poi_list'],
        $tableName
    ]);
    
    echo json_encode(['table_name' => $tableName]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>