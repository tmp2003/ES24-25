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

// Buscar todas as notas do utilizador ordenadas por data
$stmt = $conn->prepare("SELECT id, title, content, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
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

    <!-- Sidebar -->
    <div class="sidebar" style="text-align: center;">
        <a href="perfil.php" style="border: none; background: none; padding: 0;">
            <img src="./img/avatar.png" class="rounded-circle" style="width: 80px; border: none;" alt="Avatar" />
        </a>
        <p></p>
        <a href="index.php">Página Principal</a>
        <a href="apontamentos.php">Meus Apontamentos</a>
        <?php if ($isAdmin): ?>
            <a href="aprovar_contas.php" class="text-warning fw-bold">Aprovações</a>
        <?php endif; ?>
    </div>

    <!-- Conteúdo -->
    <div class="content d-flex flex-column align-items-start">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h1>Meus Apontamentos</h1>
            <a href="nova_nota.php" class="btn btn-secondary btn-lg">Nova Nota</a>
        </div>

        <?php if (count($notes) > 0): ?>
            <div class="row mt-3 w-100">
                <?php foreach ($notes as $note): ?>
                    <div class="col-md-6">
                        <div class="card mb-3 shadow-sm">
                            <div class="card-header bg-primary text-white d-flex justify-content-between">
                                <h5 class="mb-0"><?= htmlspecialchars($note['title']) ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?= nl2br(htmlspecialchars($note['content'])) ?></p>

                                <?php
                                // Buscar ficheiros associados à nota
                                $stmtFiles = $conn->prepare("SELECT file_path, file_type FROM note_files WHERE note_id = ?");
                                $stmtFiles->execute([$note['id']]);
                                $files = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);
                                ?>

                                <?php if (!empty($files)): ?>
                                    <div class="mt-2">
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
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <small class="text-muted">Criado em:
                                    <?= date('d/m/Y H:i', strtotime($note['created_at'])) ?></small>
                                <div>
                                    <a href="editar_nota.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <form action="delete_note.php" method="POST" class="d-inline">
                                        <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Apagar</button>
                                    </form>
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