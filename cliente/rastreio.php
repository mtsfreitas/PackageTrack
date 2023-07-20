<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exemplo de Códigos de Rastreamento</title>
  <style>
    .floating-stack {
      position: relative; /* Adicionado */
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
      color: black;
    }

    .floating-stack dd:hover {
      background-color: #78ffb7;
    }

    .floating-stack::-webkit-scrollbar {
      width: 8px;
    }

    .floating-stack::-webkit-scrollbar-thumb {
      border-radius: 100px;
      background: #78ffb7;
      border: 6px solid rgba(0, 0, 0, 0.2);
    }

    .floating-stack>dl:first-of-type>dd:first-of-type {
      margin-top: 0.25rem;
    }

    body {
      background: rgb(84, 144, 112);
      background: linear-gradient(90deg, rgba(84, 144, 112, 1) 0%, rgba(4, 65, 58, 1) 100%);
      color: white;
      height: 100vh;
      margin: 0;
      display: grid;
      place-items: center;
      font: 100%/1.4 system-ui;
    }

    .message {
      text-align: center;
      margin-bottom: 1rem;
    }

    .loader-container {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 999; 
      display: none; 
    }

    .loader-container.active {
      display: flex; 
    }

    .loader {
      border: 16px solid #f3f3f3;
      border-top: 16px solid #78ffb7;
      border-radius: 50%;
      width: 120px;
      height: 120px;
      animation: spin 2s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }
  </style>
  <script>
    function redirectToStatusPage(code) {
      var loaderContainer = document.querySelector(".loader-container");
      loaderContainer.classList.add("active");
    
      // Verificar se a página está sendo descarregada
      window.onbeforeunload = function() {
        loaderContainer.classList.remove("active");
      };
    
      setTimeout(function () {
        window.location.href = "status.php?codigo=" + code;
      }, 2000);
    }
  </script>
</head>

<body>
  <div class="container">
    <h2 class="message">Rastreie seu pedido</h2>
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
          echo "<dd><a href='#' onclick=\"redirectToStatusPage('" . $row['masked_code'] . "')\">&#128230; " . $row['masked_code'] . "</a></dd>";
        }
      } else {
        echo "<dd>Por favor, aguarde alguns dias para que seus pedidos sejam atualizados ou verifique se digitou corretamente seu endereço de e-mail.</dd>";
      }

      $stmt->close();
      $conn->close();
      ?>
    </div>
  </div>
  <div class="loader-container"> <!-- Adicionado -->
    <div class="loader"></div> <!-- Adicionado -->
  </div>
</body>

</html>
