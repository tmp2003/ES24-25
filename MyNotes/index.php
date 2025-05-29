<?php

session_start();

// Verificar se o utilizador está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Dados do utilizador logado
$username = $_SESSION["username"];
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 2;
$escola = $_SESSION["escola"];

// Conexão com a base de dados
$conn = new mysqli("localhost", "root", "", "notesdb");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Obter as publicações aprovadas da mesma escola do utilizador
$sql = "SELECT n.id, n.title, n.content, n.created_at, u.username
        FROM notes n
        JOIN userdata u ON n.user_id = u.id
        WHERE n.private_status = 0 AND n.escola = ?
        ORDER BY n.created_at DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da query: " . $conn->error);
}
$stmt->bind_param("i", $escola);
$stmt->execute();
$result = $stmt->get_result();

// Buscar as 5 publicações com melhor média de rating, da escola do utilizador, e que sejam públicas
$sql_top = "
    SELECT n.id, n.title, u.username, AVG(r.rating) as media
    FROM notes n
    JOIN userdata u ON n.user_id = u.id
    LEFT JOIN note_ratings r ON n.id = r.note_id
    WHERE n.private_status = 0 AND n.escola = ?
    GROUP BY n.id, n.title, u.username
    HAVING COUNT(r.rating) > 0
    ORDER BY media DESC, n.id DESC
    LIMIT 5
";
$stmt_top = $conn->prepare($sql_top);
$stmt_top->bind_param("i", $escola);
$stmt_top->execute();
$result_top = $stmt_top->get_result();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyNotes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <!-- Modal de Pesquisa -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: #23272b;">
                <div class="modal-body">
                    <form class="d-flex">
                        <input class="form-control me-2" style="background-color: #363a3e;border:none;" type="search" placeholder="Procure uma título ou conteúdo..."
                            aria-label="Search">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-arrow-right"></i> Procurar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Superior (Dark Mode) -->
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
            <div style="min-width: 25%;">
                <button class="btn btn-secondary d-flex align-items-center" style="min-width:60%;"
                    data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="bi bi-search me-2"></i> Procurar...
                </button>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="content"
        style="background-color: #131519; display: flex; gap: 3%; align-items: flex-start; min-width: 100%;margin-top: 40px; margin-left: 0; margin-right: 0;">
        <!-- Card Geral -->
        <div class="p-4 rounded-4 shadow-lg"
            style="background-color: #23262b; min-width: 80%; flex: 2; border-radius: 18px;">
            <h2 class="mb-4 pb-2 border-bottom border-secondary" style="color: #fff; font-weight: bold;">Geral</h2>
            <?php
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $data = date('d/m/Y', strtotime($row["created_at"])); // Data no formato dd/mm/yyyy
                    ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom border-gray-800 py-4"
                        style="border-color: #393e46 !important;">
                        <div class="d-flex align-items-center">
                            <!-- Ícone à esquerda -->
                            <div class="me-3 d-flex align-items-center justify-content-center"
                                style="width:40px; height:40px; background:#23272b; border-radius:8px;">
                                <i class="bi bi-file-earmark" style="font-size: 1.5rem; color: #4fc3f7;"></i>
                            </div>
                            <div>
                                <h3 class="mb-1" style="color: #fff; font-size: 1.15rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($row["title"]); ?>
                                </h3>
                                <p class="mb-0" style="color: #bfc4cc; font-size: 0.98rem; margin-right: 1%;">
                                    <?php
                                    $content = htmlspecialchars($row["content"]);
                                    if (mb_strlen($content) > 150) {
                                        $content = mb_substr($content, 0, 150) . '...';
                                    }
                                    echo nl2br($content);
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-end" style="min-width: 150px;">
                            <p class="mb-1 text-secondary" style="font-size: 0.95rem;">
                                Autor: <span
                                    class="fw-semibold text-white"><?php echo htmlspecialchars($row["username"]); ?></span>
                            </p>
                            <p class="mb-0 text-secondary" style="font-size: 0.95rem;">
                                Data: <?php echo $data; ?>
                            </p>
                            <a href="publicacao.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm mt-2">Ver
                                Detalhes e Comentar</a>
                        </div>
                    </div>
                    <?php
                endwhile;
            else:
                ?>
                <p style="color: #fff;">Não há publicações aprovadas disponíveis no momento.</p>
                <?php
            endif;
            ?>
        </div>
        <!-- Card Top Notas -->
        <div class="p-4 rounded-4 shadow-lg"
            style="background-color: #23262b; max-width: 350px; min-width: 17%; border-radius: 18px; height: fit-content;margin-right: 5%;">
            <h2 class="mb-4 pb-2 border-bottom border-secondary" style="color: #fff; font-weight: bold;">Top Notas</h2>
            <ul class="list-unstyled" style="color: #bfc4cc;">
                <?php if ($result_top->num_rows > 0): ?>
                    <?php while ($row = $result_top->fetch_assoc()): ?>
                        <li class="mb-3">
                            <a href="publicacao.php?id=<?php echo $row['id']; ?>" class="fw-semibold text-white text-decoration-none">
                                <?php echo htmlspecialchars($row["title"]); ?>
                            </a>
                            <br>
                            <span style="font-size: 0.95rem;">
                                <span class="text-secondary">Autor:</span> <?php echo htmlspecialchars($row["username"]); ?> <br>
                                <span class="text-secondary">Média:</span>
                                <span style="color: #ffc107;"><?php echo number_format($row["media"], 2); ?> ★</span>
                            </span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>Sem notas avaliadas nesta escola.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>