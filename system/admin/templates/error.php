<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaticMD Admin - Fehler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(45deg, #AC1200, #940f00ff);
            height: 100vh;
            display: flex;
            align-items: center;
            color: white;
        }
        
        .error-container {
            text-align: center;
            max-width: 600px;
        }
        
        .error-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.8;
        }
        
        .error-details {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 2rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="error-container">
                    <div class="error-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    
                    <h1 class="display-4 mb-3">Oops! Ein Fehler ist aufgetreten</h1>
                    <p class="lead">Es tut uns leid, aber etwas ist schiefgelaufen.</p>
                    
                    <?php if (isset($e)): ?>
                    <div class="error-details">
                        <h5>Fehlerdetails:</h5>
                        <p class="mb-2"><strong>Nachricht:</strong> <?= htmlspecialchars($e->getMessage()) ?></p>
                        <p class="mb-0"><strong>Datei:</strong> <?= htmlspecialchars($e->getFile()) ?> (Zeile <?= $e->getLine() ?>)</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="/admin" class="btn btn-light btn-lg me-3">
                            <i class="bi bi-arrow-left me-2"></i>
                            Zurück zum Admin
                        </a>
                        <a href="/" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-house me-2"></i>
                            Zur Startseite
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>