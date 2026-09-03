@php
    $footerText =
        \App\Models\Account\SystemSetting::getValue('copyright') ??
        (\App\Models\Account\SystemSetting::getValue('system_name')
            ? \App\Models\Account\SystemSetting::getValue('system_name') . ' &copy; ' . date('Y') . '.'
            : 'Hệ Thống IoT & Cảnh Báo Sâu Bệnh Nông Nghiệp Tỉnh Bắc Ninh &copy; ' . date('Y') . '.');
@endphp
<footer class="text-center py-3 border-top mt-auto"
    style="background-color: #ffffff; color: var(--text-muted); font-size: 13px;">
    <span>{!! $footerText !!}</span>
</footer>
