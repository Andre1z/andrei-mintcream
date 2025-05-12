<?php
/**
 * bloques/principal.php
 *
 * Renderiza la sección principal de la interfaz. Dependiendo del valor
 * de la variable $accion se muestran:
 * - El formulario de registro.
 * - El panel de administración (para Superadmin).
 * - La interfaz del foro con lista de temas, hilos y publicaciones.
 *
 * Se ha actualizado el bloque de publicaciones para permitir la inserción de imágenes
 * y para mostrar una flecha (→) junto al nombre del usuario en el reply.
 *
 * @package Mintcream
 */
?>

<?php if ($accion === 'register'): ?>
    <!-- Registro de Usuario -->
    <section class="registration-section">
        <h2>Registro de Usuario</h2>
        <form action="?accion=register" method="POST" class="registration-form">
            <input type="text" name="username" placeholder="Nombre de usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="text" name="name" placeholder="Nombre completo">
            <input type="email" name="email" placeholder="Correo electrónico">
            <button type="submit" class="btn-primary">Registrarse</button>
        </form>
    </section>

<?php elseif ($accion === 'panel' && getUserRole() === 1): ?>
    <!-- Panel de Administración para Superadmin -->
    <section class="admin-panel">
        <h2>Panel de Administración</h2>
        <div class="create-user">
            <h3>Crear Nuevo Usuario</h3>
            <form method="POST" action="?accion=panel" class="form-row">
                <input type="hidden" name="panel_action" value="create_user">
                <div>
                    <label for="nuevo_usuario">Usuario:</label>
                    <input type="text" name="username" id="nuevo_usuario" required>
                </div>
                <div>
                    <label for="nuevo_password">Contraseña:</label>
                    <input type="text" name="password" id="nuevo_password" required>
                </div>
                <div>
                    <label for="nuevo_role">Rol:</label>
                    <select name="role" id="nuevo_role">
                        <option value="1">Superadmin</option>
                        <option value="2">Admin Temas</option>
                        <option value="3">Admin Hilos</option>
                        <option value="4" selected>Usuario</option>
                        <option value="5">Visitante</option>
                    </select>
                </div>
                <div>
                    <label for="nuevo_nombre">Nombre:</label>
                    <input type="text" name="name" id="nuevo_nombre">
                </div>
                <div>
                    <label for="nuevo_email">Email:</label>
                    <input type="email" name="email" id="nuevo_email">
                </div>
                <button type="submit" class="btn-primary">Crear Usuario</button>
            </form>
        </div>
        <div class="user-list">
            <h3>Usuarios Registrados</h3>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_usuarios as $user_item): ?>
                        <tr>
                            <td><?php echo $user_item['id']; ?></td>
                            <td><?php echo htmlspecialchars($user_item['username']); ?></td>
                            <td><?php echo htmlspecialchars($user_item['name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user_item['email'] ?? ''); ?></td>
                            <td><?php echo getRoleName($user_item['role']); ?></td>
                            <td>
                                <!-- Actualizar rol -->
                                <form method="POST" action="?accion=panel" class="inline-form">
                                    <input type="hidden" name="panel_action" value="update_role">
                                    <input type="hidden" name="user_id" value="<?php echo $user_item['id']; ?>">
                                    <select name="new_role">
                                        <option value="1" <?php if ($user_item['role'] == 1) echo 'selected'; ?>>Superadmin</option>
                                        <option value="2" <?php if ($user_item['role'] == 2) echo 'selected'; ?>>Admin Temas</option>
                                        <option value="3" <?php if ($user_item['role'] == 3) echo 'selected'; ?>>Admin Hilos</option>
                                        <option value="4" <?php if ($user_item['role'] == 4) echo 'selected'; ?>>Usuario</option>
                                        <option value="5" <?php if ($user_item['role'] == 5) echo 'selected'; ?>>Visitante</option>
                                    </select>
                                    <button type="submit" class="btn-secondary">Actualizar</button>
                                </form>
                                <!-- Eliminar usuario -->
                                <form method="POST" action="?accion=panel" class="inline-form">
                                    <input type="hidden" name="panel_action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user_item['id']; ?>">
                                    <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php else: ?>
    <!-- Interfaz del Foro -->
    <div class="forum-container">
        <!-- Columna de Temas -->
        <aside class="sidebar-topics">
            <section class="topic-section">
                <h2>Temas</h2>
                <ul class="topic-list">
                    <?php foreach ($temas as $tema): ?>
                        <li>
                            <a href="?tema_id=<?php echo $tema['id']; ?>">
                                <?php echo htmlspecialchars($tema['titulo']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (checkRole(2)): ?>
                    <form action="?accion=crear_tema" method="POST" class="topic-form inline-form">
                        <input type="text" name="titulo" placeholder="Nuevo Tema" required>
                        <button type="submit" class="btn-small">Crear</button>
                    </form>
                <?php endif; ?>
            </section>
        </aside>

        <!-- Columna de Hilos -->
        <section class="middle-section">
            <?php if ($tema_seleccionado > 0): ?>
                <div class="thread-wrapper">
                    <h2>Hilos</h2>
                    <ul class="thread-list">
                        <?php foreach ($hilos as $hilo): ?>
                            <li>
                                <a href="?tema_id=<?php echo $tema_seleccionado; ?>&hilo_id=<?php echo $hilo['id']; ?>">
                                    <?php echo htmlspecialchars($hilo['titulo']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (checkRole(3)): ?>
                        <form action="?accion=crear_hilo" method="POST" class="thread-form inline-form">
                            <input type="hidden" name="tema_id" value="<?php echo $tema_seleccionado; ?>">
                            <input type="text" name="titulo" placeholder="Nuevo Hilo" required>
                            <button type="submit" class="btn-small">Crear</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Columna de Publicaciones -->
        <aside class="sidebar-posts">
            <?php if ($hilo_seleccionado > 0): ?>
                <div class="post-wrapper">
                    <h2>Publicaciones</h2>
                    <ul class="post-list">
                        <?php foreach ($publicaciones as $pub): ?>
                            <li class="post-item">
                                <div class="post-header">
                                    <span class="post-author"><?php echo htmlspecialchars($pub['username']); ?></span>
                                    <span class="post-date"><?php echo $pub['fecha']; ?></span>
                                    <a href="?tema_id=<?php echo $tema_seleccionado; ?>&hilo_id=<?php echo $hilo_seleccionado; ?>&reply=<?php echo $pub['id']; ?>" class="reply-link">Responder</a>
                                </div>
                                <div class="post-content">
                                    <?php echo nl2br(htmlspecialchars($pub['contenido'])); ?>
                                    <?php if (!empty($pub['imagen'])): ?>
                                        <div class="post-image-wrapper">
                                            <img src="uploads/<?php echo htmlspecialchars($pub['imagen']); ?>" alt="Imagen de la publicación" class="post-image">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (checkRole(4)): ?>
                        <!-- Formulario para publicar mensaje con opción de insertar imagen -->
                        <form action="?accion=crear_publicacion" method="POST" class="post-form" enctype="multipart/form-data">
                            <input type="hidden" name="hilo_id" value="<?php echo $hilo_seleccionado; ?>">
                            <?php if ($reply_message): ?>
                                <p class="reply-info">
                                    &#x2192; <?php echo htmlspecialchars($reply_message['username'] ?? 'Desconocido'); ?>:
                                    <em><?php echo substr($reply_message['contenido'], 0, 50); ?>...</em>
                                </p>
                                <input type="hidden" name="parent_id" value="<?php echo $reply_message['id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="parent_id" value="0">
                            <?php endif; ?>
                            <textarea name="contenido" placeholder="Escribe tu mensaje aquí" required></textarea>
                            <label for="imagen">Insertar imagen (opcional):</label>
                            <input type="file" name="imagen" id="imagen" accept="image/*">
                            <button type="submit" class="btn-primary">Publicar</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </aside>
    </div>
<?php endif; ?>