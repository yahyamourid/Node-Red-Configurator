<?php 

namespace App\Helpers\NodeRed;

class HttpBuilder implements GroupBuilder
{
    public function buildGroup(array $validated, array $flow, string $flowId): array
    {
        $sensorName = $validated['sensor_name'];
        $method = $validated['method'];

        $maxY = Utils::getMaxy($flow);

        $offsetY = 50;
        $baseX = 100;
        $baseY = $maxY + $offsetY;

        $groupId = Utils::uid();
        $commentId = Utils::uid();
        $injectId = Utils::uid();
        $functionId = Utils::uid();
        $httpOutId = Utils::uid();
        $debugId1 = Utils::uid();
        $httpInId = Utils::uid();
        $httpRespId = Utils::uid();
        $jsonId = Utils::uid();
        $wsOutId = Utils::uid();
        $debugId2 = Utils::uid();

        $configs = $flow['configs'] ?? [];

        $existingWebSocketId = Utils::existNode($configs, 'websocket-listener', 'path', "/ws/{$sensorName}");

        $websocketId = $existingWebSocketId ?? Utils::uid();

        $nodes = [
            [
                "id" => $groupId,
                "type" => "group",
                "z" => $flowId,
                "style" => [
                    "stroke" => "#999999",
                    "fill" => "none",
                    "label" => true,
                    "label-position" => "nw",
                    "color" => "#a4a4a4"
                ],
                "nodes" => [
                    $commentId,
                    $injectId,
                    $functionId,
                    $httpOutId,
                    $debugId1,
                    $httpInId,
                    $httpRespId,
                    $jsonId,
                    $wsOutId,
                    $debugId2,
                ],
                "x" => $baseX,
                "y" => $baseY,
                "w" => 600,
                "h" => 300
            ],
            [
                "id" => $commentId,
                "type" => "comment",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "Sensor: {$sensorName} - HTTP Communication",
                "x" => $baseX + 120,
                "y" => $baseY + 5,
                "wires" => []
            ],
            [
                "id" => $injectId,
                "type" => "inject",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "",
                "props" => [],
                "repeat" => "",
                "once" => false,
                "onceDelay" => 0.1,
                "topic" => "",
                "x" => $baseX + 100,
                "y" => $baseY + 60,
                "wires" => [[$functionId]]
            ],
            [
                "id" => $functionId,
                "type" => "function",
                "z" => $flowId, 
                "g" => $groupId,
                "name" => "gen {$sensorName}",
                "func"=> "function getRandom(min, max) {\n    return Math.random() * (max - min) + min;\n}\nconst speed = getRandom(0, 120).toFixed(2);\n\nmsg.payload = {\n    speed: parseFloat(speed),\n    timestamp: new Date().toISOString()\n};\n\nmsg.headers = {\n    \"Content-Type\": \"application/json\"\n};\n\nmsg.method = \"POST\";\nmsg.url = \"http://localhost:1880/sensors/${sensorName}\";\n\nreturn msg;\n",
                "outputs" => 1,
                "timeout" => 0,
                "x" => $baseX + 320,
                "y" => $baseY + 60,
                "wires" => [[$httpOutId]]
            ],
            [
                "id" => $httpOutId,
                "type" => "http request",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "req {$sensorName}",
                "method" => "use",
                "ret" => "txt",
                "paytoqs" => "ignore",
                "url" => "",
                "tls" => "",
                "persist" => false,
                "proxy" => "",
                "insecureHTTPParser" => false,
                "authType" => "",
                "senderr" => false,
                "headers" => [],
                "x" => $baseX + 550,
                "y" => $baseY + 60,
                "wires" => [
                    [
                        $debugId1
                    ]
                ]
            ],
            [
                "id" => $debugId1,
                "type" => "debug",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "debug {$sensorName} 1",
                "active" => false,
                "tosidebar" => true,
                "console" => false,
                "tostatus" => false,
                "x" => $baseX + 780,
                "y" => $baseY + 60,
                "wires" => [],
            ],
            [
                "id" => $httpInId,
                "type" => "http in",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "",
                "url" => "/sensors/{$sensorName}",
                "method" => strtolower($method),
                "upload" => false,
                "swaggerDoc" => "",
                "x" => $baseX + 100,
                "y" => $baseY + 130,
                "wires" => [
                    [
                        $jsonId,
                        $httpRespId
                    ]
                ]
            ],
            [
                "id" => $httpRespId,
                "type" => "http response",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "",
                "statusCode" => "",
                "headers" => [],
                "x" => $baseX + 340,
                "y" => $baseY + 180,
                "wires" => []
            ],
            [
                "id" => $jsonId,
                "type" => "json",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "",
                "property" => "payload",
                "action" => "",
                "pretty" => false,
                "x" => $baseX + 340,
                "y" => $baseY + 130,
                "wires" => [
                    [
                        $wsOutId,
                        $debugId2
                    ]
                ]
            ],
            [
                "id" => $wsOutId,
                "type" => "websocket out",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "",
                "server" => $websocketId,
                "x" => $baseX + 570,
                "y" => $baseY + 110,
                "wires" => [],
            ],
            [
                "id" => $debugId2,
                "type" => "debug",
                "z" => $flowId,
                "name" => "debug {$sensorName} 2",
                "active" => false,
                "tosidebar" => true,
                "console" => false,
                "tostatus" => false,
                "x" => $baseX + 550,
                "y" => $baseY + 160,
                "wires" => [],
            ]
        ];
        if (!$existingWebSocketId) {
            $nodes[] = [
                "id" => $websocketId,
                "type" => "websocket-listener",
                "path" => "/ws/{$sensorName}",
                "wholemsg" => false
            ];
        }
        $flow['nodes'] = array_merge($flow['nodes'], $nodes);
        $sensor = [
            "flow_id" => $flowId,
            "group_id" => $flowId,
            "name" => $sensorName,
            "protocol" => "http"
        ];

        return [
            'flow' => $flow,
            'sensor' => $sensor
        ];
    }
}