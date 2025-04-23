<?php

namespace App\Services\Helpers\NodeRed;


class WSBuilder implements GroupBuilder
{
    public function buildGroup(array $validated, array $flow, string $flowId): array
    {
        $sensorName = $validated['sensor_name'];
        $wsPath = $validated['ws_path'];

        $maxY = Utils::getMaxY($flow);

        $offsetY = 50;
        $baseX = 100;
        $baseY = $maxY + $offsetY;

        $groupId = Utils::uid();
        $commentId = Utils::uid();
        $injectId = Utils::uid();
        $functionId = Utils::uid();
        $wsOutId1 = Utils::uid();
        $wsInId = Utils::uid();
        $jsonId = Utils::uid();
        $wsOutId2 = Utils::uid();
        $debugId = Utils::uid();

        $configs = $flow['configs'] ?? [];

        $existingWebSocketOutId1 = Utils::existNode($configs, 'websocket-listener', 'path', parse_url($wsPath)['path']);
        $existingWebSocketOutId2 = Utils::existNode($configs, 'websocket-listener', 'path', "/ws/{$sensorName}");
        $existingWebSocketInId = Utils::existNode($configs, 'websocket-client', 'path', $wsPath);

        $websocketOutId1 = $existingWebSocketOutId1 ?? Utils::uid();
        $websocketOutId2 = $existingWebSocketOutId2 ?? Utils::uid();
        $websocketInId = $existingWebSocketInId ?? Utils::uid();

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
                    $wsOutId1,
                    $wsInId,
                    $jsonId,
                    $wsOutId2,
                    $debugId

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
                "name" => "Sensor: {$sensorName} - WS Communication",
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
                "repeat" => "5",
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
                "func" => "function getRandom(min, max) {\n    return Math.random() * (max - min) + min;\n}\nconst speed = getRandom(0, 120).toFixed(2);\n\nmsg.payload = {\n    speed: parseFloat(speed),\n    timestamp: new Date().toISOString()\n};\n\nreturn msg;\n",
                "outputs" => 1,
                "timeout" => 0,
                "x" => $baseX + 320,
                "y" => $baseY + 60,
                "wires" => [[$wsOutId1]]
            ],
            [
                "id" => $wsOutId1,
                "type" => "websocket out",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "",
                "server" => $websocketOutId1,
                "x" => $baseX + 570,
                "y" => $baseY + 60,
                "wires" => [],
            ],
            [
                "id" => $wsInId,
                "type" => "websocket in",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "",
                "server" => "",
                "client" => $websocketInId,
                "x" => $baseX + 100,
                "y" => $baseY + 130,
                "wires" => [[$jsonId]]
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
                        $wsOutId2,
                        $debugId
                    ]
                ]
            ],
            [
                "id" => $wsOutId2,
                "type" => "websocket out",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "",
                "server" => $websocketOutId2,
                "x" => $baseX + 550,
                "y" => $baseY + 110,
                "wires" => [],
            ],
            [
                "id" => $debugId,
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
        if (!$existingWebSocketOutId1) {
            $nodes[] = [
                "id" => $websocketOutId1,
                "type" => "websocket-listener",
                "path" => parse_url($wsPath)['path'],
                "wholemsg" => false
            ];
        }
        if (!$existingWebSocketOutId2) {
            $nodes[] = [
                "id" => $websocketOutId2,
                "type" => "websocket-listener",
                "path" => "/ws/{$sensorName}",
                "wholemsg" => false
            ];
        }
        if (!$existingWebSocketInId) {
            $nodes[] = [
                "id" => $websocketInId,
                "type" => "websocket-client",
                "path" => $wsPath,
                "wholemsg" => false
            ];
        }
        $flow['nodes'] = array_merge($flow['nodes'], $nodes);
        $sensor = [
            "flow_id" => $flowId,
            "group_id" => $flowId,
            "name" => $sensorName,
            "protocol" => "ws"
        ];

        return [
            'flow' => $flow,
            'sensor' => $sensor
        ];
    }
}
