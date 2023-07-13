<?php
include '../modulo/db_config.php';
// Iniciando a sessão
session_start();

// Verificando se o usuário está logado
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.html");
    exit;
}

// Dados do formulário
$client_email = $_POST['client_email'];
$tracking_code = $_POST['tracking_code'];
$action = $_POST['action']; // Ação do formulário (Registrar ou Deletar)

// Conexão com o banco de dados
$conn = new mysqli($host, $user, $pass, $db);

// Verificando se a conexão foi bem sucedida
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if ($action === 'Registrar') {
    // Verificando se o cliente já existe no banco de dados
    $sql = "SELECT id FROM clients WHERE email = '$client_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Obtendo o ID do cliente existente
        $row = $result->fetch_assoc();
        $client_id = $row['id'];
    } else {
        // Inserindo um novo cliente
        $sql = "INSERT INTO clients (email) VALUES ('$client_email')";
        if ($conn->query($sql) === TRUE) {
            // Obtendo o ID do novo cliente
            $client_id = $conn->insert_id;
        } else {
            echo "Erro ao inserir novo cliente: " . $conn->error;
            $conn->close();
            exit;
        }
    }

    // Registrando o código de rastreio
    $sql = "INSERT INTO tracking_codes (client_id, code) VALUES ($client_id, '$tracking_code')";

    if ($conn->query($sql) === TRUE) {
        echo "Código de rastreio registrado no banco de dados com sucesso!";
    } else {
        echo "Erro ao registrar código de rastreio: " . $conn->error;
    }
} else if ($action === 'Deletar') {
    // Deletando o código de rastreio
    $sql = "DELETE tracking_codes FROM tracking_codes INNER JOIN clients ON tracking_codes.client_id = clients.id WHERE clients.email = '$client_email' AND tracking_codes.code = '$tracking_code'";

    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo "Código de rastreio deletado com sucesso!";
        } else {
            echo "O código de rastreio fornecido não está associado ao email do cliente.";
        }
    } else {
        echo "Erro ao deletar código de rastreio: " . $conn->error;
    }
}

// Encerrando a conexão
$conn->close();
?>
