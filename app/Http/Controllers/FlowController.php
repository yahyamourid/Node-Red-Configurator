<?php

namespace App\Http\Controllers;

use App\Services\FlowService;
use Illuminate\Http\Request;

class FlowController extends Controller
{
    protected $flowService;

    public function __construct(FlowService $flowService)
    {
        $this->flowService = $flowService;
    }
    public function index()
    {
        try {
            $flows = $this->flowService->getAllFlows();
            return response()->json($flows,200);
        } catch (\Exception $e) {
            return response()->json(["error"=> $e->getMessage()],500);
        }
    }
}
