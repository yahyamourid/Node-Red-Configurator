<?php

namespace App\Services;

use App\Models\Sensor;
class SensorService 
{
    public function getAllSensors(){
        return Sensor::all();
        
    }

    public function getSensorById($id){
        return Sensor::findOrFail( $id );
    }

    public function createSensor(array $data): Sensor
    {
        return Sensor::create( $data );
    }

    public function updateSensor($id, $data): Sensor
    {
        $sensor = Sensor::findOrFail( $id );
        return $sensor->update( $data );
    }

    public function deleteSensor($id)
    {
        $sensor = Sensor::findOrFail( $id );
        return $sensor->delete();
    }

    public function isSensorExist($name)
    {
        return Sensor::where("name", $name)->exists();
    }

    public function getSensorsbyFlowId($flowId)
    {
        return Sensor::where("flow_id", $flowId)->get();
    }
    
}