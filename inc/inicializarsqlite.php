<?php
/**
 * inicializarsqlite.php
 *
 * Este archivo se encarga de establecer la conexión con la base de datos SQLite,
 * crear las tablas esenciales para la aplicación Mintcream y asegurar que la
 * estructura incluye la columna 'imagen' en la tabla de publicaciones.
 *
 * Las tablas que se crean son:
 * - users: Para almacenar los usuarios (con nombre de usuario, contraseña, rol, etc.).
 * - temas: Para almacenar los temas del foro.
 * - hilos: Para almacenar los hilos de cada tema, con una relación (foreign key) a temas.
 * - publicaciones: Para almacenar los mensajes o publicaciones, con la columna 'imagen'
 *   que almacenará el nombre del archivo (si se sube alguno), y relacionando cada publicación
 *   con un hilo y un usuario.
 *
 * Nota: Si ya existe el archivo de la base de datos y las tablas fueron creadas con un esquema anterior
 * que no incluya la columna 'imagen', deberás actualizar manualmente la base de datos (por ejemplo,
 * eliminando el archivo o usando ALTER TABLE).
 *
 * @package Mintcream
 */

// Definir la ruta absoluta al archivo de la base de datos
$db_file = __DIR__ . '/../databases/mintcream.db';

try {
    // Crear una instancia de PDO para conectarse a la base de datos SQLite.
    $pdo = new PDO("sqlite:$db_file");
    
    // Configurar PDO para que lance excepciones en caso de error.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // -----------------------
    // Crear la tabla de usuarios
    // -----------------------
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role INTEGER NOT NULL,
        name TEXT,
        email TEXT
    )");

    // -----------------------
    // Crear la tabla de temas
    // -----------------------
    $pdo->exec("CREATE TABLE IF NOT EXISTS temas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL
    )");

    // -----------------------
    // Crear la tabla de hilos
    // -----------------------
    $pdo->exec("CREATE TABLE IF NOT EXISTS hilos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tema_id INTEGER NOT NULL,
        titulo TEXT NOT NULL,
        FOREIGN KEY (tema_id) REFERENCES temas(id) ON DELETE CASCADE
    )");

    // -----------------------
    // Crear la tabla de publicaciones
    // Se añade la columna 'imagen' para almacenar el nombre del archivo subido.
    // -----------------------
    $pdo->exec("CREATE TABLE IF NOT EXISTS publicaciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        hilo_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        parent_id INTEGER DEFAULT 0,
        contenido TEXT NOT NULL,
        imagen TEXT,
        fecha DATETIME NOT NULL,
        FOREIGN KEY (hilo_id) REFERENCES hilos(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // -----------------------
    // Insertar el usuario Superadmin inicial, si no existe.
    // Datos: usuario 'admin', contraseña 'admin', rol 1, nombre y email.
    // -----------------------
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute([':username' => 'admin']);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, name, email)
                               VALUES (:username, :password, :role, :name, :email)");
        $stmt->execute([
            ':username' => 'admin',
            ':password' => 'admin', // En producción usar password_hash() para mayor seguridad.
            ':role'     => 1,
            ':name'     => 'Admin',
            ':email'    => 'admin@admin.com'
        ]);
    }
    
} catch (PDOException $e) {
    // En caso de error, se muestra el mensaje y se detiene la ejecución.
    echo "Error con la base de datos: " . $e->getMessage();
    exit;
}
?>