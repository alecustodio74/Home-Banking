<?php
// admin_login.php
// Página de login para administradores/gerentes

require_once("conexao.php");

// Inicia a sessão
session_start();

// Verifica se o usuário já está logado
if (isset($_SESSION['gerente_id'])) {
    header("Location: gerente.php"); // Redireciona para a página do gerente se já estiver logado
    exit();
}

$mensagem_erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } else {
        try {
            // CORREÇÃO: Seleciona também o agencia_id da tabela gerentes
            $stmt = $pdo->prepare("SELECT id, nome, email, senha, agencia_id FROM gerentes WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado && password_verify($senha, $resultado['senha'])) {
                // Senha correta, inicia a sessão
                $_SESSION['gerente_id'] = $resultado['id'];
                $_SESSION['gerente_nome'] = $resultado['nome']; // Use 'nome_gerente' para consistência com admin_header.php
                $_SESSION['email'] = $resultado['email'];
                $_SESSION['agencia_id'] = $resultado['agencia_id']; // CORREÇÃO: Armazena o agencia_id na sessão

                header("Location: gerente.php"); // Redireciona para a página do gerente
                exit();
            } else {
                $mensagem_erro = "Email ou senha inválidos.";
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
    <title>Login - Gerente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      body {
        background-color: #f8f9fa;
      }

      .container {
        max-width: 400px;
        margin: auto;
        margin-top: 100px;
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
        <h2>Login - Gerente</h2>
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensagem_erro; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu email" required>
            </div>
            <div class="form-group">
                <label for="senha"><i class="fas fa-lock"></i> Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
            <p class="mt-3 text-center">
                <a href="criar_gerente.php">Criar conta de administrador</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
