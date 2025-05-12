<?php
/**
 * inicializarsqlite.php
 *
 * Inicializa la conexión a la base de datos SQLite y crea las tablas esenciales
 * para el funcionamiento de la aplicación Mintcream.
 *
 * Funcionalidades:
 * - Conecta a una base de datos SQLite ubicada en "../databases/mintcream.db".
 * - Crea las tablas: users, temas, hilos y publicaciones.
 * - Inserta un usuario superadmin inicial (si no existe).
 *
 * Notas:
 * - En producción se recomienda utilizar funciones de hashing para contraseñas.
 * - El uso de claves foráneas facilita la integridad relacional entre tablas y se
 *   progama el borrado en cascada (ON DELETE CASCADE) para mantener la coherencia.
 *
 * @package Mintcream
 */

// Definir la ruta de la base de datos (se asume que existe la carpeta "databases" un nivel arriba)
$db_file = __DIR__ . '/../databases/mintcream.db';

try {
    // Crear una instancia de PDO para conectarse a la base de datos SQLite.
    $pdo = new PDO("sqlite:$db_file");
    // Configurar PDO para lanzar excepciones en caso de error.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear la tabla de usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role INTEGER NOT NULL,
        name TEXT,
        email TEXT
    )");

    // Crear la tabla de temas
    $pdo->exec("CREATE TABLE IF NOT EXISTS temas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL
    )");

    // Crear la tabla de hilos, relacionándola con la tabla de temas
    $pdo->exec("CREATE TABLE IF NOT EXISTS hilos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tema_id INTEGER NOT NULL,
        titulo TEXT NOT NULL,
        FOREIGN KEY (tema_id) REFERENCES temas(id) ON DELETE CASCADE
    )");

    // Crear la tabla de publicaciones (mensajes)
    $pdo->exec("CREATE TABLE IF NOT EXISTS publicaciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        hilo_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        parent_id INTEGER DEFAULT 0,
        contenido TEXT NOT NULL,
        fecha DATETIME NOT NULL,
        FOREIGN KEY (hilo_id) REFERENCES hilos(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Insertar el usuario superadmin inicial si no existe
    // Datos: username = 'jocarsa', password = 'jocarsa', role = 1, name = 'Jose Vicente Carratala', email = 'info@josevicentecarratala'
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute([':username' => 'jocarsa']);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, name, email)
                               VALUES (:username, :password, :role, :name, :email)");
        $stmt->execute([
            ':username' => 'jocarsa',
            ':password' => 'jocarsa', // Nota: en producción usar password_hash()
            ':role'     => 1,
            ':name'     => 'Jose Vicente Carratala',
            ':email'    => 'info@josevicentecarratala'
        ]);
    }
} catch (PDOException $e) {
    // En caso de error, mostrar el mensaje y terminar la ejecución.
    echo "Error con la base de datos: " . $e->getMessage();
    exit;
}
?>