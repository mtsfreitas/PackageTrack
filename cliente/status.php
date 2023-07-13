<!DOCTYPE html>
<html>
    <head>
        <title>Status do Pacote</title>
        <style>
           #loading {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                right: 0;
                margin: auto;
                display: block;
                width: 100px;   /* Ajuste esses valores para as dimensões do seu GIF */
                height: 100px;  /* Ajuste esses valores para as dimensões do seu GIF */
            }
        </style>
    </head>
    <body>
        <h1>Status do Pacote</h1>
        <div id="status"></div>
        <img id="loading" src="loading.gif" alt="Loading..." />

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script>
            $(document).ready(function() {
                $.ajax({
                    url: 'status_processing.php?codigo=<?php echo $_GET['codigo']; ?>',
                    type: 'GET',
                    success: function(result) {
                        $("#loading").hide();
                        var response = JSON.parse(result);
                        if(response.error) {
                            $("#status").html(response.error);
                        } else {
                            $("#status").html('Status do pacote: ' + response.status);
                        }
                    },
                    error: function() {
                        $("#loading").hide();
                        $("#status").html('Erro ao buscar o status do pacote.');
                    }
                });
            });
        </script>
    </body>
</html>
