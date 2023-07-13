<?php
$tracking_code = $_GET['codigo'];
$command = 'C:\\wamp64\\www\\dropshipping\\trackbox\\myenv\\Scripts\\python.exe C:\\wamp64\\www\\dropshipping\\trackbox\\cliente\\app.py ' . $tracking_code . ' 2>&1';
$output = shell_exec($command);
$decoded_output = json_decode($output, true);

$response = array();

if (json_last_error() === JSON_ERROR_NONE) {
    if (isset($decoded_output['status'])) {
        $response['status'] = $decoded_output['status'];
    } else {
        $response['error'] = 'O campo "status" não foi encontrado na saída. Saída completa: ' . print_r($decoded_output, true);
    }
} else {
    $response['error'] = 'A saída não é um JSON válido. Saída completa: ' . $output;
}

echo json_encode($response);
?>
