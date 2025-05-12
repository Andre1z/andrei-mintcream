<?php
/**
 * inc/procesamientodeformularios.php
 *
 * Procesa todas las solicitudes enviadas mediante formularios en la aplicación Mintcream.
 * Las acciones incluyen login, logout, registro, creación de temas, hilos, publicaciones
 * y operaciones en el panel de administración.
 *
 * NOTA: En producción, se recomienda utilizar password_hash() y password_verify() para manejar
 *       las contraseñas, además de validaciones adicionales.
 *
 * @package Mintcream
 */

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuarioInput = trim($_POST['username'] ?? '');
            $claveInput   = trim($_POST['password'] ?? '');
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $usuarioInput]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($usuario && $usuario['password'] === $claveInput) { // En producción: usar password_verify()
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['username'] = $usuario['username'];
                $_SESSION['role'] = $usuario['role'];
            } else {
                echo "<p style='color:red;'>Usuario o contraseña incorrectos</p>";
            }
        }
        break;

    case 'logout':
        session_destroy();
        header("Location: index.php");
        exit;

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevoUsuario = trim($_POST['username'] ?? '');
            $nuevaClave   = trim($_POST['password'] ?? '');
            $nombre       = trim($_POST['name'] ?? '');
            $correo       = trim($_POST['email'] ?? '');
            if (empty($nuevoUsuario) || empty($nuevaClave)) {
                echo "<p style='color:red;'>El usuario y la contraseña son obligatorios.</p>";
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
                $stmt->execute([':username' => $nuevoUsuario]);
                if ($stmt->fetchColumn() > 0) {
                    echo "<p style='color:red;'>El nombre de usuario ya existe.</p>";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, name, email)
                                           VALUES (:username, :password, :role, :name, :email)");
                    $stmt->execute([
                        ':username' => $nuevoUsuario,
                        ':password' => $nuevaClave, // En producción: usar password_hash()
                        ':role'     => 4,
                        ':name'     => $nombre,
                        ':email'    => $correo
                    ]);
                    // Iniciar sesión automáticamente
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
                    $stmt->execute([':username' => $nuevoUsuario]);
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($usuario) {
                        $_SESSION['user_id'] = $usuario['id'];
                        $_SESSION['username'] = $usuario['username'];
                        $_SESSION['role'] = $usuario['role'];
                    }
                    header("Location: index.php");
                    exit;
                }
            }
        }
        break;

    case 'crear_tema':
        if (checkRole(2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $tituloTema = trim($_POST['titulo'] ?? '');
            if (!empty($tituloTema)) {
                $stmt = $pdo->prepare("INSERT INTO temas (titulo) VALUES (:titulo)");
                $stmt->execute([':titulo' => $tituloTema]);
            }
        }
        break;

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

    case 'crear_publicacion':
        if (checkRole(4) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $hiloId    = (int)($_POST['hilo_id'] ?? 0);
            $contenido = trim($_POST['contenido'] ?? '');
            $padreId   = (int)($_POST['parent_id'] ?? 0);
            $usuarioId = $_SESSION['user_id'] ?? 0;
            $imagen    = null; // Valor por defecto: sin imagen

            // Procesar la imagen (si se sube una)
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath   = $_FILES['imagen']['tmp_name'];
                $fileName      = $_FILES['imagen']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExts   = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($fileExtension, $allowedExts)) {
                    // Generar un nombre único para evitar colisiones
                    $newFileName = uniqid('img_', true) . '.' . $fileExtension;
                    $uploadDir   = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $destPath = $uploadDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $imagen = $newFileName;
                    }
                }
            }

            if ($hiloId > 0 && !empty($contenido) && $usuarioId > 0) {
                $stmt = $pdo->prepare("INSERT INTO publicaciones (hilo_id, user_id, parent_id, contenido, imagen, fecha)
                                       VALUES (:hilo_id, :user_id, :parent_id, :contenido, :imagen, :fecha)");
                $stmt->execute([
                    ':hilo_id'    => $hiloId,
                    ':user_id'    => $usuarioId,
                    ':parent_id'  => $padreId,
                    ':contenido'  => $contenido,
                    ':imagen'     => $imagen,
                    ':fecha'      => date('Y-m-d H:i:s')
                ]);

                // Redirigir al hilo correspondiente
                $stmtTema = $pdo->prepare("SELECT tema_id FROM hilos WHERE id = :hilo_id");
                $stmtTema->execute([':hilo_id' => $hiloId]);
                $temaId = $stmtTema->fetchColumn();
                header("Location: index.php?tema_id=$temaId&hilo_id=$hiloId");
                exit;
            }
        }
        break;

    case 'panel':
        if (getUserRole() === 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                            ':password' => $claveNueva, // En producción: usar password_hash()
                            ':role'     => $rolNuevo,
                            ':name'     => $nombreNuevo,
                            ':email'    => $emailNuevo
                        ]);
                    }
                }
                if (isset($_POST['panel_action']) && $_POST['panel_action'] === 'delete_user') {
                    $usuarioID = (int)($_POST['user_id'] ?? 0);
                    if ($usuarioID > 0 && $usuarioID !== (int)($_SESSION['user_id'] ?? 0)) {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                        $stmt->execute([':id' => $usuarioID]);
                    }
                }
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