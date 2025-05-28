<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];
$error = "";

// Verificar se o ID da nota foi passado
if (!isset($_GET['id'])) {
    header("Location: apontamentos.php");
    exit();
}

$noteId = intval($_GET['id']);
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 2;

// Buscar os dados da nota
$stmt = $conn->prepare("SELECT * FROM notes WHERE id = :note_id AND user_id = :user_id");
$stmt->bindParam(":note_id", $noteId, PDO::PARAM_INT);
$stmt->bindParam(":user_id", $id, PDO::PARAM_INT);
$stmt->execute();
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    header("Location: apontamentos.php");
    exit();
}

// Buscar ficheiros associados à nota
$stmtFiles = $conn->prepare("SELECT * FROM note_files WHERE note_id = :note_id");
$stmtFiles->bindParam(":note_id", $noteId, PDO::PARAM_INT);
$stmtFiles->execute();
$files = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);

// Atualizar a nota
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $text = trim($_POST["text"]);

    if (empty($title)) {
        $error = "O título é obrigatório!";
    } else {
        $sql = "UPDATE notes SET title = :title, content = :content WHERE id = :note_id AND user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":title", $title, PDO::PARAM_STR);
        $stmt->bindParam(":content", $text, PDO::PARAM_STR);
        $stmt->bindParam(":note_id", $noteId, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Mapear ficheiros existentes por nome
            $existingFilesByName = [];
            foreach ($files as $file) {
                $existingFilesByName[basename($file['file_path'])] = $file;
            }

            // Processar upload de novos ficheiros
            if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
                $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'pdf', 'docx'];
                $tiposMimePermitidos = [
                    'image/jpeg', 'image/png', 'application/pdf',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ];

                for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                    $fileError = $_FILES['files']['error'][$i];
                    $fileName = $_FILES['files']['name'][$i];
                    $tmpName = $_FILES['files']['tmp_name'][$i];

                    if (!empty($tmpName) && $fileError === UPLOAD_ERR_OK) {
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $mimeType = mime_content_type($tmpName);

                        if (in_array($fileExt, $extensoesPermitidas) && in_array($mimeType, $tiposMimePermitidos)) {
                            $diretorioDestino = "./docs/$id/$noteId/";
                            if (!is_dir($diretorioDestino)) {
                                mkdir($diretorioDestino, 0777, true);
                            }

                            $filePath = $diretorioDestino . basename($fileName);

                            // Se já existe um ficheiro com o mesmo nome, substitui
                            if (isset($existingFilesByName[$fileName])) {
                                // Apaga o ficheiro antigo do servidor e da base de dados
                                if (file_exists($existingFilesByName[$fileName]['file_path'])) {
                                    unlink($existingFilesByName[$fileName]['file_path']);
                                }
                                $stmtDelete = $conn->prepare("DELETE FROM note_files WHERE id = :file_id");
                                $stmtDelete->bindParam(":file_id", $existingFilesByName[$fileName]['id'], PDO::PARAM_INT);
                                $stmtDelete->execute();
                                // Remove do array para não tentar apagar de novo depois
                                unset($existingFilesByName[$fileName]);
                            }

                            if (move_uploaded_file($tmpName, $filePath)) {
                                $sqlFile = "INSERT INTO note_files (note_id, file_path, file_type) VALUES (:note_id, :file_path, :file_type)";
                                $stmtFile = $conn->prepare($sqlFile);
                                $stmtFile->bindParam(":note_id", $noteId, PDO::PARAM_INT);
                                $stmtFile->bindParam(":file_path", $filePath, PDO::PARAM_STR);
                                $stmtFile->bindParam(":file_type", $fileExt, PDO::PARAM_STR);
                                $stmtFile->execute();
                            }
                        } else {
                            $error .= "Ficheiro não permitido: $fileName<br>";
                        }
                    }
                }
            }

            // Não apaga ficheiros existentes, a não ser que o utilizador clique em remover (feito abaixo)
            if (empty($error)) {
                header("Location: apontamentos.php");
                exit();
            }
        } else {
            $error = "Erro ao atualizar a nota!";
        }
    }
}

// Remover ficheiro manualmente
if (isset($_GET['delete_file'])) {
    $fileId = intval($_GET['delete_file']);
    $stmtFile = $conn->prepare("SELECT * FROM note_files WHERE id = :file_id AND note_id = :note_id");
    $stmtFile->bindParam(":file_id", $fileId, PDO::PARAM_INT);
    $stmtFile->bindParam(":note_id", $noteId, PDO::PARAM_INT);
    $stmtFile->execute();
    $file = $stmtFile->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']); // Apagar o ficheiro do servidor
        }
        $stmtDelete = $conn->prepare("DELETE FROM note_files WHERE id = :file_id");
        $stmtDelete->bindParam(":file_id", $fileId, PDO::PARAM_INT);
        $stmtDelete->execute();
        header("Location: editar_nota.php?id=$noteId");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Nota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
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

    <form method="POST" enctype="multipart/form-data">
        <div class="content" style="color: white;">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="title" class="form-label">Título da Nota</label>
                <input type="text" class="form-control textP" name="title" value="<?= htmlspecialchars($note['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="text" class="form-label">Conteúdo da Nota</label>
                <textarea class="form-control textP" name="text" rows="10"><?= htmlspecialchars($note['content']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="files" class="form-label">Anexar Ficheiros</label>
                <input type="file" class="form-control" name="files[]" multiple>
            </div>
            <div class="mb-3">
                <h5>Ficheiros Anexados:</h5>
                <ul>
                    <?php foreach ($files as $file): ?>
                        <li>
                            <a href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank"><?= htmlspecialchars(basename($file['file_path'])) ?></a>
                            <a href="editar_nota.php?id=<?= $noteId ?>&delete_file=<?= $file['id'] ?>" class="text-danger">[Remover]</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="mb-3">
                <button class="btn btn-secondary btn-lg" type="submit">Guardar</button>
                <a href="apontamentos.php" class="btn btn-secondary btn-lg">Voltar</a>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>