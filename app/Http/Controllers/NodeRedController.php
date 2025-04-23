<?php

namespace App\Http\Controllers;

use App\Services\NodeRedFlowService;
use App\Services\SensorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NodeRedController extends Controller
{
    protected $flowService;

    public function __construct(NodeRedFlowService $flowService, SensorService $sensorService)
    {
        $this->flowService = $flowService;
    }

    public function addMqttSensor(Request $request)
    {
        $validated = $request->validate([
            'flow_id' => 'required|string',
            'sensor_name' => 'required|string',
            'broker_url' => 'required|string',
            'broker_port' => 'required|numeric|digits_between:1,4',
            'topic' => 'required|string',
        ]);

        try {
            $this->flowService->generateMqttGroup($validated);
            return response()->json([
                'success' => true,
                'message' => 'MQTT group added successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addHttpSensor(Request $request)
    {
        $validated = $request->validate([
            'flow_id' => 'required|string',
            'sensor_name' => 'required|string',
            'method' => 'required|string',
        ]);

        try {
            $this->flowService->generateHttpGroup($validated);
            return response()->json([
                'success' => true,
                'message' => 'Http group added successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function addWsSensor(Request $request)
    {
        $validated = $request->validate([
            'flow_id' => 'required|string',
            'sensor_name' => 'required|string',
            'ws_path' => 'required|string',
        ]);

        try {
            $this->flowService->generateWSGroup($validated);
            return response()->json([
                'success' => true,
                'message' => 'WS group added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}