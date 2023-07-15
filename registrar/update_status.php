<?php
include '../modulo/db_config.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$codigo = $_GET['codigo'];

$query = "SELECT created_at, from_brazil FROM tracking_codes WHERE masked_code = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Adicionando o timezone ao objeto DateTime
    $created_at = new DateTime($row['created_at'], new DateTimeZone('UTC'));
    $from_brazil = $row['from_brazil'];
    $now = new DateTime(null, new DateTimeZone('UTC'));

    // Se for fim de semana, retroceda para a última sexta-feira
    $weekday = $now->format('N');
    if ($weekday == 6) {
        // Sábado: retroceda um dia
        $now->modify('-1 day');
    } elseif ($weekday == 7) {
        // Domingo: retroceda dois dias
        $now->modify('-2 days');
    }

    $diff = $created_at->diff($now)->days;

    if ($diff == 0) {
        echo "Pedido feito.";
    } else if ($diff >= 2 && $diff < 4) {
        echo "Enviado à transportadora.";
    } else if ($diff >= 4 && $diff < (($from_brazil == 1) ? 14 : 28)) {
        echo "Em trânsito.";
    } else if ($diff >= (($from_brazil == 1) ? 14 : 28)) {
        echo "Entregue.";
    }
} else {
    echo "Código de rastreamento não encontrado.";
}

$stmt->close();
$conn->close();
?>