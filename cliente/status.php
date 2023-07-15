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

$status = "";

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $created_at = new DateTime($row['created_at']);
    $from_brazil = $row['from_brazil'];
    $now = new DateTime();

    $interval = $created_at->diff($now);
    $days = $interval->days;

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

    $estimated_delivery_days = ($from_brazil == 1) ? 14 : 28;
    $remaining_delivery_days = $estimated_delivery_days - $days;
    
    if ($remaining_delivery_days <= 0) {
        $delivery_message = "Sua encomenda chegou";
    } else {
        $delivery_message = "O seu pedido chegará aproximadamente em até " . $remaining_delivery_days . " dias úteis.";
    }

    if ($days >= 0 && $days < 2) {
        $status = "Pedido feito";
    } else if ($days >= 2 && $days < 4) {
        $status = "Enviado à transportadora";
    } else if ($days >= 4 && $days < $estimated_delivery_days) {
        $status = "Em trânsito";
    } else if ($days >= $estimated_delivery_days) {
        $status = "Entregue";
    }

} else {
    echo "Código de rastreamento não encontrado.";
    exit();
}

$stmt->close();
$conn->close();

$statusList = array("Pedido feito", "Enviado à transportadora", "Em trânsito", "Entregue");
?>

<!DOCTYPE html>
<html>
<head>
<style>
    .status-list {
        list-style-type: none;
        padding: 0;
        margin: 0;
        position: relative;
    }

    .status-list:before {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        left: 11px;
        border-left: 2px dashed grey; /* linha tracejada */
        z-index: 0;
    }

    .status-item {
        margin: 30px 0; /* aumenta a distância entre os quadrados */
        padding-left: 30px;
        position: relative;
        z-index: 1;
    }

    .status-item .square {
        width: 20px;
        height: 20px;
        border: 2px solid grey;
        position: absolute;
        background-color: white;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
    }

    .status-item.active .square, .status-item.completed .square {
        background-color: cyan;
    }

    .status-item .square svg {
        /* Adicione estilos para o SVG aqui */
    }
</style>

</head>
<body>
    <ul class="status-list">
    <?php
    foreach ($statusList as $statusItem) {
        $class = "";
        if ($status == $statusItem) {
            $class = "active completed";
        } else if (array_search($status, $statusList) > array_search($statusItem, $statusList)) {
            $class = "completed";
        }

        echo '<li class="status-item ' . $class . '">';
        echo '<div class="square">';
        if ($class == "active" || $class == "completed") {
            echo '<svg><!-- Insira o conteúdo do arquivo icon2.svg aqui --></svg>';
        }
        echo '</div>';
        echo '<span>' . $statusItem . '</span>';
        echo '</li>';
    }
    ?>
    <!-- Adicione este código HTML após o bloco PHP que cria a lista de status do pedido -->
    <li class="status-item">
        <span><?php echo $delivery_message; ?></span>
    </li>
    </ul>
</body>
</html>
