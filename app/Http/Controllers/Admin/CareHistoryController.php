<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farm\CareHistory;
use App\Models\Farm\CareCategory;
use App\Models\User;
use App\Models\Farm\Garden;
use Illuminate\Http\Request;

class CareHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = CareHistory::with(['user', 'garden', 'category']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('performed_at', $request->input('date'));
        }

        $logs = $query->latest('performed_at')->paginate(15);
        $users = User::where('role_id', 3)->get();
        $categories = CareCategory::all();

        return view('care.logs', compact('logs', 'users', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'garden_id' => 'nullable|exists:gardens,id',
            'care_category_id' => 'nullable|exists:care_categories,id',
            'content' => 'required|string',
            'performed_at' => 'required|date',
        ]);

        if (empty($validated['care_category_id'])) {
            $defaultCat = CareCategory::first();
            if (!$defaultCat) {
                $defaultCat = CareCategory::create([
                    'user_id' => $validated['user_id'],
                    'name' => 'Chăm sóc chung',
                    'description' => 'Công việc tưới nước, bón phân, làm cỏ...',
                    'sort_order' => 1,
                ]);
            }
            $validated['care_category_id'] = $defaultCat->id;
        }

        if (empty($validated['garden_id'])) {
            $defaultGarden = Garden::where('user_id', $validated['user_id'])->first();
            $validated['garden_id'] = $defaultGarden ? $defaultGarden->id : null;
        }

        CareHistory::create($validated);

        return redirect()->route('care.logs')->with('success', 'Tạo nhật ký chăm sóc hỗ trợ người dân thành công.');
    }

    public function update(Request $request, $id)
    {
        $log = CareHistory::findOrFail($id);

        $validated = $request->validate([
            'care_category_id' => 'required|exists:care_categories,id',
            'content' => 'required|string',
            'performed_at' => 'nullable|date',
        ]);

        $log->update($validated);

        return redirect()->route('care.logs')->with('success', 'Cập nhật nhật ký chăm sóc thành công.');
    }

    public function destroy($id)
    {
        $log = CareHistory::findOrFail($id);
        $log->delete();

        return redirect()->route('care.logs')->with('success', 'Xóa nhật ký chăm sóc thành công.');
    }

    public function export()
    {
        return response()->json(['message' => 'Export care history initiated']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        return redirect()->route('care.logs')->with('success', 'Nhập danh sách hướng dẫn công việc từ Excel thành công.');
    }
}
