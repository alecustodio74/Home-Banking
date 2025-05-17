<?php
// registrar.php
require_once("conexao.php");

// Inicia a sessão
session_start();

// Verifica se o usuário já está logado
if (isset($_SESSION['cliente_id'])) {
    header("Location: index.php"); // Redireciona para a página inicial se já estiver logado
    exit();
}

$mensagem_erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim(htmlspecialchars($_POST['nome'], ENT_QUOTES, 'UTF-8'));
    $cpf = trim(htmlspecialchars($_POST['cpf'], ENT_QUOTES, 'UTF-8'));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (empty($nome) || empty($cpf) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem_erro = "Email inválido.";
    } elseif (strlen($cpf) != 14) { // Validação básica do CPF
        $mensagem_erro = "CPF inválido. Deve ter 14 caracteres (com pontos e traço).";
    } elseif ($senha != $confirmar_senha) {
        $mensagem_erro = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $mensagem_erro = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        try {
            // Verifica se o email ou CPF já existem
            $stmt_verificar = $pdo->prepare("SELECT id FROM clientes WHERE email = :email OR cpf = :cpf");
            $stmt_verificar->bindParam(':email', $email);
            $stmt_verificar->bindParam(':cpf', $cpf);
            $stmt_verificar->execute();
            $resultado_verificar = $stmt_verificar->fetch(PDO::FETCH_ASSOC);

            if ($resultado_verificar) {
                $mensagem_erro = "Email ou CPF já cadastrados.";
            } else {
                // Hash da senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                // Insere o novo cliente
                $stmt = $pdo->prepare("INSERT INTO clientes (nome, cpf, email, senha) VALUES (:nome, :cpf, :email, :senha)");
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':cpf', $cpf);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha', $senha_hash);
                $stmt->execute();

                // Redireciona para a página de login
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao conectar ao banco de dados: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar - Home Banking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      body {
        background-color: #f8f9fa;
      }

      .container {
        max-width: 400px;
        margin: auto;
        margin-top: 50px;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      h2 {
        text-align: center;
        margin-bottom: 20px;
      }

      .form-group {
        margin-bottom: 15px;
      }

      button.btn-primary {
        width: 100%;
      }

      .alert {
        margin-top: 10px;
      }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registrar</h2>
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensagem_erro; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="nome"><i class="fas fa-user"></i> Nome Completo:</label>
                <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite seu nome completo" required>
            </div>
            <div class="form-group">
                <label for="cpf"><i class="fas fa-id-card"></i> CPF:</label>
                <input type="text" class="form-control" id="cpf" name="cpf" placeholder="Digite seu CPF (123.456.789-00)" required>
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu email" required>
            </div>
            <div class="form-group">
                <label for="senha"><i class="fas fa-lock"></i> Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha (mínimo 6 caracteres)" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha"><i class="fas fa-lock"></i> Confirmar Senha:</label>
                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar</button>
            <p class="mt-3 text-center">
                Já tem uma conta? <a href="login.php">Entrar</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscara para o CPF
        document.getElementById('cpf').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            let formattedValue = '';
            if (value.length > 0) formattedValue += value.substring(0, 3) + '.';
            if (value.length > 3) formattedValue += value.substring(3, 6) + '.';
            if (value.length > 6) formattedValue += value.substring(6, 9) + '-';
            if (value.length > 9) formattedValue += value.substring(9, 11);
            e.target.value = formattedValue;
        });
    </script>
</body>
</html>
