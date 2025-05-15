<?php
session_start(); // Iniciar sessão

// Verificar se o utilizador está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Se não estiver logado, redirecionar para login
    exit();
}

// Dados do utilizador logado
$username = $_SESSION["username"];
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 2; // Verifica se é admin
$escola = $_SESSION["escola"];

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyNotes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Agora contém os estilos da sidebar também -->
</head>

<body>

    <!-- Navbar Superior (Dark Mode) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MyNotes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav w-100 align-items-center">

                    <!-- Barra de Pesquisa Centralizada -->
                    <li class="nav-item mx-auto search-form" style="width: 50%;">
                        <form class="d-flex">
                            <input class="form-control me-2" type="search" placeholder="Procurar Notas"
                                aria-label="Search">
                            <button class="btn btn-outline-success" type="submit">Procurar</button>
                        </form>
                    </li>

                    <!-- Links de Navegação -->


                    <!-- Botão de Logout -->
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

    <!-- Sidebar (Navbar Vertical) -->
    <div class="sidebar" style="text-align: center;">
        <a href="perfil.php" style="border: none; background: none; padding: 0;">
            <img src="./img/avatar.png" class="rounded-circle" style="width: 80px; border: none;" alt="Avatar" />
        </a>


        <p></p>
        <a href="#">Página Principal</a>
        <a href="apontamentos.php">Meus Apontamentos</a>

        <?php if ($isAdmin): ?>
            <a href="aprovar_contas.php" class="text-warning fw-bold">Aprovações de Contas</a>
            <a href="aprovar_notas.php" class="text-warning fw-bold">Aprovações de Notas</a>
        <?php endif; ?>
    </div>

    <!-- Conteúdo Principal -->
    <div class="content">
        <h1>Bem-vindo ao MyNotes</h1>
        <p class="lead">Organize suas anotações de forma fácil e prática.</p>

        <h2>Publicações Aprovadas</h2>
        <?php
        // Conexão com a base de dados
        $conn = new mysqli("localhost", "root", "", "notesdb");

        // Verificar conexão
        if ($conn->connect_error) {
            die("Erro de conexão: " . $conn->connect_error);
        }

        // Obter as publicações aprovadas da mesma escola do utilizador
        $sql = "SELECT n.id, n.title, n.content, nf.file_path 
        FROM notes n 
        LEFT JOIN note_files nf ON n.id = nf.note_id 
        WHERE n.private_status = 0 AND n.escola = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $escola);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar se existem publicações
        if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row["title"]); ?></h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($row["content"])); ?></p>
                        <?php if (!empty($row["file_path"])): ?>
                            <div class="d-flex align-items-center">
                                <span class="me-3"><?php echo htmlspecialchars(basename($row["file_path"])); ?></span>
                                <a href="<?php echo htmlspecialchars($row["file_path"]); ?>" class="btn btn-primary"
                                    download>Descarregar Documento</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            endwhile;
        else:
            ?>
            <p>Não há publicações aprovadas disponíveis no momento.</p>
            <?php
        endif;

        // Fechar conexão
        $stmt->close();
        $conn->close();
        ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>