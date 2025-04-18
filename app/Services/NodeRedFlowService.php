<?php

namespace App\Services;

use App\Services\Helpers\NodeRed\HttpBuilder;
use App\Services\Helpers\NodeRed\MqttBuilder;
use App\Services\Helpers\NodeRed\Utils;
use App\Services\Helpers\NodeRed\WSBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NodeRedFlowService
{
    protected $sensorService;

    public function __construct(SensorService $sensorService)
    {
        $this->sensorService = $sensorService;
    }

    public function generateMqttGroup(array $validated): void
    {
        try {
            if (!$this->sensorService->isSensorExist($validated['sensor_name'])) {
                $mqttBuilder = new MqttBuilder();
                $flowId = $validated['flow_id'];

                $flow = Utils::getFlow($flowId);
                $builder = $mqttBuilder->buildGroup($validated, $flow, $flowId);
                $flow = $builder['flow'];
                $sensor = $builder['sensor'];
                Utils::updateFlow($flowId, $flow);
                $this->sensorService->createSensor($sensor);
            } else {
                throw new \Exception("Sensor with name {$validated['sensor_name']} already exist");
            }
        } catch (\Exception $e) {
            throw new \Exception("Failed to add MQTT group : " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function generateHttpGroup(array $validated): void
    {
        try {
            if (!$this->sensorService->isSensorExist($validated['sensor_name'])) {
                $httpBuilder = new HttpBuilder();
                $flowId = $validated['flow_id'];

                $flow = Utils::getFlow($flowId);
                $builder = $httpBuilder->buildGroup($validated, $flow, $flowId);
                $flow = $builder['flow'];
                $sensor = $builder['sensor'];
                Utils::updateFlow($flowId, $flow);
                $this->sensorService->createSensor($sensor);
            } else {
                throw new \Exception("Sensor with name {$validated['sensor_name']} already exist");
            }
        } catch (\Exception $e) {
            throw new \Exception("Failed to add HTTP group : " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function generateWSGroup(array $validated): void
    {
        try {
            if (!$this->sensorService->isSensorExist($validated['sensor_name'])) {
                $wsBuilder = new WSBuilder();
                $flowId = $validated['flow_id'];

                $flow = Utils::getFlow($flowId);
                $builder = $wsBuilder->buildGroup($validated, $flow, $flowId);
                $flow = $builder['flow'];
                $sensor = $builder['sensor'];
                Utils::updateFlow($flowId, $flow);
                $this->sensorService->createSensor($sensor);
            } else {
                throw new \Exception("Sensor with name {$validated['sensor_name']} already exist");
            }
        } catch (\Exception $e) {
            throw new \Exception("Failde to add WS group : " . $e->getMessage(), $e->getCode(), $e);
        }
    }


}
// public function generateFlow(Request $request)
//     {
//         $validated = $request->validate([
//             'sensor_name' => 'required|string',
//             'broker_url' => 'required|string',
//             'topic' => 'required|string',
//         ]);

//         $sensorName = $sensorName;
//         $brokerUrl = $brokerUrl;
//         $topic = $topic;

//         function $this->uid() {
//             return Str::random(16);
//         }

//         $tabId = $this->uid();
//         $injectId = $this->uid();
//         $functionId = $this->uid();
//         $mqttOutId = $this->uid();
//         $mqttInId = $this->uid();
//         $debugId = $this->uid();
//         $wsOutId = $this->uid();
//         $commentId = $this->uid();
//         $brokerId = $this->uid();
//         $websocketId = $this->uid();

//         $flow = [
//             "id" => $tabId,
//             "type" => "tab",
//             "label" => $sensorName,
//             "disabled" => false,
//             "env" => [],
//         ];

//         $nodes = [
//             [
//                 "id" => $injectId,
//                 "type" => "inject",
//                 "z" => $tabId,
//                 "name" => "",
//                 "props" => [],
//                 "repeat" => "5",
//                 "crontab" => "",
//                 "once" => false,
//                 "onceDelay" => 0.1,
//                 "topic" => "",
//                 "x" => 160,
//                 "y" => 100,
//                 "wires" => [ [ $functionId ] ],
//             ],
//             [
//                 "id" => $functionId,
//                 "type" => "function",
//                 "z" => $tabId,
//                 "name" => "function 1",
//                 "func" => "function getRandom(min, max) {\n    return Math.random() * (max - min) + min;\n}\n\nconst temperature = getRandom(25, 30).toFixed(2);\nconst humidity = getRandom(60, 75).toFixed(2);\n\nmsg.payload = {\n    temperature: parseFloat(temperature),\n    humidity: parseFloat(humidity),\n    timestamp: new Date().toISOString()\n};\n\nreturn msg;",
//                 "outputs" => 1,
//                 "x" => 380,
//                 "y" => 100,
//                 "wires" => [ [ $mqttOutId ] ],
//             ],
//             [
//                 "id" => $mqttOutId,
//                 "type" => "mqtt out",
//                 "z" => $tabId,
//                 "name" => "",
//                 "topic" => $topic,
//                 "broker" => $brokerId,
//                 "x" => 600,
//                 "y" => 100,
//                 "wires" => [],
//             ],
//             [
//                 "id" => $mqttInId,
//                 "type" => "mqtt in",
//                 "z" => $tabId,
//                 "name" => "",
//                 "topic" => $topic,
//                 "qos" => "2",
//                 "datatype" => "auto-detect",
//                 "broker" => $brokerId,
//                 "x" => 140,
//                 "y" => 220,
//                 "wires" => [ [ $debugId, $wsOutId ] ],
//             ],
//             [
//                 "id" => $debugId,
//                 "type" => "debug",
//                 "z" => $tabId,
//                 "name" => "debug 1",
//                 "active" => false,
//                 "tosidebar" => true,
//                 "console" => false,
//                 "tostatus" => false,
//                 "x" => 420,
//                 "y" => 240,
//                 "wires" => [],
//             ],
//             [
//                 "id" => $wsOutId,
//                 "type" => "websocket out",
//                 "z" => $tabId,
//                 "name" => "",
//                 "server" => $websocketId,
//                 "x" => 390,
//                 "y" => 160,
//                 "wires" => [],
//             ],
//             [
//                 "id" => $commentId,
//                 "type" => "comment",
//                 "z" => $tabId,
//                 "name" => "Mqtt Communication - $sensorName",
//                 "x" => 170,
//                 "y" => 40,
//                 "wires" => [],
//             ],
//             [
//                 "id" => $brokerId,
//                 "type" => "mqtt-broker",
//                 "name" => $sensorName,
//                 "broker" => $brokerUrl,
//                 "port" => 1883,
//                 "clientid" => "",
//                 "autoConnect" => true,
//                 "usetls" => false,
//                 "protocolVersion" => 4,
//                 "keepalive" => 60,
//                 "cleansession" => true,
//             ],
//             [
//                 "id" => $websocketId,
//                 "type" => "websocket-listener",
//                 "path" => "/ws/{$sensorName}",
//                 "wholemsg" => false,
//             ]
//         ];

//         return response()->json(array_merge([$flow], $nodes));
//     }
