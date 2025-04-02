<?php
session_start(); // Iniciar sessão
require_once 'config.php';

// Verificar se o utilizador está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Se não estiver logado, redirecionar para login
    exit();
}

// Dados do utilizador logado
$id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 1; // Verifica se é admin
$escola = $_SESSION["escola"];

$error = ""; // Inicializa a variável de erro

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $text = trim($_POST["text"]);

    if (empty($title) || empty($text)) {
        $error = "Preencha todos os campos!";
    } else {
        // Inserir a nota no banco de dados
        $sql = "INSERT INTO notes (user_id, title, content) VALUES (:user_id, :title, :content)";
        $stmt = $conn->prepare($sql); // Prepare a declaração
        $stmt->bindParam(":user_id", $id, PDO::PARAM_STR);
        $stmt->bindParam(":title", $title, PDO::PARAM_STR);
        $stmt->bindParam(":content", $text, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Obter o ID da nota recém-inserida
            $noteId = $conn->lastInsertId();

            // Processar o upload dos arquivos
            if (isset($_FILES['Files']) && is_array($_FILES['Files']['name'])) {
                for ($i = 0; $i < count($_FILES['Files']['name']); $i++) {
                    if ($_FILES['Files']['error'][$i] == 0) {
                        $nomeFicheiro = $_FILES['Files']['name'][$i]; // Nome original do arquivo
                        $tmpFicheiro = $_FILES['Files']['tmp_name'][$i]; // Caminho temporário do arquivo
                        $fileType = strtolower(pathinfo($nomeFicheiro, PATHINFO_EXTENSION)); // Obtém a extensão do arquivo


                        // Define o caminho de destino
                        $diretorioDestino = "./docs/$id/$noteId/";

                        // Verifica se o diretório existe, se não, cria
                        if (!is_dir($diretorioDestino)) {
                            mkdir($diretorioDestino, 0777, true); // Cria o diretório recursivamente
                        }

                        $filePath = $diretorioDestino . basename($nomeFicheiro);

                        // Move o arquivo para o diretório desejado
                        if (move_uploaded_file($tmpFicheiro, $filePath)) {
                            // Inserir o arquivo na tabela note_files
                            $sqlFile = "INSERT INTO note_files (note_id, file_path, file_type) VALUES (:note_id, :file_path, :file_type)";
                            $stmtFile = $conn->prepare($sqlFile);
                            $stmtFile->bindParam(":note_id", $noteId, PDO::PARAM_INT);
                            $stmtFile->bindParam(":file_path", $filePath, PDO::PARAM_STR);
                            $stmtFile->bindParam(":file_type", $fileType, PDO::PARAM_STR);
                            $stmtFile->execute();
                        }
                    }
                }
            }

            // Redirecionar após registo bem-sucedido
            header("Location: index.php");
            exit();
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
                    <li class="nav-item mx-auto search-form">
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

    <div class="sidebar">
        <a href="index.php">Página Principal</a>
        <a href="apontamentos.php">Meus Apontamentos</a>
        <?php if ($isAdmin): ?>
            <a href="aprovar_contas.php" class="text-warning fw-bold">Aprovações</a>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="content">
            <div class="mb-3">
                <label for="title" class="form-label">Titulo da Nota</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
                <label for="text" class="form-label">Conteúdo da Nota</label>
                <textarea class="form-control" name="text" rows="10" required></textarea>
            </div>
            <div class="mb-3">
                <label for="formFileMultiple" class="form-label">Anexar ficheiros</label>
                <input class="form-control" type="file" name="Files[]" multiple>
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
