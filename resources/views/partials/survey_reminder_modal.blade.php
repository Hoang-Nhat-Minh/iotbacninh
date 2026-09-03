@if (Auth::check() && Auth::user()->isUser() && !request()->routeIs('degree-days.surveys.*'))
    @php
        $shouldShowSurveyModal = false;
        $hasDoneSurvey = Auth::user()->degreeDaysSurveys()->exists();
        if (!$hasDoneSurvey) {
            if (session('show_survey_reminder')) {
                $shouldShowSurveyModal = true;
                session()->forget('show_survey_reminder');
                session()->put('survey_reminder_shown_in_session', true);
            } elseif (!session('survey_reminder_shown_in_session')) {
                $shouldShowSurveyModal = true;
                session()->put('survey_reminder_shown_in_session', true);
            }
        }
    @endphp

    @if ($shouldShowSurveyModal)
        <div class="app-modal" id="modal-survey-reminder">
            <div class="modal-dialog" style="max-width: 520px;">
                <div class="modal-header d-flex justify-content-between align-items-center p-3 border-bottom" style="background-color: var(--bg-body);">
                    <div class="d-flex align-items-center gap-2.5">
                        <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center"
                            style="width: 38px; height: 38px; font-size: 18px;">
                            <i class="bi bi-clipboard2-check-fill"></i>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" style="font-size: 16px;">Khảo Sát Thực Địa Nông Nghiệp</h5>
                            <small class="text-muted" style="font-size: 11.5px;">Cải thiện chất lượng hệ thống</small>
                        </div>
                    </div>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark mb-3" style="font-size: 14.5px; line-height: 1.6;">
                        Xin chào <strong>{{ Auth::user()->name }}</strong>, bạn chưa thực hiện khảo sát thực địa nào cho vùng trồng của mình.
                    </p>
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex align-items-start gap-2.5">
                            <i class="bi bi-stars text-warning fs-5 flex-shrink-0 mt-0.5"></i>
                            <div class="small text-muted" style="line-height: 1.5;">
                                Việc ghi nhận thông tin thực tế về sâu bệnh hại và cây trồng sẽ giúp hoàn thiện các mô hình dự báo AI, nâng cao độ chính xác cảnh báo và <strong>cải thiện chất lượng phục vụ của hệ thống</strong>.
                            </div>
                        </div>
                    </div>
                    <p class="text-secondary small mb-0">
                        Vui lòng dành ra 1 – 2 phút để hoàn thành phiếu khảo sát nhanh nhé!
                    </p>
                </div>
                <div class="modal-footer p-3 border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light btn-sm px-3 py-1.5 rounded-3 btn-modal-close text-secondary fw-semibold">
                        Để sau
                    </button>
                    <a href="{{ route('degree-days.surveys.index') }}" class="btn btn-primary btn-sm px-3.5 py-1.5 rounded-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm">
                        <i class="bi bi-pencil-square"></i> Làm Khảo Sát Ngay
                    </a>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (typeof openModal === 'function') {
                        openModal('modal-survey-reminder');
                    }
                }, 500);
            });
        </script>
    @endif
@endif
