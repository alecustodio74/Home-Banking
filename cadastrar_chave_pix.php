<?php
require_once("conexao.php");
require_once("header.php");

// Verifica se o utilizador está logado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit();
}

/**
 * Valida uma chave PIX com base no tipo.
 *
 * @param string $chave A chave PIX a ser validada.
 * @param string $tipo  O tipo de chave PIX ('CPF', 'CNPJ', 'Email', 'Telefone', 'Aleatoria').
 * @return bool True se a chave for válida, false caso contrário.
 */
function validarChavePix(string $chave, string $tipo): bool {
    switch ($tipo) {
        case 'CPF':
            return validarCPF($chave);
        case 'CNPJ':
            return validarCNPJ($chave);
        case 'Email':
            return validarEmail($chave);
        case 'Telefone':
            return validarTelefone($chave);
        case 'Aleatoria':
            return validarChaveAleatoria($chave);
        default:
            return false;
    }
}

/**
 * Valida um CPF.
 *
 * @param string $cpf O CPF a ser validado.
 * @return bool True se o CPF for válido, false caso contrário.
 */
function validarCPF(string $cpf): bool {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/^(\d)\1+$/', $cpf)) {
        return false;
    }
    for ($i = 9; $i < 11; $i++) {
        for ($d = 0, $c = 0; $c < $i; $c++) {
            $d += $cpf[$c] * (($i + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$i] != $d) {
            return false;
        }
    }
    return true;
}

/**
 * Valida um CNPJ.
 *
 * @param string $cnpj O CNPJ a ser validado.
 * @return bool True se o CNPJ for válido, false caso contrário.
 */
function validarCNPJ(string $cnpj): bool {
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) != 14 || preg_match('/^(\d)\1+$/', $cnpj)) {
        return false;
    }
    for ($i = 12; $i < 14; $i++) {
        for ($d = 0, $m = ($i - 8), $c = 0; $c < $i; $c++) {
            $d += $cnpj[$c] * $m--;
            if ($m < 2) {
                $m = 9;
            }
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cnpj[$i] != $d) {
            return false;
        }
    }
    return true;
}

/**
 * Valida um endereço de email.
 *
 * @param string $email O email a ser validado.
 * @return bool True se o email for válido, false caso contrário.
 */
function validarEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida um número de telefone brasileiro.
 *
 * @param string $telefone O telefone a ser validado.
 * @return bool True se o telefone for válido, false caso contrário.
 */
function validarTelefone(string $telefone): bool {
    $telefone = preg_replace('/\D/', '', $telefone);
    return preg_match('/^(\d{2})(\d{8,9})$/', $telefone) === 1;
}

/**
  * Valida uma chave aleatória do Pix.
  *
  * @param string $chaveAleatoria A chave aleatória a ser validada.
  * @return bool True se a chave aleatória for válida, false caso contrário.
  */
function validarChaveAleatoria(string $chaveAleatoria): bool {
    // A chave aleatória do Pix tem 32 caracteres
    return strlen($chaveAleatoria) === 32;
}

$mensagem_erro = "";
$mensagem_sucesso = "";
$chave = ""; // Inicializa a variável $chave
$tipo = "";   // Inicializa a variável $tipo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id = $_SESSION['cliente_id'];
    $chave = trim(htmlspecialchars($_POST['chave'], ENT_QUOTES, 'UTF-8'));
    $tipo = $_POST['tipo'];

    // Validação dos dados
    if (empty($chave) || empty($tipo)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } elseif (!in_array($tipo, ['CPF', 'CNPJ', 'Email', 'Telefone', 'Aleatoria'])) {
        $mensagem_erro = "Tipo de chave PIX inválido.";
    } elseif (!validarChavePix($chave, $tipo)) { // Valida a chave PIX usando a função
        $mensagem_erro = "A chave PIX informada é inválida para o tipo selecionado.";
    } else {
        try {
            // Verifica se a chave PIX já existe para este cliente
            $stmt_verificar_chave = $pdo->prepare("SELECT id FROM pix_chaves WHERE cliente_id = :cliente_id AND chave = :chave");
            $stmt_verificar_chave->bindParam(':cliente_id', $cliente_id);
            $stmt_verificar_chave->bindParam(':chave', $chave);
            $stmt_verificar_chave->execute();

            if ($stmt_verificar_chave->fetch()) {
                $mensagem_erro = "Esta chave PIX já está cadastrada para você.";
            } else {
                // Insere a nova chave PIX
                $stmt_inserir_chave = $pdo->prepare("INSERT INTO pix_chaves (cliente_id, chave, tipo) VALUES (:cliente_id, :chave, :tipo)");
                $stmt_inserir_chave->bindParam(':cliente_id', $cliente_id);
                $stmt_inserir_chave->bindParam(':chave', $chave);
                $stmt_inserir_chave->bindParam(':tipo', $tipo);
                $stmt_inserir_chave->execute();

                $mensagem_sucesso = "Chave PIX cadastrada com sucesso!";
                // Limpa os campos do formulário após o sucesso
                $chave = "";
                $tipo = "";
            }
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao cadastrar chave PIX: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-5">
    <h2>Cadastrar Chave PIX</h2>

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
        <div class="mb-3">
            <label for="chave" class="form-label">Chave PIX:</label>
            <input type="text" class="form-control" id="chave" name="chave" value="<?php echo $chave; ?>" required placeholder="Digite sua chave PIX (CPF, CNPJ, Email, Telefone ou Chave Aleatória)">
        </div>
        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo de Chave:</label>
            <select class="form-control" id="tipo" name="tipo" required>
                <option value="">Selecione o tipo de chave</option>
                <option value="CPF" <?php echo $tipo === 'CPF' ? 'selected' : ''; ?>>CPF</option>
                <option value="CNPJ" <?php echo $tipo === 'CNPJ' ? 'selected' : ''; ?>>CNPJ</option>
                <option value="Email" <?php echo $tipo === 'Email' ? 'selected' : ''; ?>>Email</option>
                <option value="Telefone" <?php echo $tipo === 'Telefone' ? 'selected' : ''; ?>>Telefone</option>
                <option value="Aleatoria" <?php echo $tipo === 'Aleatoria' ? 'selected' : ''; ?>>Chave Aleatória</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
        <a href="transferir_pix.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>

<?php require_once("footer.php"); ?>
