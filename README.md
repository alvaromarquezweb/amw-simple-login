# AMW Simple Login

Plugin de WordPress para personalizar por completo la pantalla de acceso (`wp-login.php`): logo, colores, imagen de fondo a dos columnas, enlaces legales y algunos ajustes de seguridad. Pensado para quien despliega en muchos sitios: se configura entero desde el escritorio y se actualiza solo desde GitHub.

## ✨ Características

- **Logo** con selección desde la biblioteca de medios, y detección automática del logo del sitio si no configuras ninguno.
- **Paleta de colores** completa (fondo, formulario, campos, bordes, textos, botón), con las variables inyectadas en CSS.
- **Imagen de fondo a dos columnas**: formulario a la izquierda, al centro o a la derecha, con desenfoque graduable y una capa de oscurecido de color e intensidad configurables.
- **Enlaces legales** al pie del login (aviso legal, privacidad, cookies), con texto y URL propios.
- **Ajustes de interfaz**: redondeo de esquinas y ocultar los enlaces "volver al sitio" y "olvidé mi contraseña".
- **Seguridad**: mensaje de error de acceso genérico (no revela si el usuario existe) y limpieza de la huella de WordPress en el login.
- **Compatible con Divi**: usa el logo definido en las Opciones del tema de Divi cuando no hay otro configurado.
- **Autoactualización** desde las releases de este repositorio.
- **Tema oscuro por defecto**, fuente del sistema, sin cargas externas.

## 📋 Requisitos

- WordPress 5.9 o superior
- PHP 7.4 o superior
- Divi (opcional, solo para la detección de su logo)

## 🔧 Instalación

1. Descarga el `.zip` de la última [release](../../releases/latest).
2. En WordPress, ve a **Plugins → Añadir nuevo → Subir plugin**, sube el archivo y actívalo.
3. Una vez activo, las siguientes actualizaciones llegan solas desde este repositorio.

## ⚙️ Configuración

Todo se ajusta desde **Ajustes → AMW Login**:

- **Logo**: sube el que quieras o deja que use el del sitio.
- **Imagen de fondo**: imagen, disposición (izquierda, centro, derecha), desenfoque, y color e intensidad del oscurecido.
- **Paleta de colores** e **interfaz** (redondeo, ocultar enlaces).
- **Enlaces legales** del pie.

El logo se resuelve en este orden: logo del plugin, logo de Identidad del sitio, logo de Divi y, como último recurso, un SVG de reserva en línea que toma el color del tema.

## 🚀 Publicar una actualización (mantenimiento)

El plugin comprueba las releases de este repo mediante [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker). Para sacar una versión nueva:

1. Sube el número en la cabecera `Version:` del archivo principal.
2. Haz commit y push.
3. Crea una **release** con el tag correspondiente (por ejemplo, `v1.0.1`).

Los sitios con el plugin instalado detectarán la nueva versión y ofrecerán la actualización desde su panel.

## 📄 Licencia

[GPL-2.0-or-later](LICENSE). Incluye la librería Plugin Update Checker de Yahnis Elsts, con su propia licencia compatible.

## 👤 Autor

Álvaro Márquez Díaz · [alvaromarquezweb.com](https://alvaromarquezweb.com)
