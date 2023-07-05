<!DOCTYPE html>
<html>
<head>
    <title>Painel Administrativo</title>
</head>
<body>
    <h1>Registrar/Deletar Código de Rastreio</h1>
    <form action="register_delete_tracking.php" method="post">
        Email do Cliente: <input type="email" name="client_email"><br>
        Código de Rastreio: <input type="text" name="tracking_code"><br>
        <input type="submit" name="action" value="Registrar">
        <input type="submit" name="action" value="Deletar" onclick="return confirm('Tem certeza de que deseja deletar este código de rastreio?');">
    </form>
</body>
</html>
