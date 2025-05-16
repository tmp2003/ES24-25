<?php
session_start();
require_once 'config.php';

// Verificar se o utilizador está autenticado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Atribuir variáveis da sessão
$id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$escola = $_SESSION["escola"];
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 2;

$error = "";

// Buscar cadeiras da escola do utilizador
$stmtCadeiras = $conn->prepare("SELECT id, nome FROM cadeiras WHERE escola_id = :escola_id");
$stmtCadeiras->bindParam(":escola_id", $escola, PDO::PARAM_INT);
$stmtCadeiras->execute();
$cadeiras = $stmtCadeiras->fetchAll(PDO::FETCH_ASSOC);

// Processar o formulário de criação de nota
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $text = trim($_POST["text"]);
    $id_cadeira = isset($_POST["id_cadeira"]) ? intval($_POST["id_cadeira"]) : null;

    // Validar o título e a cadeira
    if (empty($title)) {
        $error = "O título é obrigatório!";
    } elseif (empty($id_cadeira)) {
        $error = "Deve selecionar uma cadeira!";
    } else {
        // Inserir a nota no banco de dados
        $sql = "INSERT INTO notes (user_id, title, content, escola, id_cadeira, private_status) 
                VALUES (:user_id, :title, :content, :escola, :id_cadeira, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":user_id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":title", $title, PDO::PARAM_STR);
        $stmt->bindParam(":content", $text, PDO::PARAM_STR);
        $stmt->bindParam(":escola", $escola, PDO::PARAM_INT);
        $stmt->bindParam(":id_cadeira", $id_cadeira, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $noteId = $conn->lastInsertId();

            // Processar os ficheiros enviados
            if (isset($_FILES['Files']) && is_array($_FILES['Files']['name'])) {
                $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'pdf', 'docx'];
                $tiposMimePermitidos = [
                    'image/jpeg', 'image/png', 'application/pdf',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ];

                for ($i = 0; $i < count($_FILES['Files']['name']); $i++) {
                    $fileError = $_FILES['Files']['error'][$i];
                    $fileName = $_FILES['Files']['name'][$i];
                    $tmpName = $_FILES['Files']['tmp_name'][$i];

                    // Verificar se o ficheiro foi enviado
                    if (!empty($tmpName) && $fileError === UPLOAD_ERR_OK) {
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $mimeType = mime_content_type($tmpName);

                        if (in_array($fileExt, $extensoesPermitidas) && in_array($mimeType, $tiposMimePermitidos)) {
                            $diretorioDestino = "./docs/$id/$noteId/";
                            if (!is_dir($diretorioDestino)) {
                                mkdir($diretorioDestino, 0777, true);
                            }

                            $filePath = $diretorioDestino . basename($fileName);
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

            // Redirecionar se não houver erros
            if (empty($error)) {
                header("Location: index.php");
                exit();
            }
        } else {
            $error = "Erro ao criar nota!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyNotes - Nova Nota</title>
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
                    <li class="nav-item mx-auto search-form" style="width: 50%;">
                        <form class="d-flex">
                            <input class="form-control me-2" type="search" placeholder="Procurar Notas" aria-label="Search">
                            <button class="btn btn-outline-success" type="submit">Procurar</button>
                        </form>
                    </li>
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
        <?php if ($isAdmin): ?>
            <a href="aprovar_contas.php" class="text-warning fw-bold">Aprovações</a>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data" id="notaForm">
        <div class="content">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="title" class="form-label">Titulo da Nota</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
                <label for="text" class="form-label">Conteúdo da Nota</label>
                <textarea class="form-control" name="text" rows="10"></textarea>
            </div>
            <div class="mb-3">
                <label for="cadeiraSelect" class="form-label">Cadeira</label>
                <select class="form-select" id="cadeiraSelect" name="id_cadeira" required>
                    <option value="">Escolha uma cadeira...</option>
                    <?php foreach ($cadeiras as $cadeira): ?>
                        <option value="<?= $cadeira['id'] ?>"><?= htmlspecialchars($cadeira['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="formFileMultiple" class="form-label">Anexar ficheiros</label>
                <input class="form-control" type="file" name="Files[]" multiple accept=".jpg,.jpeg,.png,.pdf,.docx">
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