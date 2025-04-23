<?php
function loginExterno($usuario, $clavePlano, $sistemaBuscado = null ) {
    $host = 'localhost';
    $dbname = 'db_test_seguridad';
    $user = 'admin';
    $pass = 'NFRmtc/*';
    

    try {   
        $pdo = new PDO("mysql:host=$host;dbname=$dbname,charset=uf8", $user, $pass);

        // 1. BUSCAR USUARIO EN LA BASE DE DATOS

        $stmt = $pdo->prepare("select u.*, r.nombreRol from usuario u
                            join roles r on u.rol_id = r.idRol
                            where u.usernae = :username and u.estado = 'Activo'");
        $stmt->execute(['username' => $usuario]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if($userData && password_verify($clavePlano, $userData['passwrd'])){
            // 2. VERIFICAR ACCESO AL SISTEMA
            if($sistemaBuscado){
                $stmtPermiso = $pdo->prepare("select acceso from permisos p
                                            join sistemas s on s.idSistema = p.sistema_id
                                            where p.rol_id =  :rol and s.nombreSistema = :sistema");
                $stmtPermiso->execute([
                    'rol' => $userData['rol_id'],
                    'sistema' => $sistemaBuscado
                ]);
                $permiso = $stmtPermiso->fetch(PDO::FETCH_ASSOC);

                if(!$permiso || !$permiso['acceso']){
                    return ['autenticado' => false, 'error' => 'Sin permiso para acceder a este sistema.'];
                }
            }

            // 3. USUARIO AUTENTICADO Y CON PERMISO
            return [
                'autenticado' => true,
                'usuario' => $userData['username'],
                'nombre' => $userData['nombre'],
                'rol' => $userData['nombreRol']
            ];
        } else {
            return ['autenticado' => false, 'error' => 'Credenciales inválidas o usuario inactivo.'];
        }
    }catch(PDOException $e){
        return ['autenticado' => false, 'error' => $e->getMessage()];
    }
}
?>