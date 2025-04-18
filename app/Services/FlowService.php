<?php

namespace App\Services;

use App\Services\Helpers\NodeRed\Utils;

class FlowService
{
    public function getAllFlows(): array
    {
        $allNodes = Utils::getFlows();
        $flows = [];

        foreach ($allNodes as $node) {
            if (isset($node['type']) && $node['type'] === 'tab') {
                $flows[] = [
                    'id' => $node['id'],
                    'name' => $node['label'] ?? 'Unnamed Flow',
                ];
            }
        }
        return $flows;
    }
}