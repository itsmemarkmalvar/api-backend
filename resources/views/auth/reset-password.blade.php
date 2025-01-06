<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - BiniBaby</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #FFB6C1, #E6E6FA, #98FB98);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #4A90E2;
            border-color: #4A90E2;
        }
        .btn-primary:hover {
            background-color: #357ABD;
            border-color: #357ABD;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Reset Password</h2>
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="password_confirmation" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Reset Password</button>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <p>Want to use the BiniBaby app instead?</p>
                            <!-- Primary deep link for Android -->
                            <a href="intent://reset-password?token={{ $token }}&email={{ urlencode($email) }}#Intent;scheme=binibaby;package=com.binibaby.app;S.browser_fallback_url=https://binibaby-api.com/password/reset/{{ $token }}?email={{ urlencode($email) }};end" 
                               class="btn btn-outline-primary mb-2">
                                Open in BiniBaby App
                            </a>
                            
                            <!-- iOS Universal Link backup -->
                            <a href="binibaby://reset-password?token={{ $token }}&email={{ urlencode($email) }}" 
                               class="btn btn-outline-primary mb-2 d-none ios-link">
                                Open in BiniBaby App
                            </a>

                            <script>
                                // Simple iOS detection
                                if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
                                    document.querySelector('.ios-link').classList.remove('d-none');
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 