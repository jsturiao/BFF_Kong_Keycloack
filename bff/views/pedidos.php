<!DOCTYPE html>
<html>
<head>
    <title>Pedidos - BFF Demo</title>
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

    <h1>Lista de Pedidos</h1>

    <?php if (isset($dados['data']['pedidos'])): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
            <?php foreach ($dados['data']['pedidos'] as $pedido): ?>
                <tr>
                    <td><?= htmlspecialchars($pedido['id']) ?></td>
                    <td><?= htmlspecialchars($pedido['cliente']) ?></td>
                    <td>R$ <?= number_format($pedido['total'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($pedido['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum pedido encontrado.</p>
        <pre><?php print_r($dados); ?></pre>
    <?php endif; ?>
</body>
</html>