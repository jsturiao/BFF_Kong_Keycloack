<!DOCTYPE html>
<html>
<head>
    <title>Usuários - BFF Demo</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <nav>
        <a href="/">Início</a> |
        <a href="/produtos">Produtos</a> |
        <a href="/usuarios">Usuários</a> |
        <a href="/pedidos">Pedidos</a> |
        <a href="/logout">Sair</a>
    </nav>

    <h1>Lista de Usuários</h1>

    <?php if (isset($dados['data']['usuarios'])): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
            </tr>
            <?php foreach ($dados['data']['usuarios'] as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum usuário encontrado.</p>
        <pre><?php print_r($dados); ?></pre>
    <?php endif; ?>
</body>
</html>