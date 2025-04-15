<!DOCTYPE html>
<html>
<head>
    <title>Início - BFF Demo</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <nav>
        <?php foreach ($menu as $item): ?>
            <a href="<?= htmlspecialchars($item['url']) ?>">
                <?= htmlspecialchars($item['texto']) ?>
            </a> |
        <?php endforeach; ?>
    </nav>

    <h1>Bem-vindo ao BFF Demo</h1>

    <div>
        <h2>Status da Autenticação</h2>
        <?php if (isset($_SESSION['jwt'])): ?>
            <p style="color: green;">✓ Usuário autenticado</p>
            <p>Token JWT disponível na sessão</p>
        <?php else: ?>
            <p style="color: red;">✗ Usuário não autenticado</p>
            <p>Necessário fazer login</p>
        <?php endif; ?>
    </div>

    <div>
        <h2>Endpoints Disponíveis</h2>
        <ul>
            <li><a href="/produtos">Lista de Produtos</a></li>
            <li><a href="/usuarios">Lista de Usuários</a></li>
            <li><a href="/pedidos">Lista de Pedidos</a></li>
        </ul>
    </div>

    <div>
        <h2>Debug Info</h2>
        <pre>
Request URI: <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>
Session ID: <?= session_id() ?>
        </pre>
    </div>
</body>
</html>