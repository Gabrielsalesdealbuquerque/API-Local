<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="estilo.css">
    <title>Autenticação de API</title>
</head>
<body>
    <h1>Autenticação de API</h1>

    <!-- Formulário de login -->
    <form id="loginForm" class="cont-form">
        <label for="username">Usuário:</label>
        <input type="text" id="username" name="username" style="border-radius: 5px; height: 20px;"><br>

        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" style="border-radius: 5px; height: 20px;"><br>

        <button type="submit" style="border-radius: 5px; height: 20px;">Consultar</button>
    </form>

    <!-- Div para exibir a resposta da API -->
    <div id="response" class="container">
        <h1>Resposta da consulta</h1>
        <pre class="response"></pre>
        <div id="productContainer"></div>

        <form id="profileImageForm" enctype="multipart/form-data">
            <input type="file" id="profileImageInput" name="profileImage">
            <button type="submit">Enviar</button>
        </form>
        <div id="responseDiv1"></div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const responseDiv = document.querySelector('#response .response');
        const responseDiv1 = document.getElementById('responseDiv1');

        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // Obter os valores digitados pelo usuário
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            // Concatenar usuário e senha em uma string
            const credentials = `${username}:${password}`;

            // Codificar as credenciais para o formato Base64
            const encodedCredentials = btoa(credentials);

            function createProductElements(products) {
                const productContainer = document.getElementById('productContainer');

                products.forEach(product => {
                    const imgElement = document.createElement('img');
                    imgElement.src = product.url;
                    imgElement.alt = product.name;
                    imgElement.classList.add('product-image');

                    const urlElement = document.createElement('p');
                    urlElement.textContent = 'URL da imagem: ' + product.url;

                    const infoElement = document.createElement('p');
                    infoElement.textContent = product.informacao;

                    const productWrapper = document.createElement('div');
                    productWrapper.appendChild(imgElement);
                    productWrapper.appendChild(infoElement);
                    productWrapper.appendChild(urlElement);

                    productContainer.appendChild(productWrapper);
                });
            }

            // Fazer uma solicitação HTTP para a API de usuários
            fetch('http://localhost/api-php/api.php/?endpoint=users', {
                headers: {
                    'Authorization': `Basic ${encodedCredentials}` // Incluir as credenciais no cabeçalho da solicitação
                }
            })
            .then(response => response.json()) // Converter a resposta em JSON
            .then(data => {
                createProductElements(data.users);
            })
            .catch(error => {
                responseDiv.textContent = 'Erro ao fazer a solicitação: ' + error.message; // Exibir mensagem de erro
            });

            // Fazer uma solicitação HTTP para a API de produtos
            fetch('http://localhost/api-php/api.php/?endpoint=products', {
                headers: {
                    'Authorization': `Basic ${encodedCredentials}` // Incluir as credenciais no cabeçalho da solicitação
                }
            })
            .then(response => response.json()) // Converter a resposta em JSON
            .then(data => {
                createProductElements(data.products);
            })
            .catch(error => {
                responseDiv.textContent = 'Erro ao fazer a solicitação: ' + error.message; // Exibir mensagem de erro
            });
        });

        const profileImageForm = document.getElementById('profileImageForm');
        const profileImageInput = document.getElementById('profileImageInput');

        profileImageForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const file = profileImageInput.files[0];

            const formData = new FormData();
            formData.append('profileImage', file);

            fetch('http://localhost/api-php/api.php/?endpoint=users/profile_image', {
                headers: {
                    'Authorization': `Basic ${encodedCredentials}` // Incluir as credenciais no cabeçalho da solicitação
                },
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Exiba a URL da imagem de perfil atualizada
                    const imageUrl = data.imageUrl;
                    const imageElement = document.createElement('img');
                    imageElement.src = imageUrl;
                    responseDiv1.innerHTML = '';
                    responseDiv1.appendChild(imageElement);
                } else {
                    responseDiv1.textContent = 'Erro ao enviar a imagem: ' + data.message;
                }
            })
            .catch(error => {
                responseDiv1.textContent = 'Erro ao fazer a solicitação: ' + error.message;
            });
        });
    </script>
</body>
</html>
