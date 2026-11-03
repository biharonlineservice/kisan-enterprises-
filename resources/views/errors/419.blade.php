<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ app()->getLocale() === 'hi' ? 'सुरक्षा सत्र समाप्त — Kisan Enterprises' : 'Session Expired — Kisan Enterprises' }}</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#21864B">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        :root {
            --brand-green: #1f6f43;
            --brand-forest: #14462b;
            --brand-gold: #c9933b;
        }
        body {
            background: linear-gradient(135deg, #f4f7f4 0%, #eaf2eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
        }
        .error-card {
            max-width: 520px;
            width: 100%;
            background: #ffffff;
            border-radius: 1.25rem;
            border: 1px solid #d1e2d6;
            box-shadow: 0 20px 40px -15px rgba(20, 70, 43, 0.15);
            padding: 2.25rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #1f6f43, #c9933b);
        }
        .icon-bubble {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: #fef3c7;
            border: 2px solid #fde68a;
            color: #b45309;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 8px 16px -4px rgba(180, 83, 9, 0.15);
        }
        .code-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            margin-bottom: 0.75rem;
            letter-spacing: 0.05em;
        }
        .btn-brand-primary {
            background-color: var(--brand-green);
            border-color: var(--brand-green);
            color: #ffffff;
            font-weight: 600;
            padding: 0.65rem 1.25rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .btn-brand-primary:hover {
            background-color: var(--brand-forest);
            border-color: var(--brand-forest);
            color: #ffffff;
            transform: translateY(-1px);
        }
        .countdown-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.82rem;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            margin-top: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-bubble">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <div>
            <span class="code-badge">
                <i class="bi bi-clock-history"></i> HTTP 419 • {{ app()->getLocale() === 'hi' ? 'सत्र समाप्ति' : 'Session Expired' }}
            </span>
        </div>

        <h1 class="h4 fw-bold text-dark mb-2">
            {{ app()->getLocale() === 'hi' ? 'सुरक्षा सत्र समाप्त हो गया था' : 'Your Page Session Expired' }}
        </h1>

        <p class="text-muted small mb-4">
            {{ app()->getLocale() === 'hi'
                ? 'लंबे समय तक निष्क्रिय रहने या बैक बटन दबाने के कारण सुरक्षा टोकन पुराना हो गया था। आपका कोई डेटा नष्ट नहीं हुआ है — नया टोकन लोड हो चुका है।'
                : 'Your form session timed out due to inactivity or browser navigation. A fresh security token has been loaded automatically.' }}
        </p>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
            <a href="{{ route('login') }}" class="btn btn-brand-primary shadow-sm" id="btnContinue">
                <i class="bi bi-box-arrow-in-right me-1"></i>
                {{ app()->getLocale() === 'hi' ? 'पुनः लॉगिन करें' : 'Sign In Again' }}
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                <i class="bi bi-house me-1"></i>
                {{ app()->getLocale() === 'hi' ? 'मुख्य पृष्ठ' : 'Website Home' }}
            </a>
        </div>

        <div class="countdown-pill" id="countdownWrap">
            <div class="spinner-border spinner-border-sm text-success" role="status" style="width: 0.85rem; height: 0.85rem;"></div>
            <span>
                {{ app()->getLocale() === 'hi' ? 'स्वतः लॉगिन पेज पर ले जाया जा रहा है:' : 'Redirecting automatically in:' }}
                <strong id="timerSec" class="text-dark">3s</strong>
            </span>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center text-muted" style="font-size: 0.78rem;">
            <span>&copy; {{ date('Y') }} {{ setting('business.name', 'Kisan Enterprises') }}</span>
            <span><i class="bi bi-patch-check-fill text-success me-1"></i>{{ app()->getLocale() === 'hi' ? 'सुरक्षित पोर्टल' : 'Secure Portal' }}</span>
        </div>
    </div>

    <script>
    (function () {
        let sec = 3;
        const targetUrl = @json(route('login'));
        const timerEl = document.getElementById('timerSec');

        const interval = setInterval(function () {
            sec--;
            if (timerEl) timerEl.textContent = sec + 's';
            if (sec <= 0) {
                clearInterval(interval);
                window.location.replace(targetUrl);
            }
        }, 1000);
    })();
    </script>
</body>
</html>

