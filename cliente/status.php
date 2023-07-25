<?php

include '../modulo/db_config.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$codigo = $_GET['codigo'];

$query = "SELECT created_at, updated_at, from_brazil, status_days FROM tracking_codes WHERE masked_code = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();

$status = "";

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $created_at = new DateTime($row['created_at']);
    $updated_at = new DateTime($row['updated_at']);
    $today = new DateTime();

    // Cálculo dos dias úteis
    $interval = $today->diff($created_at);
    $daysDifference = $interval->days;

    $weekendDays = 0;
    for ($i = 1; $i <= $daysDifference; $i++) {
        $currentDay = clone $created_at;
        $currentDay->modify("+$i day");
        $dayOfWeek = $currentDay->format('N'); // 1 (segunda-feira) a 7 (domingo)
        if ($dayOfWeek >= 6 || $dayOfWeek == 0) {
            $weekendDays++;
        }
    }
    $businessDays = $daysDifference - $weekendDays;
    $businessDays += $row['status_days'];

    $from_brazil = $row['from_brazil'];
    $estimated_delivery_days = ($from_brazil == 1) ? 14 : 28;
    $remaining_delivery_days = $estimated_delivery_days - $businessDays;

    if ($remaining_delivery_days <= 0) {
        $delivery_message = "Sua encomenda chegou". $businessDays;
    } else {
        $delivery_message = "O pedidao <strong>" . $codigo . "</strong> chegará aproximadamente em até " . $remaining_delivery_days . " dias úteis." . $businessDays;
    }

    if ($businessDays  >= 0 && $businessDays  < 2) {
        $status = "Pedido feito";
    } else if ($businessDays  >= 2 && $businessDays  < 4) {
        $status = "Enviado à transportadora";
    } else if ($businessDays  >= 4 && $businessDays  < $estimated_delivery_days) {
        $status = "Em trânsito";
    } else if ($businessDays  >= $estimated_delivery_days) {
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
    .icon {
        width: 100%;
        height: 65%;
        vertical-align: middle;
    }
    .container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh; /* Use a altura total da viewport */
        text-align: left;
    }

    .status-container {
        display: inline-block;
    }
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
        left: 10px;
        border-left: 1.5px dashed grey; /* linha tracejada */
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
    border: 1px solid grey;
    position: absolute;
    background-color: white;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    border-radius: 50%; /* adicionado para criar um círculo */
}


    .status-item.active .square, .status-item.completed .square {
        background-color: #4ec1b2;;
    }

    .status-item .square svg {
        /* Adicione estilos para o SVG aqui */
    }
</style>

</head>
<body>
<div class="container">
        <!-- Mova este código HTML para o topo da lista de status -->
        <li class="status-item">
            <span><?php echo $delivery_message; ?></span>
        </li>
        <div class="status-container">
            <ul class="status-list">
   
    <?php
    foreach ($statusList as $statusItem) {
        $class = "";
        if ($status == $statusItem) {
            $class = "active";
        } else if (array_search($status, $statusList) >= array_search($statusItem, $statusList)) {
            $class = "completed";
        }

        echo '<li class="status-item ' . $class . '">';
        echo '<div class="square">';
        if ($class == "active" || $class == "completed") {
            echo '<img src="icon.svg" alt="Ícone" class = "icon">';           
        }
        echo '</div>';
        echo '<span>' . $statusItem . '</span>';
        echo '</li>';
    }
    ?>
    </ul>
</div>
</div>
</body>
</html>