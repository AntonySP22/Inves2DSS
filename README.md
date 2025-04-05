# Control de Pacientes

## Miembros del Equipo
- [ Nombre ]

## Materia
- [ Materia ]

## Nombre del Proyecto
- Control de Pacientes

## Requisitos o Requerimientos
1. Tener instalado un servidor local como XAMPP o WAMP.
2. PHP versión 7.4 o superior.
3. MySQL versión 5.7 o superior.
4. Un navegador web actualizado.
5. Git (opcional, para clonar el repositorio).

## Cómo Ejecutar el Proyecto Clonado

1. **Clonar el Repositorio**
   ```bash
   git clone https://github.com/AntonySP22/Inves2DSS
   ```

2. **Mover el Proyecto**
   Copia la carpeta del proyecto clonado a la carpeta `htdocs` de tu servidor local (por ejemplo, `C:\xampp\htdocs\control_pacientes`).

3. **Configurar la Base de Datos**
   - Inicia tu servidor local (XAMPP o WAMP).
   - Abre phpMyAdmin en tu navegador (generalmente en `http://localhost/phpmyadmin`).
   - Crea una nueva base de datos llamada `control_pacientes`.
   - Importa el archivo SQL ubicado en `sql/control_pacientes.sql` para crear las tablas y datos necesarios.

4. **Configurar el Archivo de Conexión a la Base de Datos**
   - Abre el archivo `db/db.php`.
   - Asegúrate de que las credenciales de conexión a la base de datos sean correctas:
     ```php
     $host = 'localhost';
     $user = 'root';
     $password = ''; // Cambiar si tienes una contraseña configurada
     $database = 'control_pacientes';
     ```

5. **Iniciar el Proyecto**
   - Abre tu navegador web.
   - Ve a `http://localhost/control_pacientes/index.php`.

¡Listo! Ahora deberías poder usar el sistema de control de pacientes.