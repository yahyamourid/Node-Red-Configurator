<?php

namespace App\Helpers\NodeRed;


class MqttBuilder implements GroupBuilder
{
    public function buildGroup(array $validated, array $flow, string $flowId): array
    {
        $sensorName = $validated['sensor_name'];
        $brokerUrl = $validated['broker_url'];
        $brokerPort = $validated['broker_port'];
        $topic = $validated['topic'];

        $maxY = Utils::getMaxy($flow);


        $offsetY = 50;
        $baseX = 100;
        $baseY = $maxY + $offsetY;

        $groupId = Utils::uid();
        $commentId = Utils::uid();
        $injectId = Utils::uid();
        $functionId = Utils::uid();
        $mqttOutId = Utils::uid();
        $mqttInId = Utils::uid();
        $wsOutId = Utils::uid();
        $debugId = Utils::uid();

        $configs = $flow['configs'] ?? [];

        $existingBrokerId = Utils::existNode($configs, 'mqtt-broker', 'broker', $brokerUrl);
        $existingWebSocketId = Utils::existNode($configs, 'websocket-listener', 'path', "/ws/{$sensorName}");

        $brokerId = $existingBrokerId ?? Utils::uid();
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
                    $injectId,
                    $functionId,
                    $mqttOutId,
                    $commentId,
                    $mqttInId,
                    $wsOutId,
                    $debugId
                ],
                "x" => $baseX,
                "y" => $baseY,
                "w" => 650,
                "h" => 282
            ],
            [
                "id" => $commentId,
                "type" => "comment",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "Sensor: {$sensorName} - MQTT Communication",
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
                "func" => "function getRandom(min, max) {\n    return Math.random() * (max - min) + min;\n}\n\nconst temperature = getRandom(20, 35).toFixed(2);\nconst humidity = getRandom(50, 90).toFixed(2);\n\nmsg.payload = {\n    temperature: parseFloat(temperature),\n    humidity: parseFloat(humidity),\n    sensor: '{$sensorName}',\n    timestamp: new Date().toISOString()\n};\n\nreturn msg;",
                "outputs" => 1,
                "timeout" => 0,
                "x" => $baseX + 320,
                "y" => $baseY + 60,
                "wires" => [[$mqttOutId]]
            ],

            [
                "id" => $mqttOutId,
                "type" => "mqtt out",
                "z" => $flowId,
                "g" => $groupId,
                "name" => "",
                "topic" => $topic,
                "qos" => "",
                "retain" => "",
                "broker" => $brokerId,
                "x" => $baseX + 550,
                "y" => $baseY + 60,
                "wires" => []
            ],
            [
                "id" => $mqttInId,
                "type" => "mqtt in",
                "z" => $flowId,
                "name" => "",
                "topic" => $topic,
                "qos" => "2",
                "datatype" => "auto-detect",
                "broker" => $brokerId,
                "x" => $baseX + 100,
                "y" => $baseY + 130,
                "wires" => [[$debugId, $wsOutId]],
            ],
            [
                "id" => $wsOutId,
                "type" => "websocket out",
                "z" => $flowId,
                "name" => "",
                "server" => $websocketId,
                "x" => $baseX + 350,
                "y" => $baseY + 120,
                "wires" => [],
            ],
            [
                "id" => $debugId,
                "type" => "debug",
                "z" => $flowId,
                "name" => "debug 1",
                "active" => false,
                "tosidebar" => true,
                "console" => false,
                "tostatus" => false,
                "x" => $baseX + 330,
                "y" => $baseY + 170,
                "wires" => [],
            ]
        ];
        if (!$existingBrokerId) {
            $nodes[] = [
                "id" => $brokerId,
                "type" => "mqtt-broker",
                "name" => "",
                "broker" => $brokerUrl,
                "port" => $brokerPort,
                "clientid" => "",
                "usetls" => false,
                "protocolVersion" => 4,
                "keepalive" => 60,
                "cleansession" => true,
                "autoConnect" => true
            ];
        }

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
            "protocol" => "mqtt"
        ];

        return [
            'flow' => $flow,
            'sensor' => $sensor
        ];
    }
}