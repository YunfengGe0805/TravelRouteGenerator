
// 初始化地图（香港中心）
// let map = L.map('map').setView([22.3193, 114.1694], 13);
// let currentPathLayer = null;
// let currentPoiLayer = null;
// let lastTags = { like: [], dislike: [] };
// L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

let layerData = null; 
// 全局图层引用
let currentPathLayer = null;
let currentPoiLayer = null;

// document.addEventListener('DOMContentLoaded', () => {
//     window.map = L.map('map').setView([22.26646, 114.19428], 13); 
    

//     // L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
//     // }).addTo(window.map);

//     // L.tileLayer.wms('http://localhost:8080/geoserver/travel/wms', {
//     //     layers: 'travel:pois',
//     //     format: 'image/png',
//     //     transparent: true,
//     //     version: '1.1.1'
//     // }).addTo(map);

// });

document.addEventListener('DOMContentLoaded', () => {
    // 初始化地图（根据截图中心点调整）
    window.map = L.map('map').setView([22.25646, 114.19428], 13); // 精确匹配截图中的九龙区域
    
    // 基础底图（保持截图中的道路样式）
    const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // POI图层（根据截图中的油尖旺区地标定制）
    const poiLayer = L.tileLayer.wms('http://localhost:8080/geoserver/travel/wms', {
        layers: 'travel:pois',
        format: 'image/png',
        transparent: true,
        version: '1.1.1',
    });

    // 图层控制系统（匹配截图右上角位置）
    const layerControl = L.control.layers(

        { 
            "poi": poiLayer // 根据截图中的TSing Yi等标注命名
        }, 
        {
            position: 'bottomright', // 精确匹配截图中的控件位置
            collapsed: false // 保持展开状态
        }
    ).addTo(map);

    // 添加截图中的比例尺控件
    L.control.scale({
        position: 'bottomright', // 匹配截图左下角位置
        metric: true,
        imperial: false
    }).addTo(map);

});

// 加载用户历史
async function loadUserHistory() {
    try {
        const username = document.getElementById('name').value;
        if (!username) throw new Error("Please input username");

        // Clear old layers
        if (window.currentPathLayer) {
            map.removeLayer(window.currentPathLayer);
            window.currentPathLayer = null;
        }
        if (window.currentPoiLayer) {
            map.removeLayer(window.currentPoiLayer);
            window.currentPoiLayer = null;
        }

        // Send request
        const response = await fetch(`http://localhost/api/getUserLayer.php?name=${encodeURIComponent(username)}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const data = await response.json();
        if (!data.success) throw new Error(data.error);

        // Add new layers
        const pathLayerName = `travel:${data.pathLayer}`;
        const poiLayerName = `travel:${data.poiLayer}`;

        window.currentPathLayer = L.tileLayer.wms('http://localhost:8080/geoserver/travel/wms', {
            layers: pathLayerName,
            format: 'image/png',
            transparent: true,
            version: '1.3.0',
            t: Date.now()
        }).addTo(map);

        window.currentPoiLayer = L.tileLayer.wms('http://localhost:8080/geoserver/travel/wms', {
            layers: poiLayerName,
            format: 'image/png',
            transparent: true,
            version: '1.3.0',
            t: Date.now()
        }).addTo(map);

        // Force map redraw
        map.invalidateSize();

    } catch (error) {
        console.error('Error:', error);
        alert(`Failed to load history: ${error.message}`);
    }
}
// 修改submitTags函数（确保清除旧图层引用）
async function submitTags() {
    try {
        // 强制清除旧图层
        if(window.currentPathLayer) {
            map.removeLayer(window.currentPathLayer);
            window.currentPathLayer = null;
        }
        if(window.currentPoiLayer) {
            map.removeLayer(window.currentPoiLayer);
            window.currentPoiLayer = null;
        }

        // 收集最新标签（动态获取）
        // const tags = {
        //     like: Array.from(document.querySelectorAll('.tag-select[value="like"]'))
        //              .map(el => el.dataset.value),
        //     dislike: Array.from(document.querySelectorAll('.tag-select[value="dislike"]'))
        //                 .map(el => el.dataset.value)
        // };

        const tagSelects = document.querySelectorAll('.tag-select');
        const likes = [], dislikes = [];
        tagSelects.forEach(select => {
            const value = select.value;
            const tagValue = select.dataset.value; // 获取标签名称
            if (value === 'like') likes.push(tagValue);
            else if (value === 'dislike') dislikes.push(tagValue);
        });
    //const tags = { like: likes, dislike: dislikes };


        // 发送请求
        const response = await fetch('http://localhost/api/getPathByTags.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            //body: JSON.stringify(tags)
            body: JSON.stringify({
                like: likes,
                dislike: dislikes
            })
        });
        
        // 解析前强制检查状态码
        if(!response.ok) throw new Error(`HTTP错误! 状态码: ${response.status}`);
        
        const data = await response.json();
        if(!data.success) throw new Error(data.error);

        // 动态生成图层名称
        const pathLayerName = `travel:${data.pathLayer}`;
        const poiLayerName = `travel:${data.poiLayer}`;

        // 创建新图层（同步立即加载）
        // 在添加新图层前加入时间戳

        window.currentPathLayer = L.tileLayer.wms('http://localhost:8080/geoserver/travel/wms', {
            layers: pathLayerName,
            format: 'image/png',
            transparent: true,
            version: '1.3.0',
            t: Date.now()
        }).addTo(map);

        window.currentPoiLayer = L.tileLayer.wms('http://localhost:8080/geoserver/travel/wms', {
            layers: poiLayerName,
            format: 'image/png',
            transparent: true,
            version: '1.3.0',
            t: Date.now()
        }).addTo(map);

        // 强制重绘地图
        map.invalidateSize();

    } catch(error) {
        console.error('致命错误:', error);
        alert(`操作失败: ${error.message}`);
    }
}

// HTML按钮绑定（必须！）
document.getElementById('generateBtn').addEventListener('click', submitTags);

