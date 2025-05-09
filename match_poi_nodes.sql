CREATE TABLE poi_nodes AS
SELECT 
  poi.id AS poi_id,
  nearest_node.id AS node_id,
  nearest_node.the_geom AS node_geom
FROM 
  poi
CROSS JOIN LATERAL (
  SELECT 
    v.id, 
    v.the_geom,
    ST_Distance(poi.geom, v.the_geom) AS distance
  FROM 
    waysvertices_hki v
  WHERE 
    ST_DWithin(poi.geom, v.the_geom, 0.01)  -- 调整搜索半径
  ORDER BY 
    distance ASC
  LIMIT 1
) AS nearest_node;  -- 为子查询结果命名别名