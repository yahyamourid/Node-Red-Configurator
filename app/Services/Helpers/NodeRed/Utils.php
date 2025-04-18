<?php

namespace App\Services\Helpers\NodeRed;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Utils
{
    public static function uid(): string
    {
        return Str::random(16);
    }

    public static function getFlows()
    {
        $response = Http::get("http://localhost:1880/flows");
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch flows');
        }
        return $response->json();
    }
    public static function getFlow(string $flowId)
    {
        $response = Http::get("http://localhost:1880/flow/{$flowId}");
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch flow');
        }
        return $response->json();
    }

    public static function updateFlow(string $flowId, array $flow)
    {
        $response = Http::put("http://localhost:1880/flow/{$flowId}", $flow);
        if (!$response->successful()) {
            throw new \Exception("Failed to update flow with ID {$flowId}.");
        }
        return true;
    }

    public static function getMaxY(array $flow)
    {
        $maxY = collect($flow['nodes'])
            ->filter(function ($n) {
                return $n['type'] === 'group';
            })
            ->max(function ($g) {
                return $g['y'] + (isset($g['h']) ? $g['h'] : 0);
            });
        return $maxY;
    }

    public static function existNode(array $configs, string $type, string $attribute, string $value)
    {
        $existingNode = null;
        foreach ($configs as $id => $config) {
            if ($config['type'] === $type && $config[$attribute] === $value) {
                $existingNode = $config['id'];
            }
        }
        return $existingNode;
    }
}