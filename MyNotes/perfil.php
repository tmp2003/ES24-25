<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION["user_id"];
$username = $_SESSION["username"];
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 2;
$escola = $_SESSION["escola"];

$erro = "";
$success = "";

// Buscar dados do utilizador
$stmt = $conn->prepare("SELECT username, email, escola FROM userdata WHERE id = :id");
$stmt->bindParam(":id", $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$currentEmail = $user["email"];
$tipoUtilizador = (str_ends_with($currentEmail, "@ipcb.pt")) ? "Docente" : "Aluno";

// Submissão do formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $novoUsername = trim($_POST["username"]);
    $novoEmail = trim($_POST["email"]);
    $novaPassword = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    $alterarEmail = $novoEmail !== $currentEmail;
    $alterarPassword = !empty($novaPassword);

    try {
        if (empty($novoUsername) || empty($novoEmail)) {
            throw new Exception("Nome e Email são obrigatórios.");
        }

        if ($alterarPassword && $novaPassword !== $confirmPassword) {
            throw new Exception("As passwords não coincidem.");
        }

        $conn->beginTransaction();

        $sql = "UPDATE userdata SET username = :username, email = :email";
        $params = [
            ":username" => $novoUsername,
            ":email" => $novoEmail,
            ":id" => $userId
        ];

        if ($alterarPassword) {
            $sql .= ", password = :password";
            $params[":password"] = password_hash($novaPassword, PASSWORD_DEFAULT);
        }

        if ($alterarEmail) {
            $sql .= ", aprovado = 0";
        }

        $sql .= " WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $conn->commit();

        if ($alterarEmail) {
            session_destroy();
            header("Location: login.php");
            exit();
        } else {
            header("Location: index.php");
            $_SESSION["username"] = $novoUsername;
            $success = "Perfil atualizado com sucesso!";
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $erro = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perfil - MyNotes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom" style="height: 100px;">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <!-- Logo -->
            <div class="d-flex align-items-center flex-grow-1" style="min-width:180px; margin-left: 5%;">
                <a class="navbar-brand" href="index.php" style="margin-left: 3rem;">MyNotes</a>
            </div>
            <!-- Itens centrais -->
            <div class="d-flex justify-content-center flex-grow-1">
                <ul class="navbar-nav align-items-center gap-3">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Hub</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="apontamentos.php">Apontamentos</a>
                    </li>
                    <?php if ($isAdmin): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" id="aprovacoesDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Aprovações
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="aprovacoesDropdown">
                                <li><a class="dropdown-item" href="aprovar_contas.php">Contas</a></li>
                                <li><a class="dropdown-item" href="aprovar_notas.php">Notas</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <!-- Itens à direita -->
            <div class="d-flex align-items-center flex-grow-1 justify-content-end" style="min-width:180px;">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="perfil.php">
                            <img src="./img/avatar.png" class="rounded-circle" style="width: 40px; border: none;"
                                alt="Avatar" />
                        </a>
                    </li>
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Sair</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Perfil -->
    <div class="content container" style="margin-top: 3%;margin-left:8%;color:white;">
        <h2 class="mb-4">Editar Perfil</h2>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" >
            <div class="mb-3" >
                <label class="form-label">Nome de Utilizador</label>
                <input type="text" name="username" class="form-control" style="background-color: #292e35;color: white; border: none;"
                    value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email"  style="background-color: #292e35;color: white; border: none;" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"
                    required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password"  style="background-color: #292e35;color: white; border: none;" class="form-control" id="password"
                    placeholder="Deixa em branco para não alterar">
            </div>
            <div class="mb-3" id="confirm-password-group" style="display: none;">
                <label class="form-label">Confirmar Password</label>
                <input type="password" name="confirm_password" class="form-control"
                    placeholder="Confirma a nova password" style="background-color: #292e35;color: white; border: none;">
            </div>
            <div class="mb-3">
                <label class="form-label">Escola</label>
                <input type="text" class="form-control"  style="background-color: #292e35;color: white; border: none;" value="<?= htmlspecialchars($user['escola']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo de Utilizador</label>
                <input type="text" class="form-control"  style="background-color: #292e35;color: white; border: none;" value="<?= $tipoUtilizador ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 1%;">Guardar Alterações</button>
        </form>
    </div>

    <script>
        const passwordField = document.getElementById('password');
        const confirmGroup = document.getElementById('confirm-password-group');

        passwordField.addEventListener('input', () => {
            if (passwordField.value.trim() !== "") {
                confirmGroup.style.display = "block";
            } else {
                confirmGroup.style.display = "none";
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>