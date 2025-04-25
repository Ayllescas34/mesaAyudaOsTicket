<?php
// Función para autenticar usuarios externos
function loginExterno($usuario, $clavePlano, $sistemaBuscado = null ) {
    // Parámetros de conexión a la base de datos
    $host = 'localhost';
    $dbname = 'db_test_seguridad';
    $user = 'admin';
    $pass = 'NFRmtc/*';
    
    try {
        // Crear conexión PDO a MySQL
        $pdo = new PDO("mysql:host=localhost;dbname=db_test_seguridad;charset=utf8", $user, $pass);

        // 1. Buscar usuario con su rol, si está activo
        $stmt = $pdo->prepare("select u.*, r.nombreRol from usuarios u
                            join roles r on u.rol_id = r.idRol
                            where u.username = :username and u.estado = 'Activo'");
        $stmt->execute(['username' => $usuario]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si se encontró el usuario y la contraseña es correcta
        if($userData && password_verify($clavePlano, $userData['passwrd'])){
            // 2. Si se indica sistema, verificar permiso de acceso al mismo
            if($sistemaBuscado){
                $stmtPermiso = $pdo->prepare("select acceso from permisos p
                                            join sistemas s on s.idSistema = p.sistema_id
                                            where p.rol_id =  :rol and s.nombreSistema = :sistema");
                $stmtPermiso->execute([
                    'rol' => $userData['rol_id'],
                    'sistema' => $sistemaBuscado
                ]);
                $permiso = $stmtPermiso->fetch(PDO::FETCH_ASSOC);

                // Si no tiene acceso al sistema, retornar error
                if(!$permiso || !$permiso['acceso']){
                    return ['autenticado' => false, 'error' => 'Sin permiso para acceder a este sistema.'];
                }
            }

            // 3. Usuario autenticado y con permisos
            return [
                'autenticado' => true,
                'usuario' => $userData['username'],
                'nombre' => $userData['nombre'],
                'rol' => $userData['nombreRol']
            ];
        } else {
            // Usuario no encontrado o contraseña incorrecta
            return ['autenticado' => false, 'error' => 'Credenciales inválidas o usuario inactivo.'];
        }
    }catch(PDOException $e){
        // En caso de error con la base de datos
        return ['autenticado' => false, 'error' => $e->getMessage()];
    }
}
?>
