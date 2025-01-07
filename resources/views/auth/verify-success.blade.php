<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verified - BiniBaby</title>
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
            background: rgba(255, 255, 255, 0.95);
        }
        .btn-primary {
            background-color: #4A90E2;
            border-color: #4A90E2;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #357ABD;
            border-color: #357ABD;
        }
        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
        }
        .success-icon {
            font-size: 60px;
            color: #4BB543;
            margin-bottom: 20px;
        }
        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <div class="card-body text-center">
                        <img src="{{ asset('images/Logo.png') }}" alt="BiniBaby Logo" class="logo">
                        <span class="material-icons success-icon">check_circle</span>
                        <h2 class="mb-4">Email Verified Successfully!</h2>
                        
                        <p class="message mb-4">
                            Your email has been successfully verified. You can now use all features of the BiniBaby app.
                        </p>

                        <div class="mt-4">
                            <p class="mb-3">Open BiniBaby app to continue:</p>
                            <!-- Primary deep link for Android -->
                            <a href="intent://verified#Intent;scheme=binibaby;package=com.binibaby.app;end" 
                               class="btn btn-primary mb-2">
                                Open BiniBaby App
                            </a>
                            
                            <!-- iOS Universal Link backup -->
                            <a href="binibaby://verified" 
                               class="btn btn-primary mb-2 d-none ios-link">
                                Open BiniBaby App
                            </a>
                        </div>

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
</body>
</html> 