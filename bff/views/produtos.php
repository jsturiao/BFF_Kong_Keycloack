<!DOCTYPE html>
<html>
<head>
    <title>Produtos - BFF Demo</title>
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

    <h1>Lista de Produtos</h1>

    <?php if (isset($dados['data']['produtos'])): ?>
        <ul>
        <?php foreach ($dados['data']['produtos'] as $produto): ?>
            <li>
                <?= htmlspecialchars($produto['nome']) ?> - 
                R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Nenhum produto encontrado.</p>
        <pre><?php print_r($dados); ?></pre>
    <?php endif; ?>