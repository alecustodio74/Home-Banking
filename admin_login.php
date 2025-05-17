<?php
// admin_login.php
// Página de login para administradores

require_once("conexao.php");

// Inicia a sessão
session_start();

// Se o administrador já estiver logado, redireciona para a página de dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: gerente.php"); // Alterado para gerente.php
    exit();
}

$mensagem_erro = "";
$email = ""; // Inicializa a variável $email para evitar erros de undefined variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem_erro = "Email inválido.";
    } else {
        try {
            // Busca o administrador pelo email
            $stmt = $pdo->prepare("SELECT id, nome, senha FROM gerentes WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                // Verifica a senha
                if (password_verify($senha, $admin['senha'])) {
                    // Login bem-sucedido
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_nome'] = $admin['nome'];
                    header("Location: gerente.php"); // Alterado para gerente.php
                    exit();
                } else {
                    $mensagem_erro = "Senha incorreta.";
                }
            } else {
                $mensagem_erro = "Email não encontrado.";
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
    <title>Login do Administrador</title>
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
        <h2>Login do Gerente</h2>
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensagem_erro; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu email" value="<?php echo $email; ?>" required>
            </div>
            <div class="form-group">
                <label for="senha"><i class="fas fa-lock"></i> Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
            <p class="mt-3 text-center">
                <a href="criar_gerente.php">Criar Conta</a> |
                <a href="#">Esqueci minha senha</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
