<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Farm\CareCategory;
use App\Models\Farm\CareHistory;
use App\Models\Farm\UsedProduct;
use App\Models\Farm\CareProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCareController extends Controller
{
    public function categories(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $categories = CareCategory::where('user_id', $userId)->get();

        return response()->json($categories);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['user_id'] = Auth::id() ?? 1;
        $category = CareCategory::create($validated);

        return response()->json(['success' => true, 'data' => $category]);
    }

    public function histories(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $query = CareHistory::with('category')->where('user_id', $userId);

        if ($request->filled('date')) {
            $query->whereDate('performed_at', $request->date);
        }

        if ($request->filled('category_id')) {
            $query->where('care_category_id', $request->category_id);
        }

        $histories = $query->latest('performed_at')->get();

        return response()->json($histories);
    }

    public function storeHistory(Request $request)
    {
        $validated = $request->validate([
            'garden_id' => 'nullable|exists:gardens,id',
            'care_category_id' => 'nullable|exists:care_categories,id',
            'content' => 'required|string',
            'performed_at' => 'required|date',
        ]);

        $validated['user_id'] = Auth::id() ?? 1;
        $history = CareHistory::create($validated);

        return response()->json(['success' => true, 'data' => $history]);
    }

    public function products(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $products = UsedProduct::where('user_id', $userId)->get();

        return response()->json($products);
    }

    public function processes(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $processes = CareProcess::where('user_id', $userId)->get();

        return response()->json($processes);
    }
}
