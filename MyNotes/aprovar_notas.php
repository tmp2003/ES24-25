<?php
session_start();
require_once "config.php";

// Verifica se o utilizador está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Processar ações de aprovação/recusa
if (isset($_GET['action'], $_GET['id'])) {
    $notaId = intval($_GET['id']);
    if ($_GET['action'] === 'aprovar') {
        $stmt = $conn->prepare("UPDATE notes SET private_status = 0 WHERE id = ?");
        $stmt->execute([$notaId]);
        header("Location: aprovar_notas.php");
        exit();
    }
    if ($_GET['action'] === 'recusar') {
        $stmt = $conn->prepare("UPDATE notes SET private_status = 1 WHERE id = ?");
        $stmt->execute([$notaId]);
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
                                           href="?action=recusar&id=<?= $nota['id'] ?>">❌ Recusar</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>