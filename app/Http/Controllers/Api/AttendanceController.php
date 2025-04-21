<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $branchId = Auth::user()->branch_id;

        $attendances = [];
        foreach ($request->user_ids as $userId) {
            $attendances[] = Attendance::create([
                'user_id' => $userId,
                'branch_id' => $branchId
            ]);
        }

        return response()->json([
            'message' => 'Attendance marked successfully',
            'data' => $attendances
        ]);
    }

    public function index(Request $request)
    {
        $query = Attendance::with(['user', 'branch']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        return response()->json($query->latest()->paginate(15));
    }
} 