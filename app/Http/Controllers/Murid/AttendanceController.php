<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $murid = Auth::user();

        $status = $request->query('status'); // hadir | izin | sakit | alpa
        $month = $request->query('month');   // format: YYYY-MM

        $attendances = Attendance::with('subject')
            ->where('student_id', $murid->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($month, function ($q) use ($month) {
                // Aman untuk format YYYY-MM dari input type="month"
                if (preg_match('/^(\\d{4})-(\\d{2})$/', $month, $m)) {
                    $q->whereYear('attendance_date', (int) $m[1])
                      ->whereMonth('attendance_date', (int) $m[2]);
                }
            })
            ->orderByDesc('attendance_date')
            ->paginate(15)
            ->withQueryString();

        return view('murid.attendances.index', compact('attendances', 'status', 'month'));
    }
}

