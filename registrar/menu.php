<!DOCTYPE html>
<html>
<head>
    <title>Painel Administrativo</title>
</head>
<body>
    <h1>Gerenciar Código de Rastreio</h1>
    <form action="manage_tracking.php" method="post">
        Email do Cliente: <input type="email" name="client_email"><br>
        Código de Rastreio: <input type="text" name="tracking_code"><br>
        Produto do Brasil: <input type="checkbox" name="from_brazil" value="1" checked><br>
        <input type="submit" name="action" value="Registrar">
        <input type="submit" name="action" value="Editar">
        <input type="submit" name="action" value="Deletar" onclick="return confirm('Tem certeza de que deseja deletar este código de rastreio?');">
    </form>
</body>
</html>
