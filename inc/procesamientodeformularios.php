<?php
/**
 * procesamientodeformularios.php
 *
 * Este archivo procesa todas las solicitudes enviadas mediante formularios en la aplicación Mintcream.
 * Según el parámetro "accion" recibido vía GET, se ejecuta la lógica correspondiente a:
 * - Login / Logout
 * - Registro de usuarios
 * - Creación de temas, hilos y publicaciones
 * - Operaciones del panel de administración (crear, eliminar, actualizar usuarios)
 *
 * NOTA: En producción, se recomienda mejorar la seguridad implementando password_hash() y password_verify()
 *       para el manejo de contraseñas, entre otras validaciones.
 *
 * @package Mintcream
 */

// Obtiene la acción solicitada desde la URL (por ejemplo, ?accion=login)
$accion = $_GET['accion'] ?? '';

// Procesamos la acción mediante un switch
switch ($accion) {

    /**
     * Caso "login": procesa el inicio de sesión de un usuario.
     */
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recupera y limpia las entradas del formulario
            $usuarioInput = trim($_POST['username'] ?? '');
            $claveInput   = trim($_POST['password'] ?? '');

            // Consulta el usuario en la base de datos
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $usuarioInput]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Comprueba la contraseña (nota: en producción usar password_verify())
            if ($usuario && $usuario['password'] === $claveInput) {
                // Almacena los datos del usuario en la sesión para mantener el estado autenticado
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['username'] = $usuario['username'];
                $_SESSION['role'] = $usuario['role'];
            } else {
                // Se notifica al usuario que las credenciales son incorrectas
                echo "<p style='color:red;'>Usuario o contraseña incorrectos</p>";
            }
        }
        break;

    /**
     * Caso "logout": cierra la sesión del usuario y lo redirige a la página principal.
     */
    case 'logout':
        // Destruye la sesión actual
        session_destroy();
        header("Location: index.php");
        exit;
        break;

    /**
     * Caso "register": procesa el registro de un nuevo usuario.
     */
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recoge y limpia los datos ingresados en el formulario
            $nuevoUsuario = trim($_POST['username'] ?? '');
            $nuevaClave   = trim($_POST['password'] ?? '');
            $nombre       = trim($_POST['name'] ?? '');
            $correo       = trim($_POST['email'] ?? '');

            // Se valida que se hayan ingresado los campos obligatorios
            if (empty($nuevoUsuario) || empty($nuevaClave)) {
                echo "<p style='color:red;'>El usuario y la contraseña son obligatorios.</p>";
            } else {
                // Verifica que el nombre de usuario no exista ya en la base de datos
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
                $stmt->execute([':username' => $nuevoUsuario]);
                if ($stmt->fetchColumn() > 0) {
                    echo "<p style='color:red;'>El nombre de usuario ya existe.</p>";
                } else {
                    // Inserta el nuevo usuario con rol por defecto (4 = Usuario)
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, name, email)
                                           VALUES (:username, :password, :role, :name, :email)");
                    $stmt->execute([
                        ':username' => $nuevoUsuario,
                        ':password' => $nuevaClave, // En producción: usar password_hash()
                        ':role'     => 4,
                        ':name'     => $nombre,
                        ':email'    => $correo
                    ]);
                    // Inicia la sesión automáticamente al registrar al nuevo usuario
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
                    $stmt->execute([':username' => $nuevoUsuario]);
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($usuario) {
                        $_SESSION['user_id'] = $usuario['id'];
                        $_SESSION['username'] = $usuario['username'];
                        $_SESSION['role'] = $usuario['role'];
                    }
                    // Redirige a la página principal
                    header("Location: index.php");
                    exit;
                }
            }
        }
        break;

    /**
     * Caso "crear_tema": permite crear un nuevo tema en el foro.
     * Solo se permite si el usuario posee rol igual o superior a Admin Temas (rol <= 2).
     */
    case 'crear_tema':
        if (checkRole(2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $tituloTema = trim($_POST['titulo'] ?? '');
            if (!empty($tituloTema)) {
                $stmt = $pdo->prepare("INSERT INTO temas (titulo) VALUES (:titulo)");
                $stmt->execute([':titulo' => $tituloTema]);
            }
        }
        break;

    /**
     * Caso "crear_hilo": permite crear un nuevo hilo dentro de un tema.
     * Solo es accesible para usuarios con rol igual o superior a Admin Hilos (rol <= 3).
     */
    case 'crear_hilo':
        if (checkRole(3) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $temaId = (int)($_POST['tema_id'] ?? 0);
            $tituloHilo = trim($_POST['titulo'] ?? '');
            if ($temaId > 0 && !empty($tituloHilo)) {
                $stmt = $pdo->prepare("INSERT INTO hilos (tema_id, titulo) VALUES (:tema_id, :titulo)");
                $stmt->execute([':tema_id' => $temaId, ':titulo' => $tituloHilo]);
            }
        }
        break;

    /**
     * Caso "crear_publicacion": procesa la creación de un mensaje o respuesta dentro de un hilo.
     * Requiere que el usuario tenga rol igual o superior a Usuario (rol <= 4).
     */
    case 'crear_publicacion':
        if (checkRole(4) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $hiloId     = (int)($_POST['hilo_id'] ?? 0);
            $contenido  = trim($_POST['contenido'] ?? '');
            $padreId    = (int)($_POST['parent_id'] ?? 0);
            $usuarioId  = $_SESSION['user_id'] ?? 0;

            if ($hiloId > 0 && !empty($contenido) && $usuarioId > 0) {
                // Inserta la nueva publicación y registra la fecha actual
                $stmt = $pdo->prepare("INSERT INTO publicaciones (hilo_id, user_id, parent_id, contenido, fecha)
                                       VALUES (:hilo_id, :user_id, :parent_id, :contenido, :fecha)");
                $stmt->execute([
                    ':hilo_id'    => $hiloId,
                    ':user_id'    => $usuarioId,
                    ':parent_id'  => $padreId,
                    ':contenido'  => $contenido,
                    ':fecha'      => date('Y-m-d H:i:s')
                ]);

                // Recupera el ID del tema asociado al hilo para la redirección
                $stmtTema = $pdo->prepare("SELECT tema_id FROM hilos WHERE id = :hilo_id");
                $stmtTema->execute([':hilo_id' => $hiloId]);
                $temaId = $stmtTema->fetchColumn();

                // Redirige de nuevo al hilo para que el usuario permanezca allí
                header("Location: index.php?tema_id=$temaId&hilo_id=$hiloId");
                exit;
            }
        }
        break;

    /**
     * Caso "panel": gestiona las operaciones del panel de administración para Superadmin.
     * Se procesan las acciones para:
     * - Crear un nuevo usuario (panel_action = create_user)
     * - Eliminar un usuario (panel_action = delete_user)
     * - Actualizar el rol de un usuario (panel_action = update_role)
     */
    case 'panel':
        // Solo permite el acceso si el usuario es Superadmin (rol 1)
        if (getUserRole() === 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                // Acción para crear nuevo usuario
                if (isset($_POST['panel_action']) && $_POST['panel_action'] === 'create_user') {
                    $usuarioNuevo = trim($_POST['username'] ?? '');
                    $claveNueva   = trim($_POST['password'] ?? '');
                    $rolNuevo     = (int)($_POST['role'] ?? 5);
                    $nombreNuevo  = trim($_POST['name'] ?? '');
                    $emailNuevo   = trim($_POST['email'] ?? '');

                    if (!empty($usuarioNuevo) && !empty($claveNueva)) {
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, name, email)
                                               VALUES (:username, :password, :role, :name, :email)");
                        $stmt->execute([
                            ':username' => $usuarioNuevo,
                            ':password' => $claveNueva, // En producción: password_hash() para mayor seguridad
                            ':role'     => $rolNuevo,
                            ':name'     => $nombreNuevo,
                            ':email'    => $emailNuevo
                        ]);
                    }
                }

                // Acción para eliminar un usuario (evita eliminar al usuario actualmente logueado)
                if (isset($_POST['panel_action']) && $_POST['panel_action'] === 'delete_user') {
                    $usuarioID = (int)($_POST['user_id'] ?? 0);
                    if ($usuarioID > 0 && $usuarioID !== (int)($_SESSION['user_id'] ?? 0)) {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                        $stmt->execute([':id' => $usuarioID]);
                    }
                }

                // Acción para actualizar el rol de un usuario
                if (isset($_POST['panel_action']) && $_POST['panel_action'] === 'update_role') {
                    $usuarioID = (int)($_POST['user_id'] ?? 0);
                    $rolActualizado = (int)($_POST['new_role'] ?? 5);
                    if ($usuarioID > 0) {
                        $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
                        $stmt->execute([
                            ':role' => $rolActualizado,
                            ':id'   => $usuarioID
                        ]);
                    }
                }
            }
        }
        break;
}
?>