<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ai\PestPrediction;
use App\Models\Farm\Garden;
use Illuminate\Http\Request;

class PestLifecycleController extends Controller
{
    public function index()
    {
        $gardens = Garden::with('stations')->get();
        $latestPrediction = PestPrediction::with('garden')->latest()->first();

        return view('ai.pest_lifecycle', compact('gardens', 'latestPrediction'));
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'garden_id' => 'required|exists:gardens,id',
        ]);

        $prediction = PestPrediction::where('garden_id', $validated['garden_id'])
            ->latest()
            ->first();

        if (!$prediction) {
            $prediction = PestPrediction::create([
                'garden_id' => $validated['garden_id'],
                'gdd_accumulated' => 485.0,
                'gdd_target' => 500.0,
                'current_stage' => 'Sâu non đục quả (Tuổi 2-3)',
                'predicted_outbreak_date' => now()->addDays(2),
                'risk_level' => 'high',
                'prevention_guide' => 'Phun trừ sâu sinh học BT hoặc Emamectin benzoate vào chiều tối trước khi sâu đục sâu vào cuống quả.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $prediction,
        ]);
    }
}
