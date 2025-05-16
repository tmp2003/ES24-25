<?php
session_start();
require_once "config.php";
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica se o utilizador está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Função para enviar email ao autor da nota
function enviarEmailNota($email, $username, $titulo, $aprovada, $motivo = "")
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mynotesnoreply@gmail.com';
        $mail->Password = 'lubr xyeb ewxx zena';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('mynotesnoreply@gmail.com', 'MyNotes');
        $mail->addAddress($email, $username);
        $mail->isHTML(true);

        if ($aprovada) {
            $mail->Subject = 'Nota Aprovada - MyNotes';
            $mail->Body = "<p>Olá <b>$username</b>,</p>
                <p>A sua nota <b>$titulo</b> foi aprovada e está agora disponível para todos os utilizadores da sua escola.</p>
                <p>Obrigado por contribuir para o MyNotes!</p>";
        } else {
            $mail->Subject = 'Nota Rejeitada - MyNotes';
            $mail->Body = "<p>Olá <b>$username</b>,</p>
                <p>A sua nota <b>$titulo</b> foi rejeitada pelos seguintes motivos:</p>
                <blockquote style='color:#b94a48;'>$motivo</blockquote>
                <p>Pode editar e submeter novamente se desejar.</p>";
        }

        $mail->send();
    } catch (Exception $e) {
        // Opcional: log de erro
    }
}

// Processar ações de aprovação/recusa
if (isset($_GET['action'], $_GET['id'])) {
    $notaId = intval($_GET['id']);

    // Buscar dados do autor da nota
    $stmtAutor = $conn->prepare("SELECT notes.title, userdata.email, userdata.username FROM notes JOIN userdata ON notes.user_id = userdata.id WHERE notes.id = ?");
    $stmtAutor->execute([$notaId]);
    $autor = $stmtAutor->fetch(PDO::FETCH_ASSOC);

    if ($_GET['action'] === 'aprovar') {
        $stmt = $conn->prepare("UPDATE notes SET private_status = 0 WHERE id = ?");
        $stmt->execute([$notaId]);
        if ($autor) {
            enviarEmailNota($autor['email'], $autor['username'], $autor['title'], true);
        }
        header("Location: aprovar_notas.php");
        exit();
    }
    if ($_GET['action'] === 'recusar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $motivo = trim($_POST['motivo'] ?? '');
        $stmt = $conn->prepare("UPDATE notes SET private_status = 1 WHERE id = ?");
        $stmt->execute([$notaId]);
        if ($autor) {
            enviarEmailNota($autor['email'], $autor['username'], $autor['title'], false, $motivo);
        }
        header("Location: aprovar_notas.php");
        exit();
    }
}

// Buscar notas com private_status 0 (aprovado) ou 2 (a aguardar)
$sql = "SELECT notes.id, notes.title, notes.content, notes.private_status, 
               userdata.username, cadeiras.nome AS cadeira_nome
        FROM notes
        JOIN userdata ON notes.user_id = userdata.id
        LEFT JOIN cadeiras ON notes.id_cadeira = cadeiras.id
        WHERE notes.private_status IN (0,2)
        ORDER BY notes.private_status DESC, notes.id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aprovar Notas - MyNotes</title>
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
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Sair</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="content container mt-5">
        <h2 class="mb-4">Notas para Aprovação</h2>
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Utilizador</th>
                    <th>Cadeira</th>
                    <th>Título</th>
                    <th>Descrição</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notas as $nota): ?>
                <tr>
                    <td><?= htmlspecialchars($nota['username']) ?></td>
                    <td><?= htmlspecialchars($nota['cadeira_nome'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($nota['title']) ?></td>
                    <td><?= nl2br(htmlspecialchars($nota['content'])) ?></td>
                    <td>
                        <?php
                        if ($nota['private_status'] == 2) {
                            echo '<span class="text-warning">A aguardar aprovação</span>';
                        } elseif ($nota['private_status'] == 0) {
                            echo '<span class="text-success">Aceite</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Ações
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="ver_nota.php?id=<?= $nota['id'] ?>">👁️ Ver Ficheiro</a>
                                </li>
                                <?php if ($nota['private_status'] == 2): ?>
                                    <li>
                                        <a class="dropdown-item text-success"
                                           href="?action=aprovar&id=<?= $nota['id'] ?>">✅ Aprovar</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger"
                                           href="#"
                                           onclick="abrirModalRecusar(<?= $nota['id'] ?>)">❌ Recusar</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Motivo de Rejeição -->
    <div class="modal fade" id="modalRecusar" tabindex="-1" aria-labelledby="modalRecusarLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="post" id="formRecusar">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalRecusarLabel">Motivo da Rejeição</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id" id="recusarNotaId">
              <div class="mb-3">
                <label for="motivo" class="form-label">Descreva o motivo da rejeição:</label>
                <textarea class="form-control" name="motivo" id="motivo" rows="4" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-danger">Rejeitar Nota</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <script>
    function abrirModalRecusar(notaId) {
        document.getElementById('recusarNotaId').value = notaId;
        const modal = new bootstrap.Modal(document.getElementById('modalRecusar'));
        modal.show();
    }

    // Submeter o formulário para recusar nota com motivo
    document.getElementById('formRecusar').addEventListener('submit', function(e) {
        e.preventDefault();
        const notaId = document.getElementById('recusarNotaId').value;
        this.action = "?action=recusar&id=" + notaId;
        this.method = "POST";
        this.submit();
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>