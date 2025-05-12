<?php
/**
 * datosinterfaz.php
 *
 * Este archivo se encarga de reunir y preparar los datos necesarios para la
 * visualización en la interfaz de la aplicación Mintcream.
 * 
 * Funcionalidades incluidas:
 * - Obtención del listado de temas, ordenados de forma descendente.
 * - Extracción de los hilos correspondientes al tema seleccionado (si existe).
 * - Extracción de las publicaciones correspondientes al hilo seleccionado.
 * - Si se solicita responder a una publicación, se obtiene el mensaje a responder.
 * - Listado de todos los usuarios (cuando se accede al panel de administración y el usuario es Superadmin).
 *
 * @package Mintcream
 */

// Verificar que la conexión a la base de datos está establecida.
if (!isset($pdo)) {
    die("Error: La conexión a la base de datos no se ha inicializado.");
}

/**
 * 1. Obtener el listado de temas.
 */
try {
    $stmt = $pdo->query("SELECT * FROM temas ORDER BY id DESC");
    $temas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    error_log('Error al obtener temas: ' . $ex->getMessage());
    $temas = [];  // En caso de error, usar un array vacío.
}

/**
 * 2. Obtener el ID del tema seleccionado (si se ha indicado en la URL).
 */
$temaSeleccionado = isset($_GET['tema_id']) ? (int)$_GET['tema_id'] : 0;
$hilos = [];
if ($temaSeleccionado > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM hilos WHERE tema_id = :tema_id ORDER BY id DESC");
        $stmt->execute([':tema_id' => $temaSeleccionado]);
        $hilos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        error_log('Error al obtener hilos para el tema ' . $temaSeleccionado . ': ' . $ex->getMessage());
    }
}

/**
 * 3. Obtener el ID del hilo seleccionado (si se ha indicado en la URL).
 */
$hiloSeleccionado = isset($_GET['hilo_id']) ? (int)$_GET['hilo_id'] : 0;
$publicaciones = [];
if ($hiloSeleccionado > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username 
            FROM publicaciones p
            JOIN users u ON p.user_id = u.id
            WHERE p.hilo_id = :hilo_id
            ORDER BY p.id ASC
        ");
        $stmt->execute([':hilo_id' => $hiloSeleccionado]);
        $publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        error_log('Error al obtener publicaciones para el hilo ' . $hiloSeleccionado . ': ' . $ex->getMessage());
    }
}

/**
 * 4. Si se especifica una respuesta a una publicación, obtener el mensaje a responder.
 */
$reply_message = null;
if (isset($_GET['reply'])) {
    $replyId = (int)$_GET['reply'];
    if ($replyId > 0) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM publicaciones WHERE id = :reply_id");
            $stmt->execute([':reply_id' => $replyId]);
            $reply_message = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            error_log('Error al obtener el mensaje de respuesta para ID ' . $replyId . ': ' . $ex->getMessage());
        }
    }
}

/**
 * 5. Si se accede al panel de administración (acción "panel") y el usuario es Superadmin,
 *    obtener la lista de usuarios registrados.
 */
$lista_usuarios = [];
if (isset($accion) && $accion === 'panel' && function_exists('getUserRole') && getUserRole() === 1) {
    try {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
        $lista_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        error_log('Error al obtener la lista de usuarios: ' . $ex->getMessage());
    }
}
?>