<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exemplo de Códigos de Rastreamento</title>
  <style>
    .floating-stack {
      background-color: white;
      color: #fff;
      height: 300px;
      overflow-y: auto;
      width: 320px;
      border-radius: 1rem;
      overflow-y: auto;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .floating-stack a {
      text-decoration: none;
      color: black;
    }

    .floating-stack a:hover {
      color: white;
    }

    .floating-stack>dl {
      margin: 0 0 1rem;
      display: grid;
      grid-template-columns: 2.5rem 1fr;
      align-items: center;
    }

    .floating-stack dd {
      grid-column: 2;
      margin: 0;
      padding: 0.75rem;
      text-align: center;
    }

    .floating-stack dd:hover {
      background-color: #AED6F1;
    }

    .floating-stack::-webkit-scrollbar {
      width: 8px;
    }

    .floating-stack::-webkit-scrollbar-thumb {
      border-radius: 100px;
      background: #8070d4;
      border: 6px solid rgba(0, 0, 0, 0.2);
    }

    .floating-stack>dl:first-of-type>dd:first-of-type {
      margin-top: 0.25rem;
    }

    body {
      background: rgb(2, 0, 36);
      background: linear-gradient(90deg, rgba(2, 0, 36, 1) 0%, rgba(9, 58, 121, 1) 35%, rgba(0, 212, 255, 1) 100%);
      color: white;
      height: 100vh;
      margin: 0;
      display: grid;
      place-items: center;
      font: 100%/1.4 system-ui;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="floating-stack">
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
        $query = "SELECT masked_code FROM tracking_codes WHERE client_id = ? ORDER BY id DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Exibe os links para cada código de rastreamento
        while ($row = $result->fetch_assoc()) {
          echo "<dd><a href='status.php?codigo=" . $row['masked_code'] . "'>" . $row['masked_code'] . "</a></dd>";
        }
      } else {
        echo "<dd>Nenhum cliente encontrado com o e-mail fornecido.</dd>";
      }

      $stmt->close();
      $conn->close();
      ?>
    </div>
  </div>
</body>

</html>