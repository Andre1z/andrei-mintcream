# Mintcream Forum

**Mintcream Forum** es una aplicación web de foro desarrollada en PHP que permite a los usuarios registrarse, iniciar sesión y participar en discusiones mediante la publicación de temas, hilos y mensajes. La aplicación también admite la carga de imágenes en las publicaciones, las cuales se almacenan en la carpeta `uploads` y se registra en la base de datos el nombre del archivo (como texto). Además, la interfaz cuenta con un modal de zoom para visualizar las imágenes de forma ampliada y cada columna tiene su propio scroll individual para mantener el footer siempre en la parte inferior.

## Características

- **Registro y Autenticación**:  
  Permite el registro de nuevos usuarios y el inicio de sesión en el sistema.

- **Foro Estructurado**:  
  Organiza las discusiones en temas, hilos y publicaciones.

- **Carga de Imágenes**:  
  Los usuarios pueden adjuntar imágenes en sus publicaciones. Las imágenes se suben a la carpeta `uploads` y se almacena el nombre de archivo en la base de datos.

- **Modal de Zoom**:  
  Al hacer clic en una imagen, se muestra en un pop-up centrado para una visualización ampliada.

- **Scroll Individual**:  
  Cada contenedor (temas, hilos y publicaciones) tiene un scroll individual, permitiendo que el footer se mantenga en la parte inferior de la pantalla.

## Estructura del Proyecto

La estructura de carpetas del proyecto es la siguiente:
```
mintcream-forum/
├── README.md                   # Documentación e instrucciones del proyecto
├── index.php                   # Punto de entrada principal de la aplicación
├── bloques/                    # Bloques o "partials" de la interfaz
│   ├── cabeza.php              # Contiene el DOCTYPE, head y apertura del body
│   ├── cabecera.php            # Encabezado y barra de navegación
│   ├── principal.php           # Sección principal (registro, foro, panel)
│   └── piedepagina.php         # Pie de página, cierre del body y html
├── css/                        # Hojas de estilo CSS
│   ├── global.css              # Estilos globales y reset
│   ├── cabeza.css              # Estilos para el head
│   ├── cabecera.css            # Estilos para el header y navegación
│   ├── principal.css           # Estilos para las secciones principales, formularios, modal, scroll, etc.
│   └── piedepagina.css         # Estilos para el footer
├── databases/                  # Base de datos y scripts de inicialización
│   └── mintcream.db            # Archivo de base de datos SQLite (o scripts para MySQL)
├── inc/                        # Lógica del servidor y utilidades
│   ├── datosinterfaz.php       # Prepara y recopila datos para la interfaz (temas, hilos, posts)
│   ├── funciones.php           # Funciones de ayuda (manejo de roles, validaciones)
│   ├── inicializarsqlite.php   # Conexión e inicialización de SQLite (creación de tablas, etc.)
│   └── procesamientodeformularios.php  # Procesa las solicitudes de formularios (login, registro, creación de posts, etc.)
├── uploads/                    # Carpeta para almacenar archivos e imágenes subidas
│   └── (archivos de imagen, etc.)
└── mintcream.png               # Imagen/logo del proyecto (opcional)
```


## Requisitos

- PHP 7.0 o superior.
- Servidor web (Apache, Nginx, etc.).
- Base de datos MySQL o SQLite.

## Instalación

1. **Clonar el repositorio:**

   ```bash
   git clone https://github.com/Andre1z/andrei-mintcream.git
   cd mintcream-forum
   ```
2. **Configurar el entorno:**

- Asegúrate de tener un servidor local (por ejemplo, XAMPP, WAMP o LAMP).

- Configura los parámetros de la base de datos en el archivo correspondiente o utiliza SQLite (el archivo se crea automáticamente al ejecutar inc/inicializarsqlite.php).

3. **Establecer permisos:**

- Verifica que la carpeta ``` uploads ``` existe y tiene permisos de escritura.

- Si utilizas **SQLite**, asegúrate de que la carpeta ``` databases ``` tenga permisos de escritura.

4. **Acceso a la aplicación:**

- Inicia el servidor web.

-  Navega a ``` http://localhost/andrei-mintcream ``` (o la URL configurada según tu entorno).

## Uso

- **Registro y Autenticación:** Los nuevos usuarios pueden registrarse mediante el formulario de registro y luego iniciar sesión para participar en el foro.

- **Participación en el Foro:** Los usuarios autenticados pueden crear temas, hilos y publicaciones. Al adjuntar imágenes, el nombre del archivo se almacena en la base de datos, y la imagen se guarda en la carpeta ```uploads```.

- **Zoom de Imágenes:** Al pulsar una imagen en una publicación, se abrirá un modal centrado en la pantalla que permite verla en mayor detalle.

- **Gestión de Usuarios:** Los superadministradores pueden gestionar usuarios (crear, actualizar roles y eliminar) desde el panel de administración.

## Créditos

Desarrollado por Andrei Inspirado en diversas aplicaciones de foros y diseñado para fomentar la interacción en línea.

## Licencia

Licencia MIT
