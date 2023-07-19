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
$from_brazil = isset($_POST['from_brazil']) ? 1 : 0;
$action = $_POST['action'];

// Conexão com o banco de dados
$conn = new mysqli($host, $user, $pass, $db);

// Verificando se a conexão foi bem sucedida
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

function seeded_shuffle($str, $seed){
    $str_array = str_split($str);
    srand($seed);
    shuffle($str_array);
    return implode('', $str_array);
}

// Dependendo da ação, executar código diferente
switch($action) {
    case 'Registrar':
        if (empty($_POST['tracking_code'])) {
            echo "O campo de código de rastreio está vazio.";
            exit;
        }
        // Código para registrar aqui
        $seed = crc32($_POST['tracking_code']);
        $masked_code = seeded_shuffle($_POST['tracking_code'], $seed);

        // Verificar se o código de rastreamento já está registrado
        $sql = "SELECT id FROM tracking_codes WHERE code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $_POST['tracking_code']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "O código de rastreio já está registrado no banco de dados.";
            $stmt->close();
            $conn->close();
            exit;
        }

        // Verificar se o código de rastreamento mascarado já está registrado
        $sql = "SELECT id FROM tracking_codes WHERE masked_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $masked_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "O código de rastreio já está registrado no banco de dados.";
            $stmt->close();
            $conn->close();
            exit;
        }

        $sql = "SELECT id FROM clients WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $_POST['client_email']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $client_id = $row['id'];
        } else {
            $sql = "INSERT INTO clients (email) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $_POST['client_email']);
            if ($stmt->execute() === TRUE) {
                $client_id = $stmt->insert_id;
            } else {
                echo "Erro ao inserir novo cliente: " . $stmt->error;
                $stmt->close();
                $conn->close();
                exit;
            }
        }

        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        $now = $now->format('Y-m-d H:i:s');
        $sql = "INSERT INTO tracking_codes (client_id, code, masked_code, from_brazil, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ississ", $client_id, $_POST['tracking_code'], $masked_code, $from_brazil, $now, $now);


        if ($stmt->execute() === TRUE) {
            echo "Código de rastreio registrado no banco de dados com sucesso!";
        } else {
            echo "Erro ao registrar código de rastreio: " . $stmt->error;
        }
    break;     
    case 'Editar':
        // Verificando se o código de rastreio associado ao e-mail do cliente existe
        $_SESSION['client_email'] = $client_email;
        $_SESSION['tracking_code'] = $tracking_code;
    
        $sql = "SELECT clients.email, tracking_codes.code, tracking_codes.masked_code FROM tracking_codes 
            JOIN clients ON tracking_codes.client_id = clients.id 
            WHERE clients.email = ? AND (tracking_codes.code = ? OR tracking_codes.masked_code = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $client_email, $tracking_code, $tracking_code);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            // Se o código de rastreio existir, redirecionar o usuário para a página de edição
            header('Location: edit_tracking_form.php');
        } else {
            echo "Não foi encontrado o código de rastreio associado ao e-mail do cliente.";
        }
        break;        
    case 'Deletar':
        // Deletando o código de rastreio
        $sql = "DELETE tracking_codes FROM tracking_codes INNER JOIN clients ON tracking_codes.client_id = clients.id WHERE clients.email = ? AND tracking_codes.code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $_POST['client_email'], $_POST['tracking_code']);

        if ($stmt->execute() === TRUE) {
            if ($stmt->affected_rows > 0) {
                echo "Código de rastreio deletado com sucesso!";
            } else {
                echo "O código de rastreio fornecido não está associado ao email do cliente.";
            }
        } else {
            echo "Erro ao deletar código de rastreio: " . $stmt->error;
        }
        break;
    case 'Buscar':
        if (!empty($tracking_code) && empty($client_email)) {
            // Se apenas o campo do código estiver preenchido
            $sql = "SELECT clients.email FROM clients
                    INNER JOIN tracking_codes
                    ON clients.id = tracking_codes.client_id
                    WHERE tracking_codes.code = ? OR tracking_codes.masked_code = ?";
    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $tracking_code, $tracking_code);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "Email: " . $row["email"]. "<br>";
                }
            } else {
                echo "Nenhum email encontrado para o código de rastreio fornecido.";
            }
        } elseif (!empty($client_email) && empty($tracking_code)) {
            // Se apenas o campo do email estiver preenchido
            $sql = "SELECT clients.email, tracking_codes.code, tracking_codes.from_brazil, 
                            tracking_codes.masked_code, tracking_codes.created_at,  tracking_codes.updated_at, tracking_codes.status_days
                    FROM clients
                    INNER JOIN tracking_codes
                    ON clients.id = tracking_codes.client_id
                    WHERE clients.email = ?";
    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $client_email);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "Email: " . $row["email"]. "<br>";
                    echo "Código de Rastreio: " . $row["code"]. "<br>";
                    echo "Produto do Brasil: " . ($row["from_brazil"] ? "Sim" : "Não"). "<br>";
                    echo "Código Mascarado: " . $row["masked_code"]. "<br>";
                    echo "Data de Criação: " . $row["created_at"]. "<br>";
                    echo "Data de Edição: " . $row["updated_at"]. "<br>";
                    echo "Valor de status: " . $row["status_days"]. "<br>";
                    echo "-------------------<br>";
                }
            } else {
                echo "Nenhum resultado encontrado para o email fornecido.";
            }
        } else {
            echo "Você deve preencher apenas o campo do código ou o campo do email.";
        }
        break;
           
}

// Encerrando a conexão
$stmt->close();
$conn->close();
?>
