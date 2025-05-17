<?php
// criar_gerente.php
// Página para criar novos gerentes

require_once("conexao.php");

$mensagem_erro = "";
$mensagem_sucesso = ""; // Adicionada variável para mensagem de sucesso
$nome = "";
$email = "";

// Função para buscar as agências do banco de dados
function buscarAgencias($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, nome, cidade FROM agencias");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Em vez de encerrar o script, retorna um array vazio e define uma mensagem de erro
        error_log("Erro ao buscar agências: " . $e->getMessage()); // Log do erro
        return [];
    }
}

$agencias = buscarAgencias($pdo); // Busca as agências antes de qualquer output

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    $repetir_senha = $_POST['repetir_senha'];
    $agencia_id = filter_input(INPUT_POST, 'agencia_id', FILTER_VALIDATE_INT);

    if (empty($nome) || empty($email) || empty($senha) || empty($repetir_senha) || empty($agencia_id)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem_erro = "Email inválido.";
    } elseif ($senha != $repetir_senha) {
        $mensagem_erro = "As senhas não coincidem.";
    } elseif (empty($agencias)) {
        $mensagem_erro = "Não há agências cadastradas para atribuir o gerente.";
    } else {
        try {
            // Verifica se a agência selecionada existe
            $agencia_existente = false;
            foreach ($agencias as $agencia) {
                if ($agencia['id'] == $agencia_id) {
                    $agencia_existente = true;
                    break;
                }
            }
            if (!$agencia_existente) {
                $mensagem_erro = "Agência selecionada inválida.";
            } else {
                // Hash da senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                // Insere o novo gerente no banco de dados
                $stmt = $pdo->prepare("INSERT INTO gerentes (nome, email, senha, agencia_id) VALUES (:nome, :email, :senha, :agencia_id)");
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha', $senha_hash);
                $stmt->bindParam(':agencia_id', $agencia_id);

                if ($stmt->execute()) {
                    $mensagem_sucesso = "Cadastro efetuado com sucesso!"; // Define a mensagem de sucesso
                    // Limpa o formulário
                    $nome = "";
                    $email = "";
                    // Não limpa a senha por questões de segurança e UX
                } else {
                    $mensagem_erro = "Erro ao criar gerente.";
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $mensagem_erro = "Este email já está cadastrado.";
            } else {
                $mensagem_erro = "Erro ao conectar ao banco de dados: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Gerente</title>
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
        <h2>Criar Gerente</h2>
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensagem_erro; ?>
            </div>
        <?php endif; ?>
        <?php if ($mensagem_sucesso): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $mensagem_sucesso; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="nome"><i class="fas fa-user"></i> Nome:</label>
                <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite seu nome" value="<?php echo $nome; ?>" required>
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu email" value="<?php echo $email; ?>" required>
            </div>
            <div class="form-group">
                <label for="senha"><i class="fas fa-lock"></i> Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>
            <div class="form-group">
                <label for="repetir_senha"><i class="fas fa-lock"></i> Repetir Senha:</label>
                <input type="password" class="form-control" id="repetir_senha" name="repetir_senha" placeholder="Repita sua senha" required>
            </div>
            <div class="form-group">
                <label for="agencia_id"><i class="fas fa-building"></i> Agência:</label>
                <select class="form-control" id="agencia_id" name="agencia_id" required>
                    <option value="" disabled selected>Selecione a Agência</option>
                    <?php if (empty($agencias)): ?>
                        <option value="" disabled>Não há agências cadastradas</option>
                    <?php else: ?>
                        <?php foreach ($agencias as $agencia): ?>
                            <option value="<?php echo $agencia['id']; ?>">
                                <?php echo $agencia['id'] . " - " . $agencia['nome'] . " (" . $agencia['cidade'] . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Criar Conta</button>
            <p class="mt-3 text-center">
                <a href="admin_login.php">Voltar para Login</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
