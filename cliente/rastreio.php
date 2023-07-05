<?php
// Importa o arquivo de configuração do banco de dados
include '../modulo/db_config.php';

// Estabelece a conexão com o banco de dados
$conn = new mysqli($host, $user, $pass, $db);

// Verifica se a conexão foi bem sucedida
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Busca o ID do cliente para o e-mail fornecido
$email = $_POST['email'];
$query = "SELECT id FROM clients WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $client_id = $row['id'];

    // Busca os códigos de rastreamento para o cliente
    $query = "SELECT code FROM tracking_codes WHERE client_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Exibe os links para cada código de rastreamento
    while ($row = $result->fetch_assoc()) {
        echo "<a href='status.php?codigo=".$row['code']."'>".$row['code']."</a><br/>";
    }
} else {
    echo "Nenhum cliente encontrado com o e-mail fornecido.";
}

$stmt->close();
$conn->close();
?>
