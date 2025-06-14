<?php
session_start();
require '../back/conection.php';

$msgError = "";
if (isset($_SESSION['error'])) {
    $msgError = $_SESSION['error'];
    unset($_SESSION['error']);
}

/*
Usamos el IF para asegurarnos de que el método http usado en la petición sea "POST". Por ejemplo, si 
un usuario entrase a "register.php" escribiendo la ruta del archivo en el navegador le daría el error del 
else.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    if ($action === 'login') {
        $username = trim($_POST["username"]);
        $password = $_POST["password"];
        // Validamos que los campos no estén vacíos
        if (empty($username) || empty($password)) {
            $_SESSION["error"] = "Usuario y contraseña son obligatorios.";
        } else {
            // Verificamos que el usuario exista y que la contraseña sea correcta
            try {
                $select = $dbConection->prepare("SELECT user_id, username, password FROM Users WHERE username = :username");
                $select->execute([':username' => $username]);
                $user = $select->fetch(PDO::FETCH_ASSOC);
                if ($user && password_verify($password, $user["password"])) {
                    // Iniciamos la sesión y guardamos los datos
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["username"] = $user["username"];
                    // Establecemos una variable de sesión para el pop-up en home.
                    setcookie("logInMessage", "Has iniciado sesión correctamente", time() + 5, "/");
                    header('Location: home.php');
                    exit;
                } else {
                    $_SESSION["error"] = "Usuario o contraseña incorrectos.";
                    header('Location: index.php');
                    exit;
                }
            } catch (PDOException $e) {
                $_SESSION["error"] = "Error al iniciar sesión: " . $e->getMessage();
            }
        }
    } elseif ($action === 'register') {
        $username = trim($_POST["username"]); // Usamos trim para quitar posibles espacios 
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION["error"] = "Todos los campos son obligatorios.";
        } else {
            try {
                // Validamos que el usuario o email no existan ya
                $check = $dbConection->prepare("SELECT COUNT(*) FROM Users WHERE username = :username OR email = :email");
                $check->execute([':username' => $username, ':email' => $email]);
                if ($check->fetchColumn() > 0) {
                    $_SESSION["error"] = "Ese nombre de usuario o email ya está en uso.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insertamos al nuevo usuario
                    $insert = $dbConection->prepare("INSERT INTO Users (username, email, password) VALUES (:username, :email, :password)");
                    $insert->execute([
                        ':username' => $username,
                        ':email' => $email,
                        ':password' => $hashed_password
                    ]);

                    $_SESSION["user_id"] = $dbConection->lastInsertId();
                    $_SESSION["username"] = $username;
                    setcookie("registerMessage", "Te has Registrado Correctamente", time() + 5, "/");
                    header("Location: home.php");
                    exit;
                }
            } catch (PDOException $e) {
                $_SESSION["error"] = "Error al registrar usuario: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION["error"] = "Acción no válida.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BDD-index</title>
    <link href="https://fonts.googleapis.com/css2?family=Almendra&family=Cormorant+Garamond:wght@700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../src/styles/css/styles.css" />
    <script src="../src/scripts/main.js"></script>
    <link rel="shortcut icon" href="../src/img/logo.png" />
</head>

<body> <?php
    if (isset($_COOKIE['logOutMessage'])) {
            ?>
                <div id="popup" class="popup error">
                    Sesión Cerrada Correctamente
                </div> <?php
        setcookie("logOutMessage", "", time() - 3600, "/");        
    }
    if (isset($_SESSION['error'])) {
        $msgError = $_SESSION['error'];
    }
    if (!empty($msgError)): ?>
    <div id="popup">
            <?php echo htmlspecialchars($msgError); ?>
        </div>
    <?php endif;?>
    <div id="body">
        <header>
            <h1 id="titleLogIn"> <img src="../src/img/logo.png" alt="Logo" class="title-icon" /> La Biblioteca del
                Dragón</h1>
        </header>
        <div id="Texto">
            Bienvenido a La Biblioteca del Dragón, tu herramienta definitiva para gestionar campañas y personajes en
            Dungeons & Dragons 5ª Edición.<br> Diseñada para directores de juego y jugadores por igual
            , esta plataforma no solo te permite organizar tus one-shots o campañas, sino que también incluye un potente
            creador de fichas de personaje totalmente compatible
            con las reglas de D&D 5.0 <br>
            Ya sea que estés dando vida a un nuevo héroe o llevando el control de una épica campaña, aquí encontrarás
            todo lo necesario para que tu historia cobre vida. <br />
            <img src="../src/img/book.png" alt="logoLibro" class="DMSection" /><br> Si eres un director de juego,
            en La Biblioteca del Dragón podrás encontrar un gestor de campañas que te permitirá tener tus campañas bien
            organizadas, además de tener acceso a las fichas de tus jugadores, a un tirador de dados y al log de
            campaña, donde tanto tú como tus jugadores podréis ir escribiendo todo lo que suceda en esa historia que
            tenías pensada desde hace tiempo y que no sobrevivió al encuentro con tus jugadores.<br>
            <img src="../src/img/sword.png" alt="logoEspada" class="playerSection" /><br> Si, por otro lado, has
            logrado que otra persona dirija tus partidas. En esta página podrás unirte a las campañas que tu DM haya
            creado, pero más importante, podrás crear tus propias fichas de personaje en nuestro editor de fichas. Ya
            juegues en formato presencial u on-line, esta página te permitirá tirar dados para pasar esas tiradas de
            habilidad o acertar tus ataques contra cualquier monstruo que tu director de juego decida poner en tu
            camino.<br>
            <img id="banner" src="../src/img/dados.jpg" alt="bannerDados">
        </div>
        <div>


        </div>
        <div id="Log">
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="login">
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="user" name="username" required />
                <label for="password">Contraseña</label>
                <input type="password" id="pass" name="password" required />

                <button class="submit" type="submit" id="goInto">Iniciar sesión</button>
            </form>

            <div class="botones">
                <button id="goBack">Volver</button>
            </div>
        </div>
        <div id="New">
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="register">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required />
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="newName" name="username" required />
                <label for="password">Contraseña</label>
                <input type="password" id="newPass" name="password" required />

                <button class="submit" type="submit" id="newGoInto">Registrarse</button>
            </form>

            <div class="botones">
                <button id="newGoBack">Volver</button>
            </div>
        </div>
        <div id="Nosotros">
            <h1>Nosotros</h1>
            <div>
                Somos tres estudiantes de Desarrollo de Aplicaciones Web que hemos unido nuestras pasiones para crear
                algo que realmente usaríamos día a día: un gestor de campañas de Dungeons & Dragons.
                Este proyecto, nacido como nuestro Trabajo de Fin de Grado, es también el fruto de años de aventuras
                compartidas, dados lanzados y mundos imaginados.
                Como jugadores y directores de juego, sabemos lo que una buena herramienta puede aportar a una campaña,
                por eso hemos diseñado esta plataforma pensando en la comodidad, flexibilidad y creatividad que todo
                amante del rol necesita.
                Esta es nuestra forma de aportar al hobby que tanto nos ha dado.<br><br>
                <h3>Jorge Pulgar</h3>
                Desarrollador backend y máster de D&D desde hace más de seis años. Siempre está buscando nuevas formas
                de automatizar tareas y optimizar la experiencia de juego detrás de la pantalla. Su obsesión por la
                organización ha sido clave para estructurar el corazón técnico del proyecto.
                <br><br>
                <h3>Jaime Granja</h3>
                Responsable de diseño y frontend. Amante de las buenas historias y de las interfaces limpias y
                funcionales. Se asegura de que cada rincón del gestor sea tan fácil de usar como agradable a la vista.
                Su experiencia, tanto como director de juego como la de jugador le ha permitido pensar siempre en la
                usabilidad real para las mesas de juego.
                <br><br>
                <h3>Alejandro Valencia</h3>
                Encargado de la integración, pruebas y documentación. Jugador empedernido y creador de personajes
                excéntricos, ha sido la voz del usuario durante todo el desarrollo. Gracias a su ojo crítico, el
                proyecto ha pasado de ser una buena idea a una herramienta robusta y fiable.
            </div>
            <button class="botones" id="nosotrosGoBack">Volver</button>
        </div>
        <div id="botones" class="botones">
            <button id="LogIn">Iniciar Sesión</button>
            <button id="Register">Registrarme</button>
            <button id="Us">Nosotros</button>
        </div>

        <footer>
            <p>&copy; 2025 La Biblioteca del Dragón. Todos los derechos reservados.</p>
            <p>
                Esta página web no está afiliada ni respaldada por Wizards of the Coast.
                Dungeons & Dragons y sus logotipos son marcas registradas de Wizards of the Coast LLC.
                El contenido presentado tiene fines educativos y de entretenimiento.
            </p>
            <p>
                Diseño y desarrollo por Jorge Pulgar, Jaime Granja y Alejandro Valencia. Para contacto o sugerencias, escribe a BibliotecaDragon@gmail.com.
            </p>
        </footer>
    </div>
</body>

</html>
