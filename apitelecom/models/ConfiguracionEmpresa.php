<?php

require_once __DIR__ . '/../config/Database.php';

class ConfiguracionEmpresa
{
    public static function obtener(): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, nombre_empresa, slogan, direccion, telefono, whatsapp, correo, sitio_web, facebook, instagram, youtube, logo, favicon, created_at FROM configuracion_empresa WHERE id = 1 LIMIT 1');
            $stmt->execute();
            $configuracion = $stmt->fetch();

            return $configuracion ?: null;
        } catch (PDOException $e) {
            error_log('ConfiguracionEmpresa::obtener error: ' . $e->getMessage());
            return null;
        }
    }

    public static function existeConfiguracion(): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM configuracion_empresa WHERE id = 1');
            $stmt->execute();
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('ConfiguracionEmpresa::existeConfiguracion error: ' . $e->getMessage());
            return false;
        }
    }

    public static function crear(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO configuracion_empresa (nombre_empresa, slogan, direccion, telefono, whatsapp, correo, sitio_web, facebook, instagram, youtube, logo, favicon, created_at) VALUES (:nombre_empresa, :slogan, :direccion, :telefono, :whatsapp, :correo, :sitio_web, :facebook, :instagram, :youtube, :logo, :favicon, NOW())');
            $stmt->execute([
                ':nombre_empresa' => $data['nombre_empresa'] ?? null,
                ':slogan' => $data['slogan'] ?? null,
                ':direccion' => $data['direccion'] ?? null,
                ':telefono' => $data['telefono'] ?? null,
                ':whatsapp' => $data['whatsapp'] ?? null,
                ':correo' => $data['correo'] ?? null,
                ':sitio_web' => $data['sitio_web'] ?? null,
                ':facebook' => $data['facebook'] ?? null,
                ':instagram' => $data['instagram'] ?? null,
                ':youtube' => $data['youtube'] ?? null,
                ':logo' => $data['logo'] ?? null,
                ':favicon' => $data['favicon'] ?? null,
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            error_log('ConfiguracionEmpresa::crear error: ' . $e->getMessage());
            return null;
        }
    }

    public static function actualizar(array $data): bool
    {
        try {
            if (!self::existeConfiguracion()) {
                return self::crear($data) !== null;
            }

            $db = Database::getConnection();
            $fields = [];
            $params = [':id' => 1];

            if (array_key_exists('nombre_empresa', $data)) {
                $fields[] = 'nombre_empresa = :nombre_empresa';
                $params[':nombre_empresa'] = $data['nombre_empresa'];
            }
            if (array_key_exists('slogan', $data)) {
                $fields[] = 'slogan = :slogan';
                $params[':slogan'] = $data['slogan'];
            }
            if (array_key_exists('direccion', $data)) {
                $fields[] = 'direccion = :direccion';
                $params[':direccion'] = $data['direccion'];
            }
            if (array_key_exists('telefono', $data)) {
                $fields[] = 'telefono = :telefono';
                $params[':telefono'] = $data['telefono'];
            }
            if (array_key_exists('whatsapp', $data)) {
                $fields[] = 'whatsapp = :whatsapp';
                $params[':whatsapp'] = $data['whatsapp'];
            }
            if (array_key_exists('correo', $data)) {
                $fields[] = 'correo = :correo';
                $params[':correo'] = $data['correo'];
            }
            if (array_key_exists('sitio_web', $data)) {
                $fields[] = 'sitio_web = :sitio_web';
                $params[':sitio_web'] = $data['sitio_web'];
            }
            if (array_key_exists('facebook', $data)) {
                $fields[] = 'facebook = :facebook';
                $params[':facebook'] = $data['facebook'];
            }
            if (array_key_exists('instagram', $data)) {
                $fields[] = 'instagram = :instagram';
                $params[':instagram'] = $data['instagram'];
            }
            if (array_key_exists('youtube', $data)) {
                $fields[] = 'youtube = :youtube';
                $params[':youtube'] = $data['youtube'];
            }
            if (array_key_exists('logo', $data)) {
                $fields[] = 'logo = :logo';
                $params[':logo'] = $data['logo'];
            }
            if (array_key_exists('favicon', $data)) {
                $fields[] = 'favicon = :favicon';
                $params[':favicon'] = $data['favicon'];
            }

            if (empty($fields)) {
                return true;
            }

            $sql = 'UPDATE configuracion_empresa SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('ConfiguracionEmpresa::actualizar error: ' . $e->getMessage());
            return false;
        }
    }

    public static function actualizarLogo(string $url): bool
    {
        try {
            if (!self::existeConfiguracion()) {
                return self::crear(['logo' => $url]) !== null;
            }

            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE configuracion_empresa SET logo = :logo WHERE id = 1');
            return $stmt->execute([':logo' => $url]);
        } catch (PDOException $e) {
            error_log('ConfiguracionEmpresa::actualizarLogo error: ' . $e->getMessage());
            return false;
        }
    }

    public static function actualizarFavicon(string $url): bool
    {
        try {
            if (!self::existeConfiguracion()) {
                return self::crear(['favicon' => $url]) !== null;
            }

            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE configuracion_empresa SET favicon = :favicon WHERE id = 1');
            return $stmt->execute([':favicon' => $url]);
        } catch (PDOException $e) {
            error_log('ConfiguracionEmpresa::actualizarFavicon error: ' . $e->getMessage());
            return false;
        }
    }
}
