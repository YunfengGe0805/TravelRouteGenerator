<?php
header('Content-Type: application/json');
require 'db_connect.php';

$sessionId = $_GET['session_id'];

try {
    // 1. 查询会话获取表名
    $session = $pdo->query("
        SELECT route_table_name 
        FROM user_sessions 
        WHERE session_id = '$sessionId'
    ")->fetch();
    
    $tableName = $session['route_table_name'];
    
    // 2. 查询动态表数据
    $route = $pdo->query("
        SELECT ST_AsGeoJSON(ST_LineMerge(ST_Collect(geom))) AS geojson 
        FROM \"{$tableName}\"
    ")->fetch();
    
    echo json_encode(['path' => json_decode($route['geojson'])]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>