<?php
/**
 * funciones.php
 *
 * Este archivo contiene funciones auxiliares para el manejo de roles de usuario y
 * comprobaciones de seguridad en la aplicación Mintcream.
 *
 * Funciones incluidas:
 * - getUserRole(): Obtiene el rol actual del usuario desde la sesión.
 * - getRoleName(): Devuelve una representación legible del rol según su código.
 * - checkRole(): Verifica si el usuario tiene un rol con privilegios suficientes.
 *
 * Los roles se definen de la siguiente forma:
 * 1 - Superadmin
 * 2 - Admin Temas
 * 3 - Admin Hilos
 * 4 - Usuario
 * 5 - Visitante (por defecto)
 *
 * @package Mintcream
 */

/**
 * Obtiene el rol actual del usuario a partir de la variable de sesión.
 *
 * Si no existe una sesión activa o el rol no está definido, se retorna el valor 5 (Visitante).
 *
 * @return int El código del rol del usuario.
 */
function getUserRole() {
    return (isset($_SESSION['role']) && is_numeric($_SESSION['role'])) ? (int)$_SESSION['role'] : 5;
}

/**
 * Convierte el código numérico de un rol en una cadena descriptiva.
 *
 * @param int $role El código del rol a convertir.
 *
 * @return string El nombre del rol correspondiente.
 */
function getRoleName($role) {
    $role = (int)$role;
    switch ($role) {
        case 1:
            return "Superadmin";
        case 2:
            return "Admin Temas";
        case 3:
            return "Admin Hilos";
        case 4:
            return "Usuario";
        default:
            return "Visitante";
    }
}

/**
 * Comprueba si el usuario actual tiene suficientes privilegios para una acción.
 *
 * Dado que los roles son asignados numéricamente de tal manera que un valor más bajo
 * indica mayor poder, esta función valida que el rol del usuario sea menor o igual
 * que el rol mínimo requerido.
 *
 * Por ejemplo, si se requiere un rol mínimo de 3, un usuario con rol 2 o 1 tendrá permiso.
 *
 * @param int $minRole El rol mínimo permitido para realizar una acción.
 *
 * @return bool True si el usuario tiene el rol adecuado o superior, o false en caso contrario.
 */
function checkRole($minRole) {
    return getUserRole() <= (int)$minRole;
}
?>