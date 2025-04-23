CREATE DATABASE IF NOT EXISTS db_test_seguridad;
use db_test_seguridad;

/* ******************* ROLES ******************/
create table roles(
	idRol int not null auto_increment primary key,
    nombreRol varchar(50) not null,
    descripcionRol text
);

-- tuplas

insert into roles(nombreRol, descripcionRol) values
	('admin', 'Acceso Total al Sistema'),
    ('cliente','Acceso limitado a funciones');
select * from roles;

/* ******************* SISTEMAS ******************/
create table sistemas (
	idSistema int not null auto_increment primary key,
    nombreSistema varchar(150) not null,
    descripcionSistema text,
    url_acceso varchar(255)
);

-- tuplas

insert into sistemas(nombreSistema, descripcionSistema, url_acceso) values
	('osTicket','Sistena de Tickets de Soporte','https://osTicket.muniantigua.gob.gt'),
	('Compras','Sistema de solicitudes de compras','https://compras.muniantigua.gob.gt'),
	('Cuida','Sistema de denuncia de maltrato animal','https://cuida.muniantigua.gob.gt'),
	('Inventario','Sistema de inventario','https://inventario.muniantigua.gob.gt');

select * from sistemas;

/* ******************* USUARIOS ******************/

create table usuarios (
	idUsuario int not null auto_increment primary key,
    username varchar(50) not null unique,
    passwrd varchar(50) not null,
    nombre varchar(150) not null,
    email varchar(200) not null unique,
    rol_id int not null,
    estado enum('Activo', 'Inactivo') default 'Activo',
    creado_en timestamp default current_timestamp,
    foreign key (rol_id) references roles(idRol)
);

insert into usuarios (username, passwrd, nombre,email, rol_id) values
	('admin1','$2y$10$hashadmin123456','Oscar Flores','admin@gmail.com',1),
    ('cliente1','$2y$10$hashcliente123456','Alejandro Yllescas','cliente@gmail',2);

select * from usuarios;

/* ******************* PERMISOS ******************/

CREATE TABLE permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT,
    sistema_id INT,
    acceso BOOLEAN DEFAULT 0,
    FOREIGN KEY (rol_id) REFERENCES roles(idRol),
    FOREIGN KEY (sistema_id) REFERENCES sistemas(idSistema)
);


-- admin tiene acceso a todo
insert into permisos(rol_id, sistema_id, acceso)
select 1, idSistema, 1 from sistemas;

--  cliente acceso a comprar test
insert into permisos (rol_id, sistema_id, acceso)
values (2,2,1);

select * from permisos;

SELECT 
    u.idUsuario,
    u.username,
    r.nombreRol,
    s.nombreSistema,
    p.acceso
FROM usuarios u
JOIN roles r ON u.rol_id = r.idRol
JOIN permisos p ON p.rol_id = r.idRol
JOIN sistemas s ON p.sistema_id = s.idSistema
ORDER BY u.idUsuario, s.idSistema;



