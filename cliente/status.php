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

    $created_at = new DateTime($row['created_at']);
    $from_brazil = $row['from_brazil'];
    $now = new DateTime();

    $interval = $created_at->diff($now);
    $days = $interval->days;

    // Excluindo sábados e domingos do cálculo
    $remainingDays = $days;
    $weekendDays = 0;
    while ($remainingDays > 0) {
        $dayOfWeek = $created_at->format('N');
        if ($dayOfWeek >= 6) {
            $weekendDays++;
        }
        $created_at->modify('+1 day');
        $remainingDays--;
    }
    $days -= $weekendDays;

    if ($days >= 0 && $days < 2) {
        echo "Pedido feitoso";
    } else if ($days >= 2 && $days < 4) {
        echo "Enviado à transportadora.";
    } else if ($days >= 4 && $days < (($from_brazil == 1) ? 14 : 28)) {
        echo "Em trânsito.";
    } else if ($days >= (($from_brazil == 1) ? 14 : 28)) {
        echo "Entregue.";
    }
} else {
    echo "Código de rastreamento não encontrado.";
}

$stmt->close();
$conn->close();
?>
