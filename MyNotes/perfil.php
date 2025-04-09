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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MyNotes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav w-100 align-items-center">
                    <li class="nav-item mx-auto search-form">
                        <form class="d-flex">
                            <input class="form-control me-2" type="search" placeholder="Procurar Notas"
                                aria-label="Search">
                            <button class="btn btn-outline-success" type="submit">Procurar</button>
                        </form>
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

    <!-- Sidebar -->
    <div class="sidebar" style="text-align: center;">
        <a href="perfil.php" style="border: none; background: none; padding: 0;">
            <img src="./img/avatar.png" class="rounded-circle" style="width: 80px;" alt="Avatar" />
        </a>
        <p></p>
        <a href="index.php">Página Principal</a>
        <a href="apontamentos.php">Meus Apontamentos</a>
        <?php if ($isAdmin): ?>
            <a href="aprovar_contas.php" class="text-warning fw-bold">Aprovações</a>
        <?php endif; ?>
    </div>

    <!-- Conteúdo Perfil -->
    <div class="content container mt-5">
        <h2 class="mb-4">Editar Perfil</h2>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Nome de Utilizador</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" placeholder="Deixa em branco para não alterar">
            </div>
            <div class="mb-3" id="confirm-password-group" style="display: none;">
                <label class="form-label">Confirmar Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirma a nova password">
            </div>
            <div class="mb-3">
                <label class="form-label">Escola</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['escola']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo de Utilizador</label>
                <input type="text" class="form-control" value="<?= $tipoUtilizador ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Alterações</button>
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
