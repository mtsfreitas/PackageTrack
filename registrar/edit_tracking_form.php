<!DOCTYPE html>
<html>
<head>
    <title>Editar Status do Código de Rastreio</title>
</head>
<body>
    <h1>Editar Status do Código de Rastreio</h1>
    <form action="update_status.php" method="post">
        <!-- Campos ocultos para o email e o código de rastreio -->
        <input type="hidden" name="client_email" value="<?php echo isset($_SESSION['client_email']) ? $_SESSION['client_email'] : ''; ?>">
        <input type="hidden" name="tracking_code" value="<?php echo isset($_SESSION['tracking_code']) ? $_SESSION['tracking_code'] : ''; ?>">
        Novo Status: 
        <select name="new_status">
            <option value="">Nenhum</option>
            <option value="Pedido feito.">Pedido feito.</option>
            <option value="Enviado à transportadora.">Enviado à transportadora.</option>
            <option value="Em trânsito.">Em trânsito.</option>
            <option value="Entregue.">Entregue.</option>
        </select><br>
        Região de Origem: 
        <select name="origin_region">
            <option value="">Nenhum</option>
            <option value="1">Brasil</option>
            <option value="0">China</option>
        </select><br>
        <input type="submit" value="Atualizar">
    </form>
</body>
</html>
