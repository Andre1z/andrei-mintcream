<?php
/**
 * index.php
 *
 * Punto de entrada de la aplicación Mintcream.
 * Inicializa la sesión, carga configuraciones esenciales y renderiza la interfaz básica.
 *
 * @package Mintcream
 */

// Iniciar la sesión para manejar el estado del usuario.
session_start();

// Incluir configuraciones, conexión a la base de datos y utilidades.
require_once __DIR__ . '/inc/inicializarsqlite.php';       // Conexión a SQLite y creación de tablas.
require_once __DIR__ . '/inc/funciones.php';                // Funciones utilitarias y manejo de roles.
require_once __DIR__ . '/inc/procesamientodeformularios.php';// Procesamiento de formularios (login, registro, etc.).
require_once __DIR__ . '/inc/datosinterfaz.php';            // Obtención de datos para la interfaz.

// Incluir cada bloque de la interfaz (estructura HTML, cabecera, contenido y pie de página).
require_once __DIR__ . '/bloques/cabeza.php';       // Estructura inicial del HTML (<!DOCTYPE>, <head>, etc.).
require_once __DIR__ . '/bloques/cabecera.php';      // Encabezado y barra de navegación.
require_once __DIR__ . '/bloques/principal.php';     // Contenido principal dinámico según la acción.
require_once __DIR__ . '/bloques/piedepagina.php';   // Pie de página y cierre de la estructura HTML.