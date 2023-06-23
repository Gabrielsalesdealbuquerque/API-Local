<?php

// Defina o nome de usuário e senha permitidos
$usuarioPermitido = 'aa';
$senhaPermitida = 'aa';

// Verifica as credenciais fornecidas pelo cliente
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $usuarioPermitido || $_SERVER['PHP_AUTH_PW'] !== $senhaPermitida) {
    // Credenciais inválidas, retorna erro de autenticação
    header('HTTP/1.0 401 Unauthorized');
    echo 'Credenciais inválidas.';
    exit;
}

// Credenciais válidas, continuar com o processamento da API

// Defina o cabeçalho para permitir o acesso de outros domínios (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

// Verifique o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

// Verifique o endpoint solicitado
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Verifique os parâmetros da requisição
$params = $_GET;

// Defina a resposta padrão
$response = array(
    'status' => 'error',
    'message' => 'Invalid request'
);

// Verifique o método e o endpoint para executar a lógica da API
if ($method == 'GET') {
    if ($endpoint == 'users') {
        // Conecte-se ao banco de dados MySQL
        $servername = "localhost";
        $username = "teste";
        $password = "teste123";
        $dbname = "teste-api";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verifique se a conexão foi estabelecida com sucesso
        if ($conn->connect_error) {
            $response = array(
                'status' => 'error',
                'message' => 'Failed to connect to MySQL: ' . $conn->connect_error
            );
        } else {
            // Execute a consulta para obter os dados dos usuários
            $sql = "SELECT * FROM users";
            $result = $conn->query($sql);

            // Verifique se a consulta retornou resultados
            if ($result->num_rows > 0) {
                $users = array();

                // Itere pelos resultados e adicione os usuários ao array
                while ($row = $result->fetch_assoc()) {
                    $user = array(
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'profile_image' => $row['profile_image']
                    );

                    $users[] = $user;
                }

                $response = array(
                    'status' => 'success',
                    'users' => $users
                );
            } else {
                $response = array(
                    'status' => 'success',
                    'users' => []
                );
            }

            // Feche a conexão com o banco de dados
            $conn->close();
        }
    } elseif ($endpoint == 'products') {
        // Conecte-se ao banco de dados MySQL
        $servername = "localhost";
        $username = "teste";
        $password = "teste123";
        $dbname = "teste-api";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verifique se a conexão foi estabelecida com sucesso
        if ($conn->connect_error) {
            $response = array(
                'status' => 'error',
                'message' => 'Failed to connect to MySQL: ' . $conn->connect_error
            );
        } else {
            // Execute a consulta para obter os dados dos produtos
            $sql = "SELECT * FROM products";
            $result = $conn->query($sql);

            // Verifique se a consulta retornou resultados
            if ($result->num_rows > 0) {
                $products = array();

                // Itere pelos resultados e adicione os produtos ao array
                while ($row = $result->fetch_assoc()) {
                    $product = array(
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'url' => $row['url'],
                        'informacao' => $row['informacao']
                    );

                    $products[] = $product;
                }

                $response = array(
                    'status' => 'success',
                    'products' => $products
                );
            } else {
                $response = array(
                    'status' => 'success',
                    'products' => []
                );
            }

            // Feche a conexão com o banco de dados
            $conn->close();
        }
    }
} elseif ($method == 'POST' && isset($_FILES['profileImage'])) {
    $profileImage = $_FILES['profileImage'];

    // Verifique se houve algum erro ao enviar o arquivo
    if ($profileImage['error'] === UPLOAD_ERR_OK) {
        // Obtenha as informações necessárias do arquivo de imagem
        $fileName = $profileImage['name'];
        $tmpFilePath = $profileImage['tmp_name'];

        // Diretório de destino para salvar a imagem
        $destinationPath = __DIR__ . '/imagens/' . $fileName;

        // Move o arquivo para o diretório de destino
        if (move_uploaded_file($tmpFilePath, $destinationPath)) {
            // Atualize o campo de imagem no banco de dados
            $conn = new mysqli('localhost', 'teste', 'teste123', 'teste-api');

            if ($conn->connect_error) {
                die('Erro ao conectar-se ao banco de dados: ' . $conn->connect_error);
            }

            $userId = 1; // ID do usuário correspondente
            $fileNameInDb = 'imagens/' . $fileName; // Caminho relativo da imagem no banco de dados
            $sql = "UPDATE users SET profile_image = '$fileNameInDb' WHERE id = $userId";

            if ($conn->query($sql)) {
                $response = array(
                    'status' => 'success',
                    'message' => 'Imagem de perfil atualizada com sucesso.'
                );
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Erro ao atualizar a imagem de perfil: ' . $conn->error
                );
            }

            $conn->close();
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Erro ao mover o arquivo de imagem para o diretório de destino.'
            );
        }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Erro ao enviar o arquivo de imagem.'
        );
    }
}

// Enviar a resposta como JSON
header('Content-Type: application/json');
echo json_encode($response);

?>
