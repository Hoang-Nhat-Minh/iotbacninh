<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\GardenController as AdminGardenController;
use App\Http\Controllers\Admin\CareHistoryController as AdminCareHistoryController;
use App\Http\Controllers\Admin\SystemSettingController as AdminSystemSettingController;
use App\Http\Controllers\Admin\MonitoringConfigController as AdminMonitoringConfigController;
use App\Http\Controllers\Admin\CaptureScheduleController as AdminCaptureScheduleController;
use App\Http\Controllers\Admin\AutoAlertController as AdminAutoAlertController;
use App\Http\Controllers\Admin\KnowledgeBaseController as AdminKnowledgeBaseController;

use App\Http\Controllers\Manager\MonitoringStationController as ManagerStationController;
use App\Http\Controllers\Manager\MediaMonitoringController as ManagerMediaController;
use App\Http\Controllers\Manager\CaptureLocationController as ManagerLocationController;
use App\Http\Controllers\Manager\NotificationManagementController as ManagerNotificationController;
use App\Http\Controllers\Manager\SupportRequestController as ManagerSupportController;
use App\Http\Controllers\Manager\NewsController as ManagerNewsController;
use App\Http\Controllers\Manager\AgricultureKnowledgeController as ManagerKnowledgeController;
use App\Http\Controllers\Manager\ErrorLogController as ManagerErrorLogController;
use App\Http\Controllers\Manager\AccessLogController as ManagerAccessLogController;

use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\User\UserGardenController;
use App\Http\Controllers\User\UserCareController;
use App\Http\Controllers\User\DiseaseDiagnosisController as UserDiseaseDiagnosisController;
use App\Http\Controllers\User\PestLifecycleController as UserPestLifecycleController;
use App\Http\Controllers\User\ChatbotAssistantController as UserChatbotController;
use App\Http\Controllers\User\UserSupportController;
use App\Http\Controllers\User\UserNotificationController;
use App\Http\Controllers\User\UserNewsController;
use App\Http\Controllers\User\UserKnowledgeController;
use App\Http\Controllers\User\WeatherHistoryController;
use App\Http\Controllers\DegreeDays\DegreeDaysSurveyController;

Route::get('/', function () {

    return redirect('/dashboard');
});

// ==========================================
// Authentication Routes (Guest Only - UC 1, 4)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ==========================================
// Authenticated Common Routes (Admin, Manager, User)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Profile & App Settings (UC 3, 6, 25)
    Route::prefix('account')->group(function () {
        Route::get('/profile', [UserProfileController::class, 'index'])->name('account.profile');
        Route::post('/profile', [UserProfileController::class, 'update']);
        Route::post('/profile/avatar', [UserProfileController::class, 'updateAvatar']);
        Route::post('/profile/password', [UserProfileController::class, 'updatePassword']);
        Route::post('/profile/settings', [UserProfileController::class, 'updateSettings']);
    });

    // Gardens & Map (UC 7, 43)
    Route::prefix('gardens')->group(function () {
        Route::get('/map', [AdminGardenController::class, 'index'])->name('gardens.map');
    });
    Route::get('/map', function () {
        return redirect('/gardens/map');
    });

    // Care History (UC 27, 31)
    Route::prefix('care')->group(function () {
        Route::get('/logs', [AdminCareHistoryController::class, 'index'])->name('care.logs');
    });
    Route::get('/care', function () {
        return redirect('/care/logs');
    });

    // IoT Monitoring Views (UC 12-22, 44, 45, 46)
    Route::prefix('iot')->group(function () {
        Route::get('/stations', [ManagerStationController::class, 'index'])->name('iot.stations');
        Route::get('/stations/live-data', [ManagerStationController::class, 'getLiveData'])->name('iot.stations.live_data');
        Route::get('/stations/create', [ManagerStationController::class, 'create'])->name('iot.stations.create');
        Route::get('/stations/{id}/edit', [ManagerStationController::class, 'edit'])->name('iot.stations.edit');
        Route::get('/stations/{id}', [ManagerStationController::class, 'show'])->name('iot.stations.show');
        Route::get('/weather-history', [WeatherHistoryController::class, 'index'])->name('iot.weather.history');
        Route::get('/weather-history/detail/{stationId}/{date}', [WeatherHistoryController::class, 'detail'])->name('iot.weather.detail');
        Route::get('/media', [ManagerMediaController::class, 'index'])->name('iot.media');
        Route::get('/locations', [ManagerLocationController::class, 'index'])->name('iot.locations');
    });

    // Dán nhãn Tổng Nhiệt hữu hiệu (Degree-Days Labeling & Survey)
    Route::prefix('degree-days')->name('degree-days.')->group(function () {
        Route::get('/surveys', [DegreeDaysSurveyController::class, 'index'])->name('surveys.index');
        Route::post('/surveys', [DegreeDaysSurveyController::class, 'store'])->name('surveys.store');
        Route::get('/surveys/{id}', [DegreeDaysSurveyController::class, 'show'])->name('surveys.show');
        Route::get('/api/station-snapshot', [DegreeDaysSurveyController::class, 'getStationSnapshot'])->name('surveys.snapshot');
    });



    // AI Disease & Pest Prediction (UC 8, 23, 47, 48)
    Route::prefix('ai')->group(function () {
        Route::get('/diagnosis', [UserDiseaseDiagnosisController::class, 'index'])->name('ai.diagnosis');
        Route::post('/diagnosis/analyze', [UserDiseaseDiagnosisController::class, 'analyze']);
        Route::post('/diagnosis/{id}/rate', [UserDiseaseDiagnosisController::class, 'rate']);

        Route::get('/pest', [UserPestLifecycleController::class, 'index'])->name('ai.pest');
        Route::post('/pest/check', [UserPestLifecycleController::class, 'check']);
    });

    // Chatbot AI (UC 24, 49)
    Route::prefix('chatbot')->group(function () {
        Route::get('/', [UserChatbotController::class, 'index'])->name('chatbot.index');
        Route::post('/topics/store', [UserChatbotController::class, 'storeTopic']);
        Route::post('/topics/update/{id}', [UserChatbotController::class, 'updateTopic']);
        Route::post('/topics/delete/{id}', [UserChatbotController::class, 'destroyTopic']);
        Route::post('/message', [UserChatbotController::class, 'sendMessage']);
        Route::post('/stream', [UserChatbotController::class, 'streamChat'])->name('chatbot.stream');
    });

    // Notifications (UC 5)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [ManagerNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/read/{id}', [ManagerNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/delete/{id}', [ManagerNotificationController::class, 'destroy'])->name('notifications.delete');
    });

    // News & Knowledge (UC 9, 10)
    Route::prefix('content')->group(function () {
        Route::get('/news', [UserNewsController::class, 'index'])->name('content.news');
        Route::post('/news/bookmark/{id}', [UserNewsController::class, 'toggleBookmark'])->name('content.news.bookmark');
        Route::get('/news/manage', [ManagerNewsController::class, 'index'])->name('content.news.manage');

        Route::get('/knowledge', [UserKnowledgeController::class, 'index'])->name('content.knowledge');
        Route::get('/knowledge/manage', [ManagerKnowledgeController::class, 'index'])->name('content.knowledge.manage');
    });

    // Support Inbox / Request (UC 11)
    Route::prefix('support')->group(function () {
        Route::get('/', [UserSupportController::class, 'index'])->name('support.index');
        Route::post('/submit', [UserSupportController::class, 'store'])->name('support.submit');
        Route::get('/manage', [ManagerSupportController::class, 'index'])->name('support.manage');
    });
});

// ==========================================
// ROLE: Quản trị viên (Admin) Routes
// ==========================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    // User Accounts Management (UC 2)
    Route::prefix('account')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('account.users');
        Route::post('/users', [AdminUserController::class, 'store'])->name('account.users.store');
        Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('account.users.update');
        Route::post('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('account.users.toggle');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('account.users.delete');
    });

    // System Settings (UC 32)
    Route::prefix('system')->group(function () {
        Route::get('/settings', [AdminSystemSettingController::class, 'index'])->name('system.settings');
        Route::post('/settings/update', [AdminSystemSettingController::class, 'update']);

        // Monitoring Config (UC 33)
        Route::get('/monitoring-config', [AdminMonitoringConfigController::class, 'index'])->name('system.monitoring_config');
        Route::post('/monitoring-config/update', [AdminMonitoringConfigController::class, 'update']);
    });

    // Camera Image Schedules (UC 34)
    Route::prefix('iot')->group(function () {
        Route::get('/schedules', [AdminCaptureScheduleController::class, 'index'])->name('iot.schedules');
        Route::post('/schedules/store', [AdminCaptureScheduleController::class, 'store']);
        Route::post('/schedules/update/{id}', [AdminCaptureScheduleController::class, 'update']);
        Route::post('/schedules/delete/{id}', [AdminCaptureScheduleController::class, 'destroy']);
    });

    // Auto AI Alerts (UC 35, 36)
    Route::prefix('ai')->group(function () {
        Route::get('/auto-downy-mildew', [AdminAutoAlertController::class, 'downyMildew'])->name('ai.auto_downy_mildew');
        Route::post('/auto-downy-mildew/toggle', [AdminAutoAlertController::class, 'toggleDownyMildew']);

        Route::get('/auto-pest-prediction', [AdminAutoAlertController::class, 'pestPrediction'])->name('ai.auto_pest_prediction');
        Route::post('/auto-pest-prediction/toggle', [AdminAutoAlertController::class, 'togglePestPrediction']);
    });

    // Chatbot Knowledge Base (UC 58)
    Route::get('/chatbot/knowledge-base', [AdminKnowledgeBaseController::class, 'index'])->name('chatbot.knowledge_base');
    Route::post('/chatbot/knowledge-base/store', [AdminKnowledgeBaseController::class, 'store']);
    Route::post('/chatbot/knowledge-base/update/{id}', [AdminKnowledgeBaseController::class, 'update']);
    Route::post('/chatbot/knowledge-base/delete/{id}', [AdminKnowledgeBaseController::class, 'destroy']);
});

// ==========================================
// ROLE: Nhà quản lý & Quản trị viên (Manager & Admin) Routes
// ==========================================
Route::middleware(['auth', 'role:admin,manager'])->group(function () {
    // Garden Management (UC 30)
    Route::prefix('gardens')->group(function () {
        Route::post('/store', [AdminGardenController::class, 'store'])->name('gardens.store');
        Route::post('/update/{id}', [AdminGardenController::class, 'update'])->name('gardens.update');
        Route::post('/delete/{id}', [AdminGardenController::class, 'destroy'])->name('gardens.delete');
    });

    // Care Log Management & Excel (UC 31)
    Route::prefix('care')->group(function () {
        Route::post('/logs/store', [AdminCareHistoryController::class, 'store'])->name('care.logs.store');
        Route::post('/logs/update/{id}', [AdminCareHistoryController::class, 'update'])->name('care.logs.update');
        Route::post('/logs/delete/{id}', [AdminCareHistoryController::class, 'destroy'])->name('care.logs.delete');
        Route::get('/logs/export', [AdminCareHistoryController::class, 'export'])->name('care.logs.export');
        Route::post('/logs/import', [AdminCareHistoryController::class, 'import'])->name('care.logs.import');
    });

    // IoT Stations Management (UC 30, 44)
    Route::prefix('iot')->group(function () {
        Route::post('/stations/store', [ManagerStationController::class, 'store']);
        Route::post('/stations/update/{id}', [ManagerStationController::class, 'update']);
        Route::post('/stations/delete/{id}', [ManagerStationController::class, 'destroy']);

        Route::post('/media/rename', [ManagerMediaController::class, 'rename']);
        Route::post('/media/delete', [ManagerMediaController::class, 'destroy']);

        Route::post('/locations/store', [ManagerLocationController::class, 'store']);
        Route::post('/locations/update/{id}', [ManagerLocationController::class, 'update']);
        Route::post('/locations/delete/{id}', [ManagerLocationController::class, 'destroy']);
    });

    // Fallback alias routes for stations
    Route::post('/stations/store', [ManagerStationController::class, 'store']);
    Route::post('/stations/update/{id}', [ManagerStationController::class, 'update']);
    Route::post('/stations/delete/{id}', [ManagerStationController::class, 'destroy']);

    // Notification Broadcast (UC 37)
    Route::prefix('notifications')->group(function () {
        Route::post('/store', [ManagerNotificationController::class, 'store'])->name('notifications.store');
    });

    // Support Reply & Moderation (UC 38)
    Route::prefix('support')->group(function () {
        Route::post('/reply', [ManagerSupportController::class, 'reply'])->name('support.reply');
        Route::post('/delete/{id}', [ManagerSupportController::class, 'destroy'])->name('support.delete');
    });

    // News Management (UC 39)
    Route::prefix('content')->group(function () {
        Route::post('/news/store', [ManagerNewsController::class, 'store']);
        Route::post('/news/update/{id}', [ManagerNewsController::class, 'update']);
        Route::post('/news/delete/{id}', [ManagerNewsController::class, 'destroy']);

        // Knowledge Management (UC 40)
        Route::post('/knowledge/store', [ManagerKnowledgeController::class, 'store']);
        Route::post('/knowledge/update/{id}', [ManagerKnowledgeController::class, 'update']);
        Route::post('/knowledge/delete/{id}', [ManagerKnowledgeController::class, 'destroy']);
    });

    // System Logs (UC 41, 42)
    Route::prefix('system')->group(function () {
        Route::get('/error-logs', [ManagerErrorLogController::class, 'index'])->name('system.error_logs');
        Route::get('/access-logs', [ManagerAccessLogController::class, 'index'])->name('system.access_logs');
    });
});

// ==========================================
// AI Data Labeling Subsystem Routes (Admin & Manager)
// ==========================================
use App\Http\Controllers\Labeling\LabelerAuthController;
use App\Http\Controllers\Labeling\LabelerDashboardController;
use App\Http\Controllers\Labeling\ImageAnnotationController;
use App\Http\Controllers\Labeling\ImageTaskManagementController;
use App\Http\Controllers\Labeling\ImageReviewController;
use App\Http\Controllers\Labeling\DatasetExportController;
use App\Http\Controllers\Labeling\TextAnnotationController;
use App\Http\Controllers\Labeling\TextDatasetExportController;
use App\Http\Controllers\Labeling\KnowledgeBaseController;

Route::prefix('labeler')->group(function () {
    Route::get('/login', [LabelerAuthController::class, 'showLogin'])->name('labeler.login');
    Route::post('/login', [LabelerAuthController::class, 'login']);
    Route::post('/logout', [LabelerAuthController::class, 'logout'])->name('labeler.logout');

    Route::middleware(['auth', 'role:admin,manager'])->group(function () {
        Route::get('/dashboard', [LabelerDashboardController::class, 'index'])->name('labeler.dashboard');

        // Quản lý Dự Án AI (Project Management)
        Route::get('/projects', [App\Http\Controllers\Labeling\ProjectManagementController::class, 'index'])->name('labeler.projects');
        Route::post('/projects/store', [App\Http\Controllers\Labeling\ProjectManagementController::class, 'store'])->name('labeler.projects.store');
        Route::post('/projects/update/{id}', [App\Http\Controllers\Labeling\ProjectManagementController::class, 'update'])->name('labeler.projects.update');
        Route::post('/projects/delete/{id}', [App\Http\Controllers\Labeling\ProjectManagementController::class, 'destroy'])->name('labeler.projects.delete');

        // UC 48: Quản lý dữ liệu hình ảnh (Task Management)
        Route::get('/tasks', [ImageTaskManagementController::class, 'index'])->name('labeler.tasks');
        Route::post('/tasks/store', [ImageTaskManagementController::class, 'store'])->name('labeler.tasks.store');
        Route::post('/tasks/update/{id}', [ImageTaskManagementController::class, 'update'])->name('labeler.tasks.update');
        Route::post('/tasks/delete/{id}', [ImageTaskManagementController::class, 'destroy'])->name('labeler.tasks.delete');
        Route::post('/tasks/bulk-delete', [ImageTaskManagementController::class, 'bulkDestroy'])->name('labeler.tasks.bulk-delete');

        // UC 47: Gán nhãn dữ liệu hình ảnh & Quản lý Nhãn CVAT
        Route::get('/annotation', [ImageAnnotationController::class, 'index'])->name('labeler.annotation');
        Route::get('/annotation/images/{imageId}', [ImageAnnotationController::class, 'getAnnotations'])->name('labeler.annotation.get');
        Route::post('/annotation/save', [ImageAnnotationController::class, 'saveAnnotations'])->name('labeler.annotation.save');
        Route::post('/annotation/labels/store', [ImageAnnotationController::class, 'storeLabel'])->name('labeler.annotation.labels.store');
        Route::post('/annotation/labels/update/{id}', [ImageAnnotationController::class, 'updateLabel'])->name('labeler.annotation.labels.update');
        Route::post('/annotation/labels/delete/{id}', [ImageAnnotationController::class, 'deleteLabel'])->name('labeler.annotation.labels.delete');

        // UC 49: Kiểm tra chéo hình ảnh gán nhãn (Review / Validation Mode)
        Route::get('/review', [ImageReviewController::class, 'index'])->name('labeler.review');
        Route::get('/review/job/{jobId}', [ImageReviewController::class, 'workspace'])->name('labeler.review.workspace');
        Route::post('/review/issue', [ImageReviewController::class, 'storeIssue'])->name('labeler.review.issue');
        Route::post('/review/issue/delete/{id}', [ImageReviewController::class, 'deleteIssue'])->name('labeler.review.issue.delete');
        Route::post('/review/stage', [ImageReviewController::class, 'updateStage'])->name('labeler.review.stage');
        Route::post('/review/finish/{jobId}', [ImageReviewController::class, 'finishReview'])->name('labeler.review.finish');

        // UC 50: Xuất tập dữ liệu ảnh gán nhãn (Dataset Export)
        Route::get('/export', [DatasetExportController::class, 'index'])->name('labeler.export');
        Route::post('/export/generate', [DatasetExportController::class, 'export'])->name('labeler.export.generate');
        Route::get('/export/download/{id}', [DatasetExportController::class, 'download'])->name('labeler.export.download');

        // UC 51: Gán nhãn dữ liệu chatbot / văn bản NLP
        Route::get('/text', [TextAnnotationController::class, 'index'])->name('labeler.text');
        Route::get('/text/workspace/{documentId}', [TextAnnotationController::class, 'workspace'])->name('labeler.text.workspace');
        Route::post('/text/task/store', [TextAnnotationController::class, 'storeTask'])->name('labeler.text.task.store');
        Route::post('/text/document/store', [TextAnnotationController::class, 'storeDocument'])->name('labeler.text.document.store');
        Route::post('/text/annotations/save', [TextAnnotationController::class, 'saveAnnotations'])->name('labeler.text.annotations.save');
        Route::post('/text/documents/delete', [TextAnnotationController::class, 'deleteDocuments'])->name('labeler.text.documents.delete');
        Route::post('/text/annotations/delete', [TextAnnotationController::class, 'deleteAnnotations'])->name('labeler.text.annotations.delete');

        // UC 52: Xuất tập dữ liệu văn bản gán nhãn (Text Dataset Export)
        Route::get('/text/export', [TextDatasetExportController::class, 'index'])->name('labeler.text.export');
        Route::post('/text/export/generate', [TextDatasetExportController::class, 'export'])->name('labeler.text.export.generate');

        // UC 53: Quản lý cơ sở tri thức chatbot (RAG Knowledge Base & Chunking)
        Route::get('/knowledge', [KnowledgeBaseController::class, 'index'])->name('labeler.knowledge');
        Route::post('/knowledge/base/store', [KnowledgeBaseController::class, 'storeBase'])->name('labeler.knowledge.base.store');
        Route::post('/knowledge/base/delete/{id}', [KnowledgeBaseController::class, 'destroyBase'])->name('labeler.knowledge.base.delete');
        Route::post('/knowledge/document/store', [KnowledgeBaseController::class, 'storeDocument'])->name('labeler.knowledge.document.store');
        Route::post('/knowledge/document/update/{id}', [KnowledgeBaseController::class, 'updateDocument'])->name('labeler.knowledge.document.update');
        Route::post('/knowledge/document/delete/{id}', [KnowledgeBaseController::class, 'destroyDocument'])->name('labeler.knowledge.document.delete');
        Route::get('/knowledge/document/{id}/chunks', [KnowledgeBaseController::class, 'getChunks'])->name('labeler.knowledge.document.chunks');
    });
});
