<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'BFF Monitor' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Core CSS -->
    <link href="/assets/css/auth-monitor.css" rel="stylesheet">
    <link href="/assets/css/tabs.css" rel="stylesheet">
    <link href="/assets/css/component-details.css" rel="stylesheet">
    
    <!-- Estilos customizados adicionais -->
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link href="<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="/">
                <i class="bi bi-bounding-box"></i>
                BFF Monitor
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= isset($currentPage) && $currentPage === 'monitor' ? 'active' : '' ?>" href="/">
                            <i class="bi bi-graph-up"></i>
                            Monitor
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= isset($currentPage) && $currentPage === 'auth' ? 'active' : '' ?>" href="/auth">
                            <i class="bi bi-shield-check"></i>
                            Auth
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo -->
    <div class="container-fluid py-4 px-4">
        <?= $content ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core JS -->
    <script src="/assets/js/component-details.js"></script>
    
    <!-- Scripts customizados adicionais -->
    <?php if (isset($extraScripts) && is_array($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>