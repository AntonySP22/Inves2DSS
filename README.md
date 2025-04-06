# 🏥 Sistema de Control de Pacientes

## 👥 Equipo de Desarrollo
- **Blanca Maravilla** 
- **Elmer Cruz** 
- **Rebeca Orozco** 
- **Adán Ruano** 
- **Andrea** 

## 📚 Materia
Diseño de Sistemas de Software (DSS) - 2025

## 🚀 Características Principales
- Registro y gestión de pacientes
- Programación de citas médicas
- Control de tratamientos y medicamentos
- Panel administrativo
- Roles de usuario (Admin, Médico, Paciente)

## ⚙️ Requisitos Técnicos
- PHP 7.4+
- MySQL 5.7+
- Servidor Apache/Nginx
- Navegador moderno (Chrome, Firefox, Edge)

## 🛠️ Instalación
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

## 📸 Capturas del Sistema
![image](https://github.com/user-attachments/assets/61451c11-f3c5-4010-b0f1-cb57bdee147f)
![image](https://github.com/user-attachments/assets/7ac9c46d-e5cb-4e17-b6bf-3c7c55306632)
![image](https://github.com/user-attachments/assets/7fe16b08-127c-451d-a4c8-3f55c5327635)
![image](https://github.com/user-attachments/assets/fc9cbcbd-aeee-41b6-a6f1-eb1472e3e54a)


## 📞 Contacto
Para soporte técnico o colaboraciones, contactar al equipo de desarrollo.
