<?php
    include '../modulo/db_config.php';
    // Iniciando a sessão
    session_start();
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Conexão com o banco de dados
    $conn = new mysqli($host, $user, $pass, $db);
    
    // Verificando se a conexão foi bem sucedida
    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }
    
    // Criando a consulta SQL
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);
    
    // Verificando se as credenciais são válidas
    if ($result->num_rows > 0) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header("Location: registro.php"); // Redirecionando para a página de registro
    } else {
        echo "Nome de usuário ou senha incorretos!";
    }
    
    // Encerrando a conexão
    $conn->close();
?>
