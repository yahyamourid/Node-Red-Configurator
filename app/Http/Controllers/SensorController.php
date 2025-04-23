<?php

namespace App\Http\Controllers;

use App\Services\SensorService;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    protected $sensorService;

    public function __construct(SensorService $sensorService)
    {
        $this->sensorService = $sensorService;
    }

    public function index()
    {
        return response()->json($this->sensorService->getAllSensors(), 200);
    }

    public function show($id)
    {
        try {
            $sensor = $this->sensorService->getSensorById($id);
            return response()->json($sensor, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Sensor not found'], 404);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:sensors,name',
            'type' => 'required|string',
            'flow_id' => 'nullable|string',
            'topic' => 'nullable|string',
        ]);

        $sensor = $this->sensorService->createSensor($validated);
        return response()->json($sensor, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
            'flow_id' => 'nullable|string',
            'topic' => 'nullable|string',
        ]);

        try {
            $this->sensorService->updateSensor($id, $validated);
            return response()->json(['message' => 'Sensor updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Sensor not found or update failed'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->sensorService->deleteSensor($id);
            return response()->json(['message' => 'Sensor deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Sensor not found or delete failed'], 404);
        }
    }

    public function getByflowId($flowId){
        $sensors = $this->sensorService->getsensorsByflowId($flowId);
        return response()->json($sensors,200);
    }
}
