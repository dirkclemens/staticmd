<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\StaticMD\Core\I18n::getLanguage()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('admin.brand') ?> - <?= __('admin.login.signin') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ffffffff 0%, #c2c2c2ff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(45deg, #AC1200, #940f00ff);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-form {
            padding: 2rem;
        }
        
        .form-control:focus {
            border-color: #AC1200;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(45deg, #AC1200, #940f00ff);
            border: none;
            padding: 12px 0;
            font-weight: 600;
        }
        
        .btn-login:hover {
            background: linear-gradient(45deg, #AC1200, #940f00ff);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-container">
                    <div class="login-header">
                        <h3 class="mb-2">
                            <i class="bi bi-shield-lock me-2"></i>
                            <?= __('admin.brand') ?>
                        </h3>
                        <p class="mb-0"><?= __('admin.login.title') ?></p>
                    </div>
                    
                    <div class="login-form">
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['message']) && $_GET['message'] === 'logged_out'): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <?= __('admin.login.logged_out') ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="/admin?action=login">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->auth->generateCSRFToken()) ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-1"></i>
                                    <?= __('admin.login.username') ?>
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       required autocomplete="username" autofocus>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-1"></i>
                                    <?= __('admin.login.password') ?>
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required autocomplete="current-password">
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                <?= __('admin.login.signin') ?>
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="/" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>
                                <?= __('admin.login.back_to_site') ?>
                            </a>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>