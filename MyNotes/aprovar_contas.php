<?php
session_start();
require_once "config.php";
require 'vendor/autoload.php'; // Carregar o PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica se o utilizador está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Buscar utilizadores (inclui agora o campo admin)
$sql = "SELECT id, username, email, aprovado, admin FROM userdata";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 2;

// Dados do utilizador logado
$username = $_SESSION["username"];

// Função para enviar email ao utilizador aprovado
function enviarEmailAprovacao($email, $username)
{
    $mail = new PHPMailer(true);
    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP do Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'mynotesnoreply@gmail.com'; // Substitua pelo seu email do Gmail
        $mail->Password = 'lubr xyeb ewxx zena'; // Substitua pela senha de aplicativo gerada
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configurações do email
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('mynotesnoreply@gmail.com', 'MyNotes'); // Substitua pelo seu email
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = 'Conta Aprovada - MyNotes';
        $mail->Body = "<p>Olá <b>$username</b>,</p>
                       <p>A sua conta foi aprovada por um administrador. Já pode acessar o site e utilizar os nossos serviços.</p>
                       <p>Obrigado por utilizar o MyNotes!</p>";

        $mail->send();
    } catch (Exception $e) {
        // Log de erro (opcional)
        error_log("Erro ao enviar email: {$mail->ErrorInfo}");
    }
}

// Processar ações de aprovação
if (isset($_GET['action']) && $_GET['action'] === 'approve' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Atualizar o estado de aprovação no banco de dados
    $sql = "UPDATE userdata SET aprovado = 1 WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Buscar o email e o username do utilizador aprovado
        $sql = "SELECT email, username FROM userdata WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Enviar email de aprovação
            enviarEmailAprovacao($user['email'], $user['username']);
        }

        header("Location: aprovar_contas.php?success=1");
        exit();
    } else {
        header("Location: aprovar_contas.php?error=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyNotes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script>
        // Função para confirmar exclusão
        function confirmarExclusao(userId) {
            if (confirm("Tem certeza de que deseja apagar este utilizador? Esta ação não pode ser desfeita!")) {
                window.location.href = "process.php?action=delete&id=" + userId;
            }
        }
    </script>
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

    <!-- Conteúdo Principal -->
    <div class="content container" style="margin-top: 3%;margin-left:8%;">
        <h2 class="mb-4 " style="color: white;">Gestão de Utilizadores</h2>
        <table class="table table-striped" style="background-color: #353b44; color: #fff;">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Aprovado</th>
                    <th>Admin</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody style="background-color: #353b44; color: #fff;">
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= $user['aprovado'] ? "✔️ Aprovado" : "❌ Pendente" ?></td>
                        <td>
                            <?php
                            if ($user['admin'] == 2) {
                                echo "Administrador 🛠️";
                            } elseif ($user['admin'] == 1) {
                                echo "Moderador 👨‍🏫";
                            } else {
                                echo "Utilizador 🧑‍🎓";
                            }
                            ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">Ações</button>
                                <ul class="dropdown-menu">
                                    <?php
                                    $emailIpcb = str_ends_with($user['email'], '@ipcb.pt');
                                    $adminLevel = $_SESSION['admin'] ?? 0;
                                    ?>

                                    <?php if ($user['aprovado'] == 0): ?>
                                        <li><a class="dropdown-item text-success"
                                                href="aprovar_contas.php?action=approve&id=<?= $user['id'] ?>">✅ Aprovar</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ($adminLevel == 2 && $emailIpcb): ?>
                                        <?php if ($user['admin'] == 0): ?>
                                            <li><a class="dropdown-item text-success"
                                                    href="process.php?action=promote&id=<?= $user['id'] ?>">⬆️ Promover a
                                                    Moderador</a></li>
                                            <li><a class="dropdown-item text-warning"
                                                    href="process.php?action=promote2&id=<?= $user['id'] ?>">⬆️ Promover a Admin</a>
                                            </li>
                                        <?php elseif ($user['admin'] == 1): ?>
                                            <li><a class="dropdown-item text-warning"
                                                    href="process.php?action=promote2&id=<?= $user['id'] ?>">⬆️ Promover a Admin</a>
                                            </li>
                                            <li><a class="dropdown-item text-secondary"
                                                    href="process.php?action=demote&id=<?= $user['id'] ?>&to=0">⬇️ Despromover para
                                                    Utilizador</a></li>
                                        <?php elseif ($user['admin'] == 2): ?>
                                            <li><a class="dropdown-item text-secondary"
                                                    href="process.php?action=demote&id=<?= $user['id'] ?>&to=1">⬇️ Despromover para
                                                    Moderador</a></li>
                                            <li><a class="dropdown-item text-secondary"
                                                    href="process.php?action=demote&id=<?= $user['id'] ?>&to=0">⬇️ Despromover para
                                                    Utilizador</a></li>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($adminLevel == 2 && $_SESSION['user_id'] != $user['id']): ?>
                                        <li><a class="dropdown-item text-danger" href="#"
                                                onclick="confirmarExclusao(<?= $user['id'] ?>)">🗑️ Apagar</a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>


                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>