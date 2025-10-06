-- ========================
-- ÁREAS
-- ========================
INSERT INTO areas (nombre, descripcion) VALUES
('Recursos Humanos', 'Área encargada de la gestión de personal'),
('Contabilidad', 'Área encargada de la contabilidad y finanzas'),
('Logística', 'Área encargada de abastecimiento y control de bienes'),
('Dirección', 'Despacho principal de la DREMH Pasco');

-- ========================
-- USUARIOS
-- ========================
INSERT INTO usuarios (nombre, email, password_hash, id_area, rol) VALUES
('Admin General', 'admin@dremh.gob.pe', 'hash_admin', 4, 'admin'),
('María Pérez', 'mperez@dremh.gob.pe', 'hash_rrhh', 1, 'rrhh'),
('Juan Torres', 'jtorres@dremh.gob.pe', 'hash_jefe_logistica', 3, 'jefe_area'),
('Ana López', 'alopez@dremh.gob.pe', 'hash_empleado1', 2, 'empleado'),
('Carlos Ramos', 'cramos@dremh.gob.pe', 'hash_empleado2', 3, 'empleado');

-- ========================
-- TIPOS DE CORRESPONDENCIA
-- ========================
INSERT INTO corr_tipos (nombre, descripcion) VALUES
('Oficio', 'Documento formal dirigido a un área o persona'),
('Memorando', 'Comunicación interna breve'),
('Solicitud', 'Solicitud de información o recurso'),
('Informe', 'Informe técnico o administrativo');

-- ========================
-- CORRESPONDENCIA (ejemplos de envíos)
-- ========================
INSERT INTO correspondencia (numero_externo, asunto, descripcion, id_tipo, id_estado, id_origen_usuario, id_origen_area, prioridad)
VALUES
('OF-001-2025', 'Solicitud de vacaciones', 'Empleado solicita 10 días de vacaciones en octubre.', 3, 1, 4, 2, 'Normal'),
('OF-002-2025', 'Informe de gastos', 'Se adjunta informe de gastos del mes pasado.', 4, 2, 3, 3, 'Alta'),
('OF-003-2025', 'Capacitación obligatoria', 'Se comunica capacitación para todo el personal.', 2, 1, 2, 1, 'Normal');

-- ========================
-- DESTINATARIOS
-- ========================
INSERT INTO corr_destinatarios (id_correspondencia, id_area, id_usuario)
VALUES
(1, 1, NULL), -- vacaciones → RRHH
(2, 1, NULL), -- informe de gastos → RRHH
(2, 4, 1),    -- informe de gastos → Dirección
(3, NULL, 4), -- capacitación → usuario Ana López
(3, 3, NULL); -- capacitación → Logística

-- ========================
-- ADJUNTOS
-- ========================
INSERT INTO corr_adjuntos (id_correspondencia, nombre_original, ruta_storage, mime, tamano, subido_por)
VALUES
(1, 'solicitud_vacaciones.pdf', '/storage/docs/solicitud_vacaciones.pdf', 'application/pdf', 254000, 4),
(2, 'informe_gastos.xlsx', '/storage/docs/informe_gastos.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 102400, 3),
(3, 'programa_capacitacion.pdf', '/storage/docs/programa_capacitacion.pdf', 'application/pdf', 300000, 2);

-- ========================
-- HISTORIAL
-- ========================
INSERT INTO corr_historial (id_correspondencia, id_usuario, accion, comentario)
VALUES
(1, 4, 'Enviado', 'Solicitud enviada a RRHH'),
(2, 3, 'Enviado', 'Informe enviado a RRHH y Dirección'),
(2, 1, 'Revisado', 'Informe revisado por Dirección'),
(3, 2, 'Enviado', 'Memorando de capacitación enviado a todos'),
(3, 4, 'Recibido', 'Empleado Ana López recibió el memorando');

-- ========================
-- COMENTARIOS
-- ========================
INSERT INTO corr_comentarios (id_correspondencia, id_usuario, comentario, privado)
VALUES
(1, 1, 'RRHH revisará la solicitud en los próximos días', TRUE),
(2, 1, 'Se detectaron inconsistencias menores en el informe', FALSE),
(3, 4, 'Confirmo asistencia a la capacitación', FALSE);

-- ========================
-- NOTIFICACIONES
-- ========================
INSERT INTO corr_notificaciones (id_correspondencia, id_usuario, tipo, mensaje)
VALUES
(1, 1, 'inapp', 'Nueva solicitud de vacaciones enviada'),
(2, 2, 'email', 'Se ha recibido un nuevo informe de gastos'),
(3, 4, 'inapp', 'Ha recibido un memorando de capacitación');

-- ========================
-- AUDITORÍA
-- ========================
INSERT INTO corr_auditoria (id_usuario, accion, id_correspondencia, ip_origen, user_agent)
VALUES
(4, 'crear_correspondencia', 1, '192.168.1.10', 'Mozilla/5.0'),
(3, 'crear_correspondencia', 2, '192.168.1.20', 'Mozilla/5.0'),
(2, 'crear_correspondencia', 3, '192.168.1.30', 'Mozilla/5.0'),
(1, 'ver_correspondencia', 2, '192.168.1.5', 'Mozilla/5.0');
