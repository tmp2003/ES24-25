<?php
session_start();
require_once 'config.php';

// Verificar se o utilizador está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Dados do utilizador
$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 2;
$escola = $_SESSION["escola"];

// Publicar nota (mudar private_status para 2)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["publicar_note_id"])) {
    $noteId = intval($_POST["publicar_note_id"]);
    $stmtPub = $conn->prepare("UPDATE notes SET private_status = 2 WHERE id = ? AND user_id = ?");
    $stmtPub->execute([$noteId, $user_id]);
    header("Location: apontamentos.php");
    exit();
}

// Buscar todas as notas do utilizador ordenadas por data
$stmt = $conn->prepare("SELECT id, title, content, created_at, private_status FROM notes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyNotes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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


    <!-- Navbar Secundária -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar2nd" style="height: 50px; background-color: #393e46;">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <a href="index.php" class="btn btn-link text-white text-decoration-none"
                    style="margin-left: 50%;min-width: 180px;">
                    <i class="bi bi-house-door-fill"></i> Home
                </a>
            </div>
            <div
                style="height: 50px; display: flex; align-items: center; min-width: 180px; box-sizing: border-box;">
                <a href="nova_nota.php" class="btn btn-secondary" style="">Nova Nota</a>
            </div>
        </div>
    </nav>

    <!-- Conteúdo -->
    <div class="content d-flex flex-column align-items-start">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h1 style="color: white;">Meus Apontamentos</h1>
        </div>

        <?php if (count($notes) > 0): ?>
            <div class="row mt-3 w-100">
                <?php foreach ($notes as $note): ?>
                    <div class="col-md-6">
                        <div class="card mb-3 shadow-sm custom-card">
                            <div class="card-header text-white d-flex justify-content-between">
                                <h5 class="mb-0"><?= htmlspecialchars($note['title']) ?></h5>
                            </div>
                            <div class="card-body" style="background-color: #2b2f38;">
                                <p class="card-text;" style="color: white;"><?= nl2br(htmlspecialchars($note['content'])) ?></p>

                                <?php
                                // Buscar ficheiros associados à nota
                                $stmtFiles = $conn->prepare("SELECT file_path, file_type FROM note_files WHERE note_id = ?");
                                $stmtFiles->execute([$note['id']]);
                                $files = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);
                                ?>

                                <?php if (!empty($files)): ?>
                                    <div class="mt-2" style="color: white;">
                                        <h6>Ficheiros:</h6>
                                        <ul class="list-unstyled">
                                            <?php foreach ($files as $file): ?>
                                                <li>
                                                    <a href="download.php?note_id=<?= $note['id'] ?>&file=<?= urlencode($file['file_path']) ?>"
                                                        class="btn btn-sm btn-outline-primary">
                                                        Download (<?= htmlspecialchars($file['file_type']) ?>)
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center" style="color: white;">
                                <small>Criado em:
                                    <?= date('d/m/Y H:i', strtotime($note['created_at'])) ?></small>
                                <div>
                                    <a href="editar_nota.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <form action="delete_note.php" method="POST" class="d-inline">
                                        <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Apagar</button>
                                    </form>
                                    <?php if ($note['private_status'] == 1): ?>
                                        <form action="apontamentos.php" method="POST" class="d-inline">
                                            <input type="hidden" name="publicar_note_id" value="<?= $note['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Publicar</button>
                                        </form>
                                    <?php elseif ($note['private_status'] == 0): ?>
                                        <span class="badge bg-success">Publicada</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">A aguardar aprovação de moderadores</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mt-3">Ainda não tem apontamentos.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>