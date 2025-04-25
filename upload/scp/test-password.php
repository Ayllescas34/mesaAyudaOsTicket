<?php
// Datos simulados que provienen de la base de datos
/*$usuarioData = [
    'idUsuario' => 1,
    'username' => 'admin1',
    'passwrd' => '$2y$10$cj63MFA.ZlUHFk7Q4xfD5uInjNaTugaLnlkMi7UVO17iti2w0nvR.', // Hash de la contraseña almacenada
    'nombre' => 'Oscar Flores',
    'email' => 'admin@gmail.com',
    'estado' => 'Activo', // Este valor se obtiene de la base de datos
    'rol_id' => 1,
    'nombreRol' => 'admin',
];

// Contraseña ingresada por el usuario
$passwordIngresada = 'admin123'; // La contraseña proporcionada por el usuario en el formulario

// Verificación del estado del usuario
if ($usuarioData['estado'] !== 'Activo') {
    echo "❌ El usuario está inactivo. No se puede proceder con la autenticación.";
    exit(); // Si el usuario está inactivo, salimos del proceso
} else {
    echo "✔ Usuario activo, procediendo con la autenticación.<br>";
}

// Verificación de la contraseña utilizando password_verify
if (password_verify($passwordIngresada, $usuarioData['passwrd'])) {
    echo "✔ Contraseña válida. El usuario ha sido autenticado exitosamente.<br>";
    // Aquí puedes continuar con el proceso de login, como iniciar sesión y redirigir a la página principal
} else {
    echo "❌ Contraseña incorrecta. No se puede proceder con la autenticación.<br>";
    exit(); // Si la contraseña es incorrecta, no se procede con la autenticación
}*/


// Datos simulados que provienen de la base de datos
$usuarioData = [
    'idUsuario' => 1,
    'username' => 'admin1',
    'passwrd' => '$2y$10$cj63MFA.ZlUHFk7Q4xfD5uInjNaTugaLnlkMi7UVO17iti2w0nvR.', // Hash de la contraseña almacenada
    'nombre' => 'Oscar Flores',
    'email' => 'admin@gmail.com',
    'estado' => 'Activo',
    'rol_id' => 1,
    'nombreRol' => 'admin',
];

// Contraseña ingresada por el usuario
$passwordIngresada = 'admin123'; // La contraseña proporcionada por el usuario en el formulario

// Verificación del estado del usuario
if ($usuarioData['estado'] !== 'Activo') {
    echo "❌ El usuario está inactivo. No se puede proceder con la autenticación.";
    exit(); // Si el usuario está inactivo, salimos del proceso
} else {
    echo "✔ Usuario activo, procediendo con la autenticación.<br>";
}

// Verificación de la contraseña utilizando password_verify
if (password_verify($passwordIngresada, $usuarioData['passwrd'])) {
    echo "✔ Contraseña válida. El usuario ha sido autenticado exitosamente.<br>";
    // Aquí puedes continuar con el proceso de login, como iniciar sesión y redirigir a la página principal
} else {
    echo "❌ Contraseña incorrecta. No se puede proceder con la autenticación.<br>";
    exit(); // Si la contraseña es incorrecta, no se procede con la autenticación
}


?>
