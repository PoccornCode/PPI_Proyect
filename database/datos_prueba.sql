-- Datos de prueba: categorias, productos, imagenes y subastas
-- Nota: copiar la carpeta uploads/productos/ en el proyecto.

SET FOREIGN_KEY_CHECKS = 0;
SET @id_usuario = COALESCE((SELECT MIN(id_usuario) FROM sb_usuario), 1);

-- Categorias
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (1, 'Tecnología', 'Equipos de cómputo, cámaras fotográficas, consolas, audio de alta fidelidad y periféricos.', 'assets/img/cat-tecnologia.jpg');
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (2, 'Vehículos', 'Automóviles clásicos, motocicletas, bicicletas de ruta, repuestos originales y accesorios.', 'assets/img/cat-vehiculos.jpg');
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (3, 'Arte', 'Pinturas al óleo, esculturas, grabados originales, fotografía artística y piezas de autor.', 'assets/img/cat-arte.jpg');
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (4, 'Joyería', 'Relojería fina, anillos de plata y oro, piedras preciosas y accesorios de alta gama.', 'assets/img/cat-joyeria.jpg');
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (5, 'Moda', 'Prendas de diseñador, calzado, bolsos, artículos vintage y accesorios exclusivos.', 'assets/img/cat-moda.jpg');
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (6, 'Hogar', 'Mobiliario de diseño, iluminación decorativa, cerámica fina, alfombras y artículos para el hogar.', 'assets/img/cat-hogar.jpg');
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (7, 'Coleccionables', 'Numismática, filatelia, relojes antiguos, figuras de edición limitada y artículos históricos.', 'assets/img/cat-coleccionables.jpg');
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (8, 'Deportes', 'Artículos deportivos, equipamiento de ciclismo, aventura y coleccionables deportivos.', 'assets/img/cat-deportes.jpg');
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (9, 'Instrumentos Musicales', 'Guitarras acústicas y eléctricas, teclados, amplificadores de estudio y equipamiento musical.', 'assets/img/cat-instrumentos.jpg');
INSERT IGNORE INTO sb_categoria (id_categoria, nombre, descripcion, imagen) VALUES (10, 'Libros', 'Primeras ediciones, libros antiguos, literatura clásica, manuscritos y publicaciones históricas.', 'assets/img/cat-libros.jpg');

-- Productos
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (5, 'Reloj de bolsillo de latón, siglo XIX', 'Pieza en buen estado de funcionamiento, con cadena original y estuche. Sin precio de reserva.', 'Excelente', 1, 7, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (6, 'Cámara de película de 35 mm', 'Cámara clásica totalmente mecánica con lente 50mm f/1.8 y funda de cuero.', 'Como nuevo', 1, 1, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (7, 'Lámpara de mesa de bronce', 'Lámpara vintage con base de bronce macizo y pantalla de vidrio opalino.', 'Excelente', 1, 6, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (8, 'Colección de monedas antiguas', 'Lote de 18 monedas históricas de plata y cobre en álbum protector.', 'Excelente', 1, 7, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (9, 'Guitarra acústica de estudio', 'Tapa de abeto macizo, diapasón de palisandro y estuche rígido incluido.', 'Como nuevo', 1, 9, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (10, 'Óleo sobre lienzo, paisaje', 'Obra original firmada, enmarcada en madera tallada de 60x45 cm.', 'Nuevo', 1, 3, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (11, 'Consola portátil retro', 'Edición clásica restaurada con pantalla IPS brillante y batería recargable.', 'Como nuevo', 1, 1, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (12, 'Juego de vajilla de cerámica', 'Set artesanal de 16 piezas esmaltadas a mano, apto para lavavajillas.', 'Nuevo', 1, 6, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (13, 'Anillo de plata con ópalo', 'Plata esterlina 925 con ópalo natural engastado a mano, talla 7.', 'Nuevo', 1, 4, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (14, 'Máquina de escribir vintage', 'Máquina mecánica portátil de los años 60 con estuche rígido original y cinta nueva.', 'Excelente', 1, 7, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (21, 'Raqueta de tenis clásica de madera', 'Raqueta vintage en buen estado con funda original de lona.', 'Excelente', 1, 8, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (22, 'Balón de cuero vintage de fútbol', 'Balón cosido a mano estilo retro para colección o exhibición.', 'Como nuevo', 1, 8, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (23, 'Primera edición ilustrada, 1954', 'Libro de tapa dura encuadernado en tela con grabados originales.', 'Excelente', 1, 10, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (24, 'Atlas geográfico antiguo', 'Edición de colección con mapas a color en excelente conservación.', 'Excelente', 1, 10, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (25, 'Guantes de boxeo de cuero retro', 'Par de guantes clásicos de cuero genuino con cordones.', 'Como nuevo', 1, 8, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (26, 'Chaqueta de cuero clásica', 'Chaqueta estilo aviador de cuero marrón, talla M.', 'Como nuevo', 1, 5, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (27, 'Gafas de sol vintage', 'Montura clásica metálica con estuche rígido original.', 'Nuevo', 1, 5, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (28, 'Patines clásicos de cuatro ruedas', 'Patines retro de bota de cuero con ruedas de uretano.', 'Como nuevo', 1, 8, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (29, 'Colección de novelas clásicas', 'Lote de 6 tomos encuadernados en piel con lomo dorado.', 'Excelente', 1, 10, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (30, 'Casco clásico de motocicleta', 'Casco retro abierto con visor y acabado brillante.', 'Nuevo', 1, 2, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (31, 'Brújula náutica de latón', 'Brújula clásica de bolsillo con tapa abatible y grabado náutico.', 'Excelente', 1, 7, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (32, 'Tetera de porcelana pintada', 'Tetera artesanal de porcelana blanca con detalles florales.', 'Como nuevo', 1, 6, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (33, 'Violín clásico 4/4 con arco', 'Violín de madera de abeto con estuche rígido y resina.', 'Como nuevo', 1, 9, @id_usuario);
INSERT INTO sb_producto (id_producto, nombre, descripcion, condicion, estado, id_categoria, id_usuario) VALUES (34, 'Reloj despertador mecánico', 'Despertador retro de metal cromado con doble campana.', 'Excelente', 1, 1, @id_usuario);

-- Imagenes
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (5, 5, 1, 'subasta_5.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (6, 6, 1, 'subasta_6.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (7, 7, 1, 'subasta_7.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (8, 8, 1, 'subasta_8.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (9, 9, 1, 'subasta_9.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (10, 10, 1, 'subasta_10.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (11, 11, 1, 'subasta_11.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (12, 12, 1, 'subasta_12.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (13, 13, 1, 'subasta_13.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (14, 14, 1, 'subasta_14.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (21, 21, 1, 'subasta_21.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (22, 22, 1, 'subasta_22.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (23, 23, 1, 'subasta_23.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (24, 24, 1, 'subasta_lote_4_1795161786.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (25, 25, 1, 'subasta_25.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (26, 26, 1, 'subasta_26.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (27, 27, 1, 'subasta_27.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (28, 28, 1, 'subasta_28.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (29, 29, 1, 'subasta_lote_9_1795161822.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (30, 30, 1, 'subasta_30.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (31, 31, 1, 'producto_31.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (32, 32, 1, 'producto_32.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (33, 33, 1, 'producto_33.jpg');
INSERT INTO sb_imagen_producto (id_imagen_producto, id_producto, principal, ruta) VALUES (34, 34, 1, 'producto_34.jpg');

-- Subastas activas
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (5, 185.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 20 HOUR), 'activa', 5);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (6, 64.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 17 HOUR), 'activa', 6);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (7, 38.50, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 18 HOUR), 'activa', 7);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (8, 120.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 19 HOUR), 'activa', 8);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (9, 165.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 21 HOUR), 'activa', 9);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (10, 95.00, 5.00, NOW(), DATE_ADD(NOW(), INTERVAL 142 HOUR), 'activa', 10);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (11, 72.00, 3.00, NOW(), DATE_ADD(NOW(), INTERVAL 118 HOUR), 'activa', 11);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (12, 45.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 94 HOUR), 'activa', 12);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (13, 88.00, 4.00, NOW(), DATE_ADD(NOW(), INTERVAL 70 HOUR), 'activa', 13);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (14, 82.00, 3.00, NOW(), DATE_ADD(NOW(), INTERVAL 71 HOUR), 'activa', 14);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (21, 42.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 94 HOUR), 'activa', 21);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (22, 55.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 118 HOUR), 'activa', 22);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (23, 68.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 142 HOUR), 'activa', 23);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (24, 79.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 118 HOUR), 'activa', 24);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (25, 64.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 94 HOUR), 'activa', 25);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (26, 115.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 142 HOUR), 'activa', 26);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (27, 48.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 70 HOUR), 'activa', 27);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (28, 59.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 118 HOUR), 'activa', 28);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (29, 92.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 166 HOUR), 'activa', 29);
INSERT INTO sb_subasta (id_subasta, precio_inicial, incremento_minimo, fecha_inicial, fecha_final, estado, id_producto) VALUES (30, 130.00, 2.00, NOW(), DATE_ADD(NOW(), INTERVAL 142 HOUR), 'activa', 30);

SET FOREIGN_KEY_CHECKS = 1;
