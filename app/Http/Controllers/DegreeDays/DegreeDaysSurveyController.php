<?php

namespace App\Http\Controllers\DegreeDays;

use App\Http\Controllers\Controller;
use App\Models\DegreeDays\DegreeDaysSurvey;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\SensorReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DegreeDaysSurveyController extends Controller
{
    /**
     * Lấy danh sách trạm mà user hiện tại có quyền sở hữu / quản lý
     */
    protected function getAllowedStations()
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isManager()) {
            return MonitoringStation::with('garden')->get();
        }

        // Nông dân thông thường chỉ truy cập trạm thuộc vườn của mình
        return MonitoringStation::whereHas('garden', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('garden')->get();
    }

    /**
     * Màn hình chính: Form khảo sát mới & Danh sách lịch sử khảo sát
     */
    public function index(Request $request)
    {
        $allowedStations = $this->getAllowedStations();
        $allowedStationIds = $allowedStations->pluck('id')->toArray();
        $user = Auth::user();

        // 1. Truy vấn danh sách khảo sát của user hiện tại (admin có thể xem tất cả nếu muốn)
        $query = DegreeDaysSurvey::with(['user', 'station.garden', 'sensorReading']);

        if (!$user->isAdmin() && !$user->isManager()) {
            $query->where('user_id', $user->id);
        }

        // Bộ lọc nếu có
        if ($request->filled('station_id')) {
            $query->where('monitoring_station_id', $request->station_id);
        }
        if ($request->filled('object_type')) {
            $query->where('object_type', $request->object_type);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('date')) {
            $query->whereDate('surveyed_at', $request->date);
        }

        $surveys = $query->orderBy('surveyed_at', 'desc')->paginate(15)->withQueryString();

        // Thống kê nhanh cho user
        $totalSurveys = DegreeDaysSurvey::when(!$user->isAdmin() && !$user->isManager(), function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        $todaySurveys = DegreeDaysSurvey::when(!$user->isAdmin() && !$user->isManager(), function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereDate('surveyed_at', Carbon::today())->count();

        $pestSurveys = DegreeDaysSurvey::when(!$user->isAdmin() && !$user->isManager(), function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('object_type', 'pest')->count();

        $diseaseSurveys = DegreeDaysSurvey::when(!$user->isAdmin() && !$user->isManager(), function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('object_type', 'disease')->count();

        // Trạm mặc định chọn trên form
        $selectedStationId = $request->input('selected_station_id', $allowedStations->first()?->id);
        $initialSnapshot = null;
        if ($selectedStationId) {
            $initialSnapshot = $this->getClosestIotSnapshot((int)$selectedStationId, Carbon::now());
        }

        // 2. Dữ liệu biểu đồ trực quan (Chỉ tạo khi là Admin hoặc Manager)
        $chartData = null;
        if ($user->isAdmin() || $user->isManager()) {
            // Biểu đồ phân bố mức độ bệnh hại lá
            $leafDiseaseCounts = [
                'Không có' => DegreeDaysSurvey::where('object_type', 'disease')
                    ->where(function ($q) {
                        $q->where('affected_part', 'leaf')->orWhereNull('affected_part');
                    })->where('severity', 'none')->count(),
                'Ít' => DegreeDaysSurvey::where('object_type', 'disease')
                    ->where(function ($q) {
                        $q->where('affected_part', 'leaf')->orWhereNull('affected_part');
                    })->where('severity', 'low')->count(),
                'Trung bình' => DegreeDaysSurvey::where('object_type', 'disease')
                    ->where(function ($q) {
                        $q->where('affected_part', 'leaf')->orWhereNull('affected_part');
                    })->where('severity', 'medium')->count(),
                'Nhiều' => DegreeDaysSurvey::where('object_type', 'disease')
                    ->where(function ($q) {
                        $q->where('affected_part', 'leaf')->orWhereNull('affected_part');
                    })->where('severity', 'high')->count(),
                'Bùng phát' => DegreeDaysSurvey::where('object_type', 'disease')
                    ->where(function ($q) {
                        $q->where('affected_part', 'leaf')->orWhereNull('affected_part');
                    })->where('severity', 'outbreak')->count(),
            ];

            // Biểu đồ phân bố giai đoạn sâu đục cuống quả
            $stageCounts = [
                'Trứng' => DegreeDaysSurvey::where('object_type', 'pest')->where('development_stage', 'egg')->count(),
                'Sâu non' => DegreeDaysSurvey::where('object_type', 'pest')->where('development_stage', 'larva')->count(),
                'Nhộng' => DegreeDaysSurvey::where('object_type', 'pest')->where('development_stage', 'pupa')->count(),
                'Trưởng thành' => DegreeDaysSurvey::where('object_type', 'pest')->where('development_stage', 'adult')->count(),
                'Không phát hiện' => DegreeDaysSurvey::where('object_type', 'pest')->where('development_stage', 'none')->count(),
            ];

            $chartData = [
                'leaf_disease' => [
                    'labels' => array_keys($leafDiseaseCounts),
                    'data' => array_values($leafDiseaseCounts),
                ],
                'stages' => [
                    'labels' => array_keys($stageCounts),
                    'data' => array_values($stageCounts),
                ],
            ];
        }

        return view('degree_days.index', compact(
            'allowedStations',
            'surveys',
            'totalSurveys',
            'todaySurveys',
            'pestSurveys',
            'diseaseSurveys',
            'selectedStationId',
            'initialSnapshot',
            'chartData'
        ));
    }


    /**
     * API AJAX: Lấy snapshot dữ liệu IoT gần nhất theo trạm và thời gian khảo sát
     */
    public function getStationSnapshot(Request $request)
    {
        $stationId = (int) $request->input('station_id');
        $allowedStationIds = $this->getAllowedStations()->pluck('id')->toArray();

        if (!in_array($stationId, $allowedStationIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập dữ liệu của trạm này.',
            ], 403);
        }

        $timeInput = $request->input('surveyed_at');
        $surveyedAt = $timeInput ? Carbon::parse($timeInput) : Carbon::now();

        $snapshot = $this->getClosestIotSnapshot($stationId, $surveyedAt);

        return response()->json([
            'success' => true,
            'data' => $snapshot,
        ]);
    }

    /**
     * Lưu bản ghi khảo sát thực địa kèm snapshot IoT
     */
    public function store(Request $request)
    {
        $allowedStations = $this->getAllowedStations();
        $allowedStationIds = $allowedStations->pluck('id')->toArray();

        // 1. Backend Validation nghiêm ngặt
        $validated = $request->validate([
            'monitoring_station_id' => [
                'required',
                'integer',
                Rule::in($allowedStationIds),
            ],
            'surveyed_at' => 'required|date|before_or_equal:' . Carbon::now()->addMinutes(5)->toDateTimeString(),
            'object_type' => 'required|in:pest,disease',
            'severity' => 'required|in:none,low,medium,high,outbreak',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB
        ], [
            'monitoring_station_id.in' => 'Trạm quan trắc không hợp lệ hoặc bạn không có quyền sở hữu trạm này.',
            'surveyed_at.before_or_equal' => 'Thời gian khảo sát không thể ở tương lai.',
            'object_type.in' => 'Loại đối tượng khảo sát không hợp lệ.',
            'severity.in' => 'Mức độ phát sinh không hợp lệ.',
            'image.max' => 'Dung lượng ảnh không được vượt quá 10MB.',
            'image.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
        ]);

        // 2. Validate có điều kiện tùy theo loại đối tượng
        if ($request->object_type === 'pest') {
            $request->validate([
                'development_stage' => 'required|in:none,egg,larva,pupa,adult,unknown',
                'quantity_range' => 'required|in:unknown,1_5,6_20,21_50,gt_50',
            ], [
                'development_stage.required' => 'Vui lòng chọn giai đoạn sâu phát triển.',
                'quantity_range.required' => 'Vui lòng chọn khoảng số lượng sâu quan sát được.',
            ]);
            $developmentStage = $request->development_stage;
            $quantityRange = $request->quantity_range;
            $affectedPart = null;
            $infectionRateRange = null;
        } else {
            $request->validate([
                'affected_part' => 'required|in:leaf,flower,fruit,branch,other',
                'infection_rate_range' => 'required|in:lt_5,5_10,10_25,25_50,gt_50',
            ], [
                'affected_part.required' => 'Vui lòng chọn bộ phận cây trồng bị bệnh.',
                'infection_rate_range.required' => 'Vui lòng chọn tỷ lệ số cây bị nhiễm bệnh.',
            ]);
            $developmentStage = null;
            $quantityRange = null;
            $affectedPart = $request->affected_part;
            $infectionRateRange = $request->infection_rate_range;
        }

        // 3. Xử lý ảnh chụp thực địa nếu có
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads/degree_days_surveys', 'public');
        }

        // 4. Bắt cố định Snapshot IoT tại thời điểm khảo sát
        $surveyedAt = Carbon::parse($validated['surveyed_at']);
        $iotSnapshot = $this->getClosestIotSnapshot((int)$validated['monitoring_station_id'], $surveyedAt);
        $station = MonitoringStation::with('garden')->find($validated['monitoring_station_id']);

        $vals = $iotSnapshot['values'] ?? [];

        // 5. Lưu vào Database (Lưu cả snapshot tên trạm & user để bảng độc lập hoàn toàn)
        $survey = DegreeDaysSurvey::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'monitoring_station_id' => $station?->id ?? $validated['monitoring_station_id'],
            'station_code' => $station?->code,
            'station_name' => $station?->name,
            'garden_name' => $station?->garden?->name,
            'surveyed_at' => $surveyedAt,
            'object_type' => $validated['object_type'],
            'development_stage' => $developmentStage,
            'quantity_range' => $quantityRange,
            'affected_part' => $affectedPart,
            'infection_rate_range' => $infectionRateRange,
            'severity' => $validated['severity'],
            'image_path' => $imagePath,
            'notes' => $validated['notes'] ?? null,
            'iot_sensor_reading_id' => $iotSnapshot['reading_id'] ?? null,
            'iot_recorded_at' => $iotSnapshot['recorded_at'] ?? null,
            'iot_temperature' => $vals['temp'] ?? null,
            'iot_humidity' => $vals['humidity'] ?? null,
            'iot_rainfall' => $vals['rain'] ?? null,
            'iot_light' => $vals['light'] ?? null,
            'iot_wind_speed' => $vals['wind'] ?? null,
            'iot_soil_moisture' => $vals['soil_moist'] ?? null,
            'iot_soil_temp' => $vals['soil_temp'] ?? null,
            'iot_soil_ph' => $vals['soil_ph'] ?? null,
            'iot_snapshot' => $iotSnapshot['raw'] ?? null,
        ]);

        return redirect()->route('degree-days.surveys.index')
            ->with('success', 'Đã lưu bản ghi khảo sát thực địa & liên kết snapshot IoT thành công!');
    }

    /**
     * API xem chi tiết bản ghi khảo sát (cho Modal)
     */
    public function show($id)
    {
        $survey = DegreeDaysSurvey::with(['user', 'station.garden', 'sensorReading'])->findOrFail($id);
        $user = Auth::user();

        // Kiểm tra quyền: Nông dân chỉ xem được khảo sát của chính mình
        if (!$user->isAdmin() && !$user->isManager() && $survey->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem bản ghi khảo sát này.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'survey' => [
                'id' => $survey->id,
                'surveyed_at' => $survey->surveyed_at->format('H:i d/m/Y'),
                'surveyor_name' => $survey->user->name ?? $survey->user_name ?? 'Người dùng',
                'station_name' => $survey->station->name ?? $survey->station_name ?? '--',
                'station_code' => $survey->station->code ?? $survey->station_code ?? '--',
                'garden_name' => $survey->station->garden->name ?? $survey->garden_name ?? 'Vùng trồng',
                'object_type' => $survey->object_type,
                'object_type_label' => $survey->object_type_label,
                'development_stage_label' => $survey->development_stage_label,
                'quantity_range_label' => $survey->quantity_range_label,
                'affected_part_label' => $survey->affected_part_label,
                'infection_rate_label' => $survey->infection_rate_label,
                'severity_label' => $survey->severity_label,
                'severity_badge_class' => $survey->severity_badge_class,
                'notes' => $survey->notes,
                'image_url' => $survey->image_path ? asset('storage/' . $survey->image_path) : null,
                'iot' => [
                    'has_reading' => !empty($survey->iot_recorded_at),
                    'recorded_at' => $survey->iot_recorded_at ? Carbon::parse($survey->iot_recorded_at)->format('H:i d/m/Y') : null,
                    'temperature' => $survey->iot_temperature !== null ? ($survey->iot_temperature . ' °C') : '--',
                    'humidity' => $survey->iot_humidity !== null ? ($survey->iot_humidity . ' %') : '--',
                    'rainfall' => $survey->iot_rainfall !== null ? ($survey->iot_rainfall . ' mm') : '0.0 mm',
                    'light' => $survey->iot_light !== null ? (number_format($survey->iot_light) . ' Lux') : '--',
                    'wind' => $survey->iot_wind_speed !== null ? ($survey->iot_wind_speed . ' m/s') : '--',
                    'soil_moist' => $survey->iot_soil_moisture !== null ? ($survey->iot_soil_moisture . ' %') : '--',
                    'soil_temp' => $survey->iot_soil_temp !== null ? ($survey->iot_soil_temp . ' °C') : '--',
                    'soil_ph' => $survey->iot_soil_ph !== null ? ($survey->iot_soil_ph . ' pH') : '--',
                ]
            ]
        ]);
    }

    /**
     * Tìm bản ghi SensorReading gần nhất với surveyed_at và bóc tách dữ liệu
     */
    protected function getClosestIotSnapshot(int $stationId, Carbon $surveyedAt): array
    {
        // 1. Bản ghi trước thời điểm khảo sát
        $before = SensorReading::where('monitoring_station_id', $stationId)
            ->where('recorded_at', '<=', $surveyedAt)
            ->latest('recorded_at')
            ->first();

        // 2. Bản ghi sau thời điểm khảo sát
        $after = SensorReading::where('monitoring_station_id', $stationId)
            ->where('recorded_at', '>=', $surveyedAt)
            ->oldest('recorded_at')
            ->first();

        $closest = null;
        if ($before && $after) {
            $diffBefore = abs($surveyedAt->diffInSeconds(Carbon::parse($before->recorded_at)));
            $diffAfter = abs($surveyedAt->diffInSeconds(Carbon::parse($after->recorded_at)));
            $closest = $diffBefore <= $diffAfter ? $before : $after;
        } else {
            $closest = $before ?: $after;
        }

        if (!$closest) {
            return [
                'has_reading' => false,
                'reading_id' => null,
                'recorded_at' => null,
                'recorded_at_display' => 'Chưa có bản ghi đo đạc từ trạm',
                'values' => [],
                'raw' => null,
            ];
        }

        $parsed = $this->extractReadingValues($closest->data ?? []);
        $recAt = Carbon::parse($closest->recorded_at);

        return [
            'has_reading' => true,
            'reading_id' => $closest->id,
            'recorded_at' => $recAt->toDateTimeString(),
            'recorded_at_display' => $recAt->format('H:i:s d/m/Y'),
            'values' => $parsed,
            'raw' => $closest->data,
        ];
    }

    /**
     * Bóc tách các chỉ số cảm biến từ gói JSON
     */
    protected function extractReadingValues(array $data): array
    {
        $res = [];

        // Hỗ trợ dạng mảng readings
        if (isset($data['readings']) && is_array($data['readings'])) {
            foreach ($data['readings'] as $item) {
                $code = strtoupper($item['device_code'] ?? '');
                $val = $item['value'] ?? null;
                if ($val === null) continue;

                if (str_contains($code, 'TEMP_AIR') || $code === 'TEMP') $res['temp'] = (float) $val;
                elseif (str_contains($code, 'HUM_AIR') || $code === 'HUM') $res['humidity'] = (float) $val;
                elseif (str_contains($code, 'SOIL_MOIST')) $res['soil_moist'] = (float) $val;
                elseif (str_contains($code, 'SOIL_PH') || $code === 'PH') $res['soil_ph'] = (float) $val;
                elseif (str_contains($code, 'SOIL_TEMP')) $res['soil_temp'] = (float) $val;
                elseif (str_contains($code, 'LIGHT') || $code === 'LUX') $res['light'] = (int) $val;
                elseif (str_contains($code, 'RAIN')) $res['rain'] = (float) $val;
                elseif (str_contains($code, 'WIND')) $res['wind'] = (float) $val;
            }
        }

        // Hỗ trợ dạng JSON key-value phẳng
        if (isset($data['temp_air'])) $res['temp'] = (float) $data['temp_air'];
        if (isset($data['temperature'])) $res['temp'] = (float) $data['temperature'];
        if (isset($data['humidity_air'])) $res['humidity'] = (float) $data['humidity_air'];
        if (isset($data['humidity'])) $res['humidity'] = (float) $data['humidity'];
        if (isset($data['rain'])) $res['rain'] = (float) $data['rain'];
        if (isset($data['light'])) $res['light'] = (int) $data['light'];
        if (isset($data['wind_speed'])) $res['wind'] = (float) $data['wind_speed'];
        if (isset($data['wind'])) $res['wind'] = (float) $data['wind'];
        if (isset($data['soil_ph'])) $res['soil_ph'] = (float) $data['soil_ph'];
        if (isset($data['soil_temperature'])) $res['soil_temp'] = (float) $data['soil_temperature'];
        if (isset($data['soil_temp'])) $res['soil_temp'] = (float) $data['soil_temp'];
        if (isset($data['soil_moisture'])) $res['soil_moist'] = (float) $data['soil_moisture'];
        if (isset($data['soil_moist'])) $res['soil_moist'] = (float) $data['soil_moist'];

        return $res;
    }
}
