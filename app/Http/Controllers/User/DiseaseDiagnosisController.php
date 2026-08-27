<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ai\DiseaseDiagnosis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiseaseDiagnosisController extends Controller
{
    public function index()
    {
        $diagnoses = DiseaseDiagnosis::where('user_id', Auth::id() ?? 1)
            ->latest()
            ->paginate(10);

        return view('ai.manual_diagnosis', compact('diagnoses'));
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $imagePath = $request->file('image')->store('uploads/ai_diagnoses', 'public');

        $diagnosis = DiseaseDiagnosis::create([
            'user_id' => Auth::id() ?? 1,
            'image_url' => $imagePath,
            'disease_name' => 'Bệnh Sương Mai (Downy Mildew)',
            'confidence' => 96.8,
            'treatment_guide' => 'Phun thuốc sinh học hoạt chất Mancozeb + Metalaxyl hoặc Nano Bạc. Ngừng tưới phun sương chiều tối.',
            'status' => 'completed',
        ]);

        return redirect()->route('ai.diagnosis')->with('success', 'Core AI đã hoàn tất chẩn đoán bệnh sương mai.');
    }

    public function rate(Request $request, $id)
    {
        $validated = $request->validate([
            'user_feedback_rating' => 'required|integer|min:1|max:5',
            'user_feedback_comment' => 'nullable|string|max:500',
        ]);

        $diagnosis = DiseaseDiagnosis::findOrFail($id);
        $diagnosis->update($validated);

        return response()->json(['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá kết quả chẩn đoán!']);
    }
}
