<?php


session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "notesdb");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$note_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 2;

// Obter detalhes da publicação
$sql = "SELECT n.title, n.content, nf.file_path, u.username 
        FROM notes n 
        LEFT JOIN note_files nf ON n.id = nf.note_id 
        LEFT JOIN userdata u ON n.user_id = u.id 
        WHERE n.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $note_id);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();

// Obter média de avaliações e avaliação do utilizador
$sql_media = "SELECT AVG(rating) as media FROM note_ratings WHERE note_id = ?";
$stmt_media = $conn->prepare($sql_media);
$stmt_media->bind_param("i", $note_id);
$stmt_media->execute();
$media = $stmt_media->get_result()->fetch_assoc()["media"];

$user_rating = 0;
if (isset($_SESSION["user_id"])) {
    $sql_user_rating = "SELECT rating FROM note_ratings WHERE note_id = ? AND user_id = ?";
    $stmt_user_rating = $conn->prepare($sql_user_rating);
    $stmt_user_rating->bind_param("ii", $note_id, $_SESSION["user_id"]);
    $stmt_user_rating->execute();
    $result_user_rating = $stmt_user_rating->get_result()->fetch_assoc();
    if ($result_user_rating) {
        $user_rating = $result_user_rating["rating"];
    }
}

// Guardar avaliação
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["star_rating"])) {
    $rating = intval($_POST["star_rating"]);
    $user_id = $_SESSION["user_id"];
    $sql_upsert = "INSERT INTO note_ratings (note_id, user_id, rating) VALUES (?, ?, ?)
                   ON DUPLICATE KEY UPDATE rating = VALUES(rating)";
    $stmt_upsert = $conn->prepare($sql_upsert);
    $stmt_upsert->bind_param("iii", $note_id, $user_id, $rating);
    $stmt_upsert->execute();
    header("Location: publicacao.php?id=" . $note_id);
    exit();
}

// Obter comentários da publicação
$sql_comments = "SELECT c.id AS comment_id, c.comment, c.created_at, u.username 
                 FROM comments c 
                 JOIN userdata u ON c.user_id = u.id 
                 WHERE c.note_id = ? 
                 ORDER BY c.created_at DESC";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $note_id);
$stmt_comments->execute();
$comments = $stmt_comments->get_result();

// Eliminar comentário
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_comment_id"]) && $isAdmin) {
    $delete_comment_id = intval($_POST["delete_comment_id"]);
    $sql_delete = "DELETE FROM comments WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $delete_comment_id);
    $stmt_delete->execute();
    header("Location: publicacao.php?id=" . $note_id);
    exit();
}

// Adicionar novo comentário com verificação de palavras proibidas
$comentario_erro = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["comment"])) {
    $comment = trim($_POST["comment"]);
    $user_id = $_SESSION["user_id"];
    $bad_words = ['merda', 'puta', 'fodasse', 'shiy', 'fuck'];
    $has_bad_word = false;
    foreach ($bad_words as $bad_word) {
        if (stripos($comment, $bad_word) !== false) {
            $has_bad_word = true;
            break;
        }
    }
    if ($has_bad_word) {
        $comentario_erro = "O seu comentário contém linguagem imprópria e não foi publicado.";
    } elseif (!empty($comment)) {
        $sql_insert = "INSERT INTO comments (note_id, user_id, comment) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iis", $note_id, $user_id, $comment);
        $stmt_insert->execute();
        header("Location: publicacao.php?id=" . $note_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalhes da Publicação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
  </head>

<body>

    <!-- Navbar Superior (Dark Mode) -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom" style="height: 100px;">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center flex-grow-1" style="min-width:180px; margin-left: 5%;">
                <a class="navbar-brand" href="index.php" style="margin-left: 3rem;">MyNotes</a>
            </div>
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
    <div class="content" style="color: white;">
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-10">
                    <h1><?php echo htmlspecialchars($note["title"]); ?></h1>
                    <p><?php echo nl2br(htmlspecialchars($note["content"])); ?></p>
                    <?php if (!empty($note["file_path"])): ?>
                        <a href="<?php echo htmlspecialchars($note["file_path"]); ?>" class="btn btn-primary" download>Descarregar Documento</a>
                    <?php endif; ?>
                    <p class="mt-3" style="opacity: 0.5;">Publicado por: <?php echo htmlspecialchars($note["username"]); ?></p>
                </div>
                <div class="col-md-2 d-flex flex-column align-items-end justify-content-start" style="min-width: 120px;">
                    <form method="POST" id="ratingForm">
                        <div class="star-rating" style="font-size:2rem; cursor:pointer;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="submit" name="star_rating" value="<?php echo $i; ?>" style="background:none;border:none;padding:0;">
                                    <span class="star<?php if ($i <= $user_rating) echo ' checked'; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                                </button>
                            <?php endfor; ?>
                        </div>
                    </form>
                    <div class="mt-2" style="color: #ffc107;">
                        Média: <?php echo $media ? number_format($media, 2) : "Sem avaliações"; ?>
                    </div>
                </div>
            </div>
            <hr>
            <h3>Comentários</h3>
            <?php if ($comments->num_rows > 0): ?>
                <?php while ($comment = $comments->fetch_assoc()): ?>
                    <div class="mb-3">
                        <strong>
                            <img src="./img/avatar.png" class="rounded-circle"
                                style="width: 40px; border: none; margin-right: 1%;"
                                alt="Avatar" /><?php echo htmlspecialchars($comment["username"]); ?></strong>
                        <p class="comentario-texto"><?php echo nl2br(htmlspecialchars($comment["comment"])); ?></p>
                        <small class="mt-3" style="opacity: 0.5;"><?php echo $comment["created_at"]; ?></small>
                        <?php if ($isAdmin): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="delete_comment_id" value="<?php echo $comment["comment_id"]; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Não há comentários ainda. Seja o primeiro a comentar!</p>
            <?php endif; ?>

            <hr>
            <h4>Adicionar Comentário</h4>
            <?php if (!empty($comentario_erro)): ?>
                <div class="alert alert-danger text-center"><?php echo $comentario_erro; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <textarea name="comment" class="textP"
                        style="min-width: 100%; border-radius: 10px; min-height: 20px;" rows="3"
                        placeholder="Escreva seu comentário aqui..." required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Comentar</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>