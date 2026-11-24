-- =============================================================================
-- Gavel.net — Script de restauración completa de BD local (SEGURO PARA GIT)
-- =============================================================================
-- Uso:
--   1. Selecciona tu base de datos local en phpMyAdmin (o MySQL CLI).
--   2. Importa o ejecuta este archivo completo.
--
-- Seguridad:
--   - NO contiene datos personales reales (correos, teléfonos ni contraseñas reales).
--   - Incluye únicamente estructura completa + datos de prueba sanitizados.
--
-- Cuentas demo incluidas (solo entorno local — contraseña: Gavel123!):
--   - Administrador : admin@gavel.local      (user: admin_demo)
--   - Moderador     : moderador@gavel.local  (user: moderador_demo)
--   - Usuario       : usuario@gavel.local    (user: usuario_demo)
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. LIMPIEZA DE TABLAS EXISTENTES
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `sb_puja`;
DROP TABLE IF EXISTS `sb_subasta`;
DROP TABLE IF EXISTS `sb_imagen_producto`;
DROP TABLE IF EXISTS `sb_producto`;
DROP TABLE IF EXISTS `sb_categoria`;
DROP TABLE IF EXISTS `sb_contacto`;
DROP TABLE IF EXISTS `sb_codigo_verificacion`;
DROP TABLE IF EXISTS `sb_telefono_usuario`;
DROP TABLE IF EXISTS `sb_correo_usuario`;
DROP TABLE IF EXISTS `sb_usuario`;
DROP TABLE IF EXISTS `sb_rol`;

-- -----------------------------------------------------------------------------
-- 2. ESTRUCTURA DE TABLAS
-- -----------------------------------------------------------------------------
CREATE TABLE `sb_rol` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sb_usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` text NOT NULL,
  `apellido` text NOT NULL,
  `username` varchar(50) NOT NULL,
  `contrasenia` text NOT NULL,
  `ubicacion` text NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `id_rol` int(11) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `fk_usuario_rol` (`id_rol`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `sb_rol` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sb_correo_usuario` (
  `id_usuario` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL,
  PRIMARY KEY (`id_usuario`,`correo`),
  UNIQUE KEY `uk_correo` (`correo`),
  CONSTRAINT `fk_correo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `sb_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sb_telefono_usuario` (
  `id_usuario` int(11) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  PRIMARY KEY (`id_usuario`,`telefono`),
  CONSTRAINT `fk_telefono_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `sb_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sb_codigo_verificacion` (
  `id_codigo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `tipo` enum('registro','recuperacion') DEFAULT 'registro',
  `expira_en` datetime NOT NULL,
  `utilizado` tinyint(1) DEFAULT 0,
  `intentos` int(11) DEFAULT 0,
  `creado_en` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_codigo`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `sb_codigo_verificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `sb_usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sb_categoria` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Publicada, 0 = Desactivada',
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `uk_categoria_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sb_producto` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `condicion` varchar(40) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `id_categoria` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id_producto`),
  KEY `fk_producto_categoria` (`id_categoria`),
  KEY `fk_producto_usuario` (`id_usuario`),
  CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `sb_categoria` (`id_categoria`),
  CONSTRAINT `fk_producto_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `sb_usuario` (`id_usuario`),
  CONSTRAINT `ck_producto_condicion` CHECK (`condicion` in ('Nuevo','Como nuevo','Excelente','Buen estado','Aceptable','Para repuestos o restauración'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sb_imagen_producto` (
  `id_imagen_producto` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT 0,
  `ruta` text NOT NULL,
  PRIMARY KEY (`id_imagen_producto`),
  KEY `fk_imagen_producto` (`id_producto`),
  CONSTRAINT `fk_imagen_producto` FOREIGN KEY (`id_producto`) REFERENCES `sb_producto` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sb_subasta` (
  `id_subasta` int(11) NOT NULL AUTO_INCREMENT,
  `precio_inicial` decimal(10,2) NOT NULL,
  `incremento_minimo` decimal(10,2) NOT NULL,
  `fecha_inicial` datetime NOT NULL,
  `fecha_final` datetime NOT NULL,
  `estado` varchar(25) NOT NULL DEFAULT 'pendiente',
  `id_producto` int(11) NOT NULL,
  PRIMARY KEY (`id_subasta`),
  KEY `fk_subasta_producto` (`id_producto`),
  CONSTRAINT `fk_subasta_producto` FOREIGN KEY (`id_producto`) REFERENCES `sb_producto` (`id_producto`),
  CONSTRAINT `ck_precio_inicial_positivo` CHECK (`precio_inicial` > 0),
  CONSTRAINT `ck_incremento_minimo_positivo` CHECK (`incremento_minimo` > 0),
  CONSTRAINT `ck_fechas_subasta` CHECK (`fecha_final` > `fecha_inicial`),
  CONSTRAINT `ck_subasta_estado` CHECK (`estado` in ('pendiente','activa','rechazada','finalizada','sin_ofertas','cancelada'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sb_puja` (
  `id_puja` int(11) NOT NULL AUTO_INCREMENT,
  `monto` decimal(10,2) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_subasta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id_puja`),
  KEY `fk_puja_usuario` (`id_usuario`),
  KEY `fk_puja_subasta` (`id_subasta`),
  CONSTRAINT `fk_puja_subasta` FOREIGN KEY (`id_subasta`) REFERENCES `sb_subasta` (`id_subasta`),
  CONSTRAINT `fk_puja_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `sb_usuario` (`id_usuario`),
  CONSTRAINT `ck_pujas_monto_positivo` CHECK (`monto` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sb_contacto` (
  `id_contacto` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `nombre` varchar(120) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `asunto` varchar(150) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_contacto`),
  KEY `idx_contacto_usuario` (`id_usuario`),
  CONSTRAINT `fk_contacto_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `sb_usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. ROLES Y USUARIOS DEMO SANITIZADOS (Contraseña local: Gavel123!)
-- -----------------------------------------------------------------------------
INSERT INTO `sb_rol` (`id_rol`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Usuario'),
(3, 'Moderador');

INSERT INTO `sb_usuario` (`id_usuario`, `nombre`, `apellido`, `username`, `contrasenia`, `ubicacion`, `estado`, `id_rol`) VALUES
(1, 'Admin', 'Demo', 'admin_demo', '$2y$10$69YIRSmSjBZ8CCPfEWP7FuKoDCbfJUrWa3cN1s3KGgEYCHdRjkpiu', 'San Salvador, El Salvador', 1, 1),
(2, 'Moderador', 'Demo', 'moderador_demo', '$2y$10$69YIRSmSjBZ8CCPfEWP7FuKoDCbfJUrWa3cN1s3KGgEYCHdRjkpiu', 'San Salvador, El Salvador', 1, 3),
(3, 'Usuario', 'Demo', 'usuario_demo', '$2y$10$69YIRSmSjBZ8CCPfEWP7FuKoDCbfJUrWa3cN1s3KGgEYCHdRjkpiu', 'San Salvador, El Salvador', 1, 2);

INSERT INTO `sb_correo_usuario` (`id_usuario`, `correo`) VALUES
(1, 'admin@gavel.local'),
(2, 'moderador@gavel.local'),
(3, 'usuario@gavel.local');

-- -----------------------------------------------------------------------------
-- 4. CATEGORÍAS BASE
-- -----------------------------------------------------------------------------
INSERT INTO `sb_categoria` (`id_categoria`, `nombre`, `descripcion`, `imagen`, `estado`) VALUES
(1, 'Tecnología', 'Equipos de cómputo, cámaras fotográficas, consolas, audio de alta fidelidad y periféricos.', 'assets/img/cat-tecnologia.jpg', 1),
(2, 'Vehículos', 'Automóviles clásicos, motocicletas, bicicletas de ruta, repuestos originales y accesorios.', 'assets/img/cat-vehiculos.jpg', 1),
(3, 'Arte', 'Pinturas al óleo, esculturas, grabados originales, fotografía artística y piezas de autor.', 'assets/img/cat-arte.jpg', 1),
(4, 'Joyería', 'Relojería fina, anillos de plata y oro, piedras preciosas y accesorios de alta gama.', 'assets/img/cat-joyeria.jpg', 1),
(5, 'Moda', 'Prendas de diseñador, calzado, bolsos, artículos vintage y accesorios exclusivos.', 'assets/img/cat-moda.jpg', 1),
(6, 'Hogar', 'Mobiliario de diseño, iluminación decorativa, cerámica fina, alfombras y artículos para el hogar.', 'assets/img/cat-hogar.jpg', 1),
(7, 'Coleccionables', 'Numismática, filatelia, relojes antiguos, figuras de edición limitada y artículos históricos.', 'assets/img/cat-coleccionables.jpg', 1),
(8, 'Deportes', 'Artículos deportivos, equipamiento de ciclismo, aventura y coleccionables deportivos.', 'assets/img/cat-deportes.jpg', 1),
(9, 'Instrumentos Musicales', 'Guitarras acústicas y eléctricas, teclados, amplificadores de estudio y equipamiento musical.', 'assets/img/cat-instrumentos.jpg', 1),
(10, 'Libros', 'Primeras ediciones, libros antiguos, literatura clásica, manuscritos y publicaciones históricas.', 'assets/img/cat-libros.jpg', 1);

-- -----------------------------------------------------------------------------
-- 5. PRODUCTOS, IMÁGENES Y SUBASTAS DE PRUEBA
-- Nota: requiere las imágenes en uploads/productos/
-- -----------------------------------------------------------------------------
SET @id_vendedor = 3;

INSERT INTO `sb_producto` (`id_producto`, `nombre`, `descripcion`, `condicion`, `estado`, `id_categoria`, `id_usuario`) VALUES
(5, 'Reloj de bolsillo de latón, siglo XIX', 'Pieza en buen estado de funcionamiento, con cadena original y estuche. Sin precio de reserva.', 'Excelente', 1, 7, @id_vendedor),
(6, 'Cámara de película de 35 mm', 'Cámara clásica totalmente mecánica con lente 50mm f/1.8 y funda de cuero.', 'Como nuevo', 1, 1, @id_vendedor),
(7, 'Lámpara de mesa de bronce', 'Lámpara vintage con base de bronce macizo y pantalla de vidrio opalino.', 'Excelente', 1, 6, @id_vendedor),
(8, 'Colección de monedas antiguas', 'Lote de 18 monedas históricas de plata y cobre en álbum protector.', 'Excelente', 1, 7, @id_vendedor),
(9, 'Guitarra acústica de estudio', 'Tapa de abeto macizo, diapasón de palisandro y estuche rígido incluido.', 'Como nuevo', 1, 9, @id_vendedor),
(10, 'Óleo sobre lienzo, paisaje', 'Obra original firmada, enmarcada en madera tallada de 60x45 cm.', 'Nuevo', 1, 3, @id_vendedor),
(11, 'Consola portátil retro', 'Edición clásica restaurada con pantalla IPS brillante y batería recargable.', 'Como nuevo', 1, 1, @id_vendedor),
(12, 'Juego de vajilla de cerámica', 'Set artesanal de 16 piezas esmaltadas a mano, apto para lavavajillas.', 'Nuevo', 1, 6, @id_vendedor),
(13, 'Anillo de plata con ópalo', 'Plata esterlina 925 con ópalo natural engastado a mano, talla 7.', 'Nuevo', 1, 4, @id_vendedor),
(14, 'Máquina de escribir vintage', 'Máquina mecánica portátil de los años 60 con estuche rígido original y cinta nueva.', 'Excelente', 1, 7, @id_vendedor),
(21, 'Raqueta de tenis clásica de madera', 'Raqueta vintage en buen estado con funda original de lona.', 'Excelente', 1, 8, @id_vendedor),
(22, 'Balón de cuero vintage de fútbol', 'Balón cosido a mano estilo retro para colección o exhibición.', 'Como nuevo', 1, 8, @id_vendedor),
(23, 'Primera edición ilustrada, 1954', 'Libro de tapa dura encuadernado en tela con grabados originales.', 'Excelente', 1, 10, @id_vendedor),
(24, 'Atlas geográfico antiguo', 'Edición de colección con mapas a color en excelente conservación.', 'Excelente', 1, 10, @id_vendedor),
(25, 'Guantes de boxeo de cuero retro', 'Par de guantes clásicos de cuero genuino con cordones.', 'Como nuevo', 1, 8, @id_vendedor),
(26, 'Chaqueta de cuero clásica', 'Chaqueta estilo aviador de cuero marrón, talla M.', 'Como nuevo', 1, 5, @id_vendedor),
(27, 'Gafas de sol vintage', 'Montura clásica metálica con estuche rígido original.', 'Nuevo', 1, 5, @id_vendedor),
(28, 'Patines Clásicos de Cuatro Ruedas Riedell', 'Par de patines clásicos de cuatro ruedas marca Riedell modelo R3 con bota de cuero negro, plancha Avanti, ruedas de uretano con núcleo verde y punteras protectoras de cuero azul.', 'Como nuevo', 1, 8, @id_vendedor),
(29, 'Lote de 4 Libros Antiguos Encuadernados en Piel', 'Colección de 4 tomos antiguos de tapa dura con lomos grabados en dorado, cantos marmoleados y corte rojo. Encuadernación firme y páginas completas sin hojas sueltas.', 'Excelente', 1, 10, @id_vendedor),
(30, 'Casco Integral Vega con Visor Abatible', 'Casco cerrado marca Vega con acabado texturizado tipo fibra de carbono, visor transparente abatible e interior acolchado limpio en color negro.', 'Buen estado', 1, 2, @id_vendedor),
(31, 'Brújula y Reloj Solar de Bolsillo en Latón', 'Pieza antigua de bolsillo fabricada en latón grabado con brújula central bajo cristal y cuadrante solar plegable con numeración romana.', 'Excelente', 1, 7, @id_vendedor),
(33, 'Violín Clásico 4/4 de Madera con Lote de Arcos', 'Violín acústico tamaño 4/4 en madera barnizada con voluta tallada, puente ajustado, cuatro cuerdas instaladas y juego de 4 arcos de madera con crin natural.', 'Como nuevo', 1, 9, @id_vendedor),
(35, 'Cámara Réflex Canon EOS 80D (Cuerpo)', 'Cuerpo de cámara réflex digital Canon EOS 80D de 24.2 MP con tapa frontal original de montura EF, zapata superior limpia y dial de modos en perfecto funcionamiento.', 'Como nuevo', 1, 1, @id_vendedor),
(36, 'Reloj Automático Seiko 5 Acero Inoxidable', 'Reloj mecánico automático Seiko 5 de 21 joyas con caja y brazalete de acero inoxidable, esfera oscura con números romanos XII y VI, y fechador doble de día y fecha.', 'Buen estado', 1, 4, @id_vendedor),
(37, 'Guitarra Acústica Walden D310e Tapa de Abeto', 'Guitarra acústica Walden modelo D310e con tapa de abeto natural, golpeador negro, diapasón de palisandro y 6 cuerdas metálicas recién calibradas.', 'Excelente', 1, 9, @id_vendedor),
(38, 'Álbum de Monedas Antiguas (24 Piezas)', 'Carpeta roja de colección numismática con hojas plásticas y 24 monedas antiguas de plata, cobre y bronce protegidas individualmente en cartones.', 'Excelente', 1, 7, @id_vendedor),
(39, 'Cafetera Espresso Italiana de Aluminio', 'Cafetera tipo moka italiana fabricada en aluminio con asa ergonómica negra, válvula de seguridad en la caldera y embudo dosificador metálico interno.', 'Buen estado', 1, 6, @id_vendedor);

INSERT INTO `sb_imagen_producto` (`id_producto`, `principal`, `ruta`) VALUES
(5, 1, 'subasta_5.jpg'),
(6, 1, 'subasta_6.jpg'),
(7, 1, 'subasta_7.jpg'),
(8, 1, 'subasta_8.jpg'),
(9, 1, 'subasta_9.jpg'),
(10, 1, 'subasta_10.jpg'),
(11, 1, 'subasta_11.jpg'),
(12, 1, 'subasta_12.jpg'),
(13, 1, 'subasta_13.jpg'),
(14, 1, 'subasta_14.jpg'),
(21, 1, 'subasta_21.jpg'),
(22, 1, 'subasta_22.jpg'),
(23, 1, 'subasta_23.jpg'),
(24, 1, 'subasta_lote_4_1795161786.jpg'),
(25, 1, 'subasta_25.jpg'),
(26, 1, 'subasta_26.jpg'),
(27, 1, 'subasta_27.jpg'),
(28, 1, 'subasta_28.jpg'),
(28, 0, 'subasta_28_2.jpg'),
(29, 1, 'subasta_29_1.jpg'),
(29, 0, 'subasta_29_2.jpg'),
(29, 0, 'subasta_29_3.jpg'),
(30, 1, 'subasta_30.jpg'),
(30, 0, 'subasta_30_2.jpg'),
(31, 1, 'producto_31.jpg'),
(33, 1, 'producto_33.jpg'),
(35, 1, 'pend_canon_80d_1.jpg'),
(35, 0, 'pend_canon_80d_2.jpg'),
(35, 0, 'pend_canon_80d_3.jpg'),
(36, 1, 'pend_seiko_1.jpg'),
(36, 0, 'pend_seiko_2.jpg'),
(37, 1, 'pend_guitarra_1.jpg'),
(37, 0, 'pend_guitarra_2.jpg'),
(38, 1, 'pend_monedas_1.jpg'),
(38, 0, 'pend_monedas_2.jpg'),
(39, 1, 'pend_moka_1.jpg'),
(39, 0, 'pend_moka_2.jpg');

INSERT INTO `sb_subasta` (`id_subasta`, `precio_inicial`, `incremento_minimo`, `fecha_inicial`, `fecha_final`, `estado`, `id_producto`) VALUES
(5, 185.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 20 HOUR), 'activa', 5),
(6, 64.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 17 HOUR), 'activa', 6),
(7, 38.50, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 18 HOUR), 'activa', 7),
(8, 120.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 19 HOUR), 'activa', 8),
(9, 165.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 21 HOUR), 'activa', 9),
(10, 95.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 142 HOUR), 'activa', 10),
(11, 72.00, 3.00, NOW(), DATE_ADD(NOW(), INTERVAL 118 HOUR), 'activa', 11),
(12, 45.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 94 HOUR), 'activa', 12),
(13, 88.00, 4.00, NOW(), DATE_ADD(NOW(), INTERVAL 70 HOUR), 'activa', 13),
(14, 82.00, 3.00, NOW(), DATE_ADD(NOW(), INTERVAL 71 HOUR), 'activa', 14),
(21, 42.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 94 HOUR), 'activa', 21),
(22, 55.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 118 HOUR), 'activa', 22),
(23, 68.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 142 HOUR), 'activa', 23),
(24, 79.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 118 HOUR), 'activa', 24),
(25, 64.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 94 HOUR), 'activa', 25),
(26, 115.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 142 HOUR), 'activa', 26),
(27, 48.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 70 HOUR), 'activa', 27),
(28, 59.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 96 HOUR), 'pendiente', 28),
(29, 92.00, 3.00, NOW(), DATE_ADD(NOW(), INTERVAL 120 HOUR), 'pendiente', 29),
(30, 110.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 120 HOUR), 'pendiente', 30),
(31, 95.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 96 HOUR), 'pendiente', 31),
(33, 180.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 144 HOUR), 'pendiente', 33),
(35, 350.00, 10.00, NOW(), DATE_ADD(NOW(), INTERVAL 120 HOUR), 'pendiente', 35),
(36, 120.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 96 HOUR), 'pendiente', 36),
(37, 165.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 120 HOUR), 'pendiente', 37),
(38, 140.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 144 HOUR), 'pendiente', 38),
(39, 35.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 72 HOUR), 'pendiente', 39);

SET FOREIGN_KEY_CHECKS = 1;
