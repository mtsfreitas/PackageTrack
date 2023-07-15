<?php
include '../modulo/db_config.php';

// Iniciando a sessão
session_start();

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$client_email = $_SESSION['client_email'];
$tracking_code = $_SESSION['tracking_code'];
$status = $_POST['new_status'];
$origin_region = $_POST['origin_region'];  // nova variável para armazenar a região de origem do pedido

if ($status == "" && $origin_region == "") {
    exit("Nenhuma alteração feita. Selecione o novo status ou a região de origem para continuar.");
}

// Configurando os dias conforme o status selecionado
$days = null;
switch ($status) {
    case "Pedido feito.":
        $days = 0;
        break;
    case "Enviado à transportadora.":
        $days = 2;
        break;
    case "Em trânsito.":
        $days = 4;
        break;
    case "Entregue.":
        $days = 38;
        break;
    default:
        $status = null;
}

$sql = "UPDATE tracking_codes 
        INNER JOIN clients ON tracking_codes.client_id = clients.id 
        SET order_status = COALESCE(?, order_status), created_at = COALESCE(DATE_SUB(NOW(), INTERVAL ? DAY), created_at), from_brazil = COALESCE(NULLIF(?, ''), from_brazil)
        WHERE clients.email = ? AND tracking_codes.code = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sisss", $status, $days, $origin_region, $client_email, $tracking_code);

if ($stmt->execute() === TRUE) {
    echo "Status do código de rastreio atualizado com sucesso!";
} else {
    echo "Erro ao atualizar o status do código de rastreio: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
