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

        // Aqui está a nova lógica para fazer a solicitação POST
        $url = 'https://api.17track.net/track/v2/register';
        $headers = [
            '17token: E2C0856D641F44DE07AE3BAE2F69D2B2',
            'Content-Type: application/json'
        ];
        $data = json_encode([
            [
                "number" => $tracking_code
            ]
        ]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        #update curl.cainfo
        #curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        #curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        if($response === false) {
            echo 'Curl error: ' . curl_error($curl);
        }
     
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($statusCode == 200) {
            echo "Código de rastreio registrado na API com sucesso!";
        } else {
            echo "A solicitação falhou com o status $statusCode";
        }

        curl_close($curl);
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
