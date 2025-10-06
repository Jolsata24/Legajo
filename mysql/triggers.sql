DELIMITER //
CREATE TRIGGER trg_after_insert_destinatario
AFTER INSERT ON corr_destinatarios
FOR EACH ROW
BEGIN
  INSERT INTO corr_notificaciones (id_correspondencia, id_usuario, mensaje, tipo)
  SELECT NEW.id_correspondencia, u.id, CONCAT('Nueva correspondencia: ', c.asunto), 'inapp'
  FROM usuarios u
  JOIN correspondencia c ON c.id = NEW.id_correspondencia
  WHERE (NEW.id_usuario IS NOT NULL AND u.id = NEW.id_usuario)
    OR (NEW.id_area IS NOT NULL AND u.id_area = NEW.id_area);
END;
//
DELIMITER ;


DELIMITER //
CREATE PROCEDURE sp_cambiar_estado(
  IN p_id_correspondencia BIGINT,
  IN p_id_usuario INT,
  IN p_estado INT,
  IN p_comentario TEXT
)
BEGIN
  UPDATE correspondencia SET id_estado = p_estado WHERE id = p_id_correspondencia;
  INSERT INTO corr_historial (id_correspondencia, id_usuario, accion, comentario)
    VALUES (p_id_correspondencia, p_id_usuario, CONCAT('Cambio de estado a ', p_estado), p_comentario);
END;
//
DELIMITER ;




