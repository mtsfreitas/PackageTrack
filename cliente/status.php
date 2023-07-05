<?php
// Verifica se o parâmetro 'codigo' está presente na URL
if (isset($_GET['codigo'])) {
    // Obtém o código de rastreamento da URL
    $codigo = $_GET['codigo'];

    // Faz a solicitação para a API
    $url = 'https://api.17track.net/track/v2/gettrackinfo';
    $headers = [
        '17token: E2C0856D641F44DE07AE3BAE2F69D2B2',
        'Content-Type: application/json'
    ];
    $data = json_encode([['number' => $codigo]]);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if ($response === false) {
        echo 'Falha na solicitação: ' . curl_error($ch);
    } else {
        $responseData = json_decode($response, true);

        if ($responseData['code'] == 0 && isset($responseData['data']['accepted'][0]['track_info']['milestone'])) {
            $milestones = $responseData['data']['accepted'][0]['track_info']['milestone'];

            echo "[ x ] Pedido feito";

            foreach ($milestones as $milestone) {
                $status = $milestone['key_stage'];

                if ($status == 'InfoReceived') {
                    echo " ---> [ x ] Enviado";
                } elseif ($status == 'PickedUp') {
                    echo " ---> [ x ] Em trânsito";
                } elseif ($status == 'Delivered') {
                    echo " ---> [ x ] Entregue";
                }
            }
        } else {
            echo "Não foi possível obter informações de rastreamento para o código informado.";
        }
    }

    curl_close($ch);
} else {
    echo 'Nenhum código de rastreamento fornecido na URL.';
}
?>
