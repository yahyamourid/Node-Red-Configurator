<?php
namespace App\Services\Helpers\NodeRed;
interface GroupBuilder
{
    public function buildGroup(array $validated, array $flow, string $flowId): array;
}