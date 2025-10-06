create database milegajo;
USE milegajo;
-- Tabla de áreas (departamentos)
CREATE TABLE areas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Usuarios (asume que ya tienes tabla usuario; aquí la dejo integrada)
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  id_area INT,
  rol ENUM('admin','rrhh','jefe_area','empleado') DEFAULT 'empleado',
  activo BOOLEAN DEFAULT TRUE,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_area) REFERENCES areas(id)
);

-- Tipos de correspondencia (opcional)
CREATE TABLE corr_tipos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  descripcion TEXT
);

-- Estados de correspondencia
CREATE TABLE corr_estados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL -- Ej: Pendiente, En revisión, Aprobado, Rechazado, Archivado
);

INSERT INTO corr_estados (nombre) VALUES ('Pendiente'),('En revision'),('Aprobado'),('Rechazado'),('Archivado');

-- Tabla principal: correspondencia
CREATE TABLE correspondencia (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  numero_externo VARCHAR(100), -- número de oficio o referencia
  asunto VARCHAR(255) NOT NULL,
  descripcion TEXT,
  id_tipo INT,
  id_estado INT DEFAULT 1,
  id_origen_usuario INT NOT NULL,
  id_origen_area INT,
  fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_limite DATE NULL,
  prioridad ENUM('Baja','Normal','Alta') DEFAULT 'Normal',
  eliminado_logico BOOLEAN DEFAULT FALSE,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_tipo) REFERENCES corr_tipos(id),
  FOREIGN KEY (id_estado) REFERENCES corr_estados(id),
  FOREIGN KEY (id_origen_usuario) REFERENCES usuarios(id),
  FOREIGN KEY (id_origen_area) REFERENCES areas(id)
);

-- Destinatarios (puede enviarse a varios usuarios y/o a un área)
CREATE TABLE corr_destinatarios (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_correspondencia BIGINT NOT NULL,
  id_area INT NULL,
  id_usuario INT NULL,
  visto BOOLEAN DEFAULT FALSE,
  recibido_en DATETIME NULL,
  leido_en DATETIME NULL,
  FOREIGN KEY (id_correspondencia) REFERENCES correspondencia(id),
  FOREIGN KEY (id_area) REFERENCES areas(id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

-- Archivos adjuntos
CREATE TABLE corr_adjuntos (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_correspondencia BIGINT NOT NULL,
  nombre_original VARCHAR(255),
  ruta_storage VARCHAR(500), -- ruta en storage / S3 / filesystem
  mime VARCHAR(100),
  tamano BIGINT,
  subido_por INT,
  subido_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_correspondencia) REFERENCES correspondencia(id),
  FOREIGN KEY (subido_por) REFERENCES usuarios(id)
);

-- Historial de eventos (seguimiento)
CREATE TABLE corr_historial (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_correspondencia BIGINT NOT NULL,
  id_usuario INT NOT NULL,
  accion VARCHAR(100) NOT NULL, -- Ej: enviado, reasignado, aprobado, comentado
  comentario TEXT,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  datos_json JSON NULL,
  FOREIGN KEY (id_correspondencia) REFERENCES correspondencia(id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

-- Comentarios (puede usarse para observaciones internas)
CREATE TABLE corr_comentarios (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_correspondencia BIGINT NOT NULL,
  id_usuario INT NOT NULL,
  comentario TEXT NOT NULL,
  privado BOOLEAN DEFAULT TRUE, -- privado para área o público dentro del legajo
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_correspondencia) REFERENCES correspondencia(id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

-- Notificaciones internas (puede leerse por worker que mande emails)
CREATE TABLE corr_notificaciones (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_correspondencia BIGINT,
  id_usuario INT,
  tipo ENUM('email','inapp') DEFAULT 'inapp',
  mensaje TEXT,
  leido BOOLEAN DEFAULT FALSE,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_correspondencia) REFERENCES correspondencia(id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

-- Log de auditoría (accesos y descargas)
CREATE TABLE corr_auditoria (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT,
  accion VARCHAR(100), -- ej: ver_correspondencia, descargar_adjunto
  id_correspondencia BIGINT NULL,
  id_adjuntos BIGINT NULL,
  ip_origen VARCHAR(45),
  user_agent TEXT,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
