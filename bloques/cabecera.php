<?php
/**
 * cabecera.php
 *
 * Genera el encabezado de la página que incluye el logotipo, el título y
 * la barra de navegación. Muestra distintos elementos en función
 * de si el usuario está autenticado o no.
 *
 * @package Mintcream
 */
?>
<header>
    <nav class="main-nav">
        <div class="nav-brand">
            <img src="mintcream.png" alt="Logo Mintcream" id="logo">
            <h1><a href="index.php">Andrei | Mintcream</a></h1>
        </div>
        <div class="nav-actions">
            <?php if (isset($_SESSION['username'])): ?>
                <!-- Si el usuario está autenticado, mostrar sus datos y opciones -->
                <div class="user-panel">
                    <span>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                    <span>(<?php echo getRoleName(getUserRole()); ?>)</span>
                    <?php if (getUserRole() === 1): ?>
                        <a href="?accion=panel" class="btn">Panel</a>
                    <?php endif; ?>
                    <a href="?accion=logout" class="btn">Cerrar Sesión</a>
                </div>
            <?php else: ?>
                <!-- Si el usuario no está autenticado, mostrar el formulario de login y el enlace de registro -->
                <form method="POST" action="?accion=login" class="login-form">
                    <input type="text" name="username" placeholder="Usuario" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit" class="btn">Entrar</button>
                </form>
                <a href="?accion=register" class="btn">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main>