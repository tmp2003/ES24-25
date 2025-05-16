<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Verificar se o ID da nota foi passado
if (!isset($_GET['id'])) {
    header("Location: aprovar_notas.php");
    exit();
}

$noteId = intval($_GET['id']);
$id = $_SESSION["user_id"];

// Buscar os dados da nota
$stmt = $conn->prepare("SELECT * FROM notes WHERE id = :note_id");
$stmt->bindParam(":note_id", $noteId, PDO::PARAM_INT);
$stmt->execute();
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    header("Location: aprovar_notas.php");
    exit();
}

// Buscar ficheiros associados à nota
$stmtFiles = $conn->prepare("SELECT * FROM note_files WHERE note_id = :note_id");
$stmtFiles->bindParam(":note_id", $noteId, PDO::PARAM_INT);
$stmtFiles->execute();
$files = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ver Nota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MyNotes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav w-100 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="sidebar" style="text-align: center;">
        <a href="perfil.php" style="border: none; background: none; padding: 0;">
            <img src="./img/avatar.png" class="rounded-circle" style="width: 80px; border: none;" alt="Avatar" />
        </a>
        <p></p>
        <a href="index.php">Página Principal</a>
        <a href="apontamentos.php">Meus Apontamentos</a>
    </div>

    <div class="content">
        <div class="mb-3">
            <label class="form-label"><strong>Título da Nota</strong></label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($note['title']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label"><strong>Conteúdo da Nota</strong></label>
            <textarea class="form-control" rows="10" readonly><?= htmlspecialchars($note['content']) ?></textarea>
        </div>
        <div class="mb-3">
            <h5>Ficheiros Anexados:</h5>
            <ul>
                <?php foreach ($files as $file): ?>
                    <li>
                        <a href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank"><?= htmlspecialchars(basename($file['file_path'])) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="mb-3">
            <a href="aprovar_notas.php" class="btn btn-secondary btn-lg">Voltar</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>