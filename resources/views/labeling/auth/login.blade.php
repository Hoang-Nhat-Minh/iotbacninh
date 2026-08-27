<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Cổng Data Labeler - AI Labeling Subsystem</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

    <style>
        :root {
            --font-base: 'Be Vietnam Pro', sans-serif;
            --ai-primary: #4f46e5;
            --ai-primary-dark: #4338ca;
            --ai-accent: #8b5cf6;
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #eef2ff 50%, #f5f3ff 100%);
        }

        body {
            font-family: var(--font-base);
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }

        .labeler-auth-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            padding: 40px 32px;
        }

        .labeler-brand-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--ai-primary), var(--ai-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #ffffff;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.3);
        }

        .form-control,
        .input-group-text {
            border-color: #e2e8f0 !important;
            color: #1e293b !important;
        }

        .form-control:focus {
            border-color: var(--ai-primary) !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1) !important;
        }

        .btn-ai-submit {
            background: linear-gradient(135deg, var(--ai-primary), var(--ai-accent));
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 12px;
            border-radius: 12px;
            transition: all 0.25s ease;
        }

        .btn-ai-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
            color: #ffffff;
        }

        .btn-back-main {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s ease;
        }

        .btn-back-main:hover {
            color: #1e293b;
        }
    </style>
</head>

<body>

    <div class="labeler-auth-card">
        <div class="text-center mb-4">
            <div class="labeler-brand-icon">
                <i class="bi bi-tags-fill"></i>
            </div>
            <h4 class="fw-bold mb-1">Cổng Data Labeler AI</h4>
            <p class="text-muted-labeler small mb-0">Đăng nhập dành cho Quản trị viên & Nhà quản lý AI Labeling</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-center gap-2"
                style="font-size: 13.5px; border-radius: 10px;">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form action="{{ url('/labeler/login') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-semibold">Tên đăng nhập hoặc SĐT <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person text-muted-labeler"></i></span>
                    <input type="text" name="username" class="form-control"
                        placeholder="Ví dụ: admin hoặc 0987654321" value="{{ old('username') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-muted-labeler"></i></span>
                    <input type="password" name="password" id="labeler-password" class="form-control"
                        placeholder="••••••••" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()"
                        style="border-color: #e2e8f0; color: #64748b;">
                        <i class="bi bi-eye" id="icon-pass"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember-labeler"
                        style="border-color: #cbd5e1;">
                    <label class="form-check-label small" for="remember-labeler" style="color: #64748b;">Ghi nhớ tài
                        khoản</label>
                </div>
            </div>

            <button type="submit" class="btn btn-ai-submit w-100 mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Đăng Nhập Vào Data Labeler Workspace
            </button>
        </form>

        <div class="text-center pt-3 border-top" style="border-color: #e2e8f0 !important;">
            <a href="{{ url('/login') }}" class="btn-back-main d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Quay lại cổng đăng nhập hệ thống chính
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passInput = document.getElementById('labeler-password');
            const icon = document.getElementById('icon-pass');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                passInput.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>

</body>

</html>
