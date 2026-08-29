<?php

require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class CitaController
{
    public function index(): void
    {
        $filtros = [
            'cliente_id' => $_GET['cliente_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'tipo_cita' => $_GET['tipo_cita'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
            'buscar' => $_GET['buscar'] ?? null,
            'telefono' => $_GET['telefono'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Cita::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Citas listadas correctamente.', $result);
    }

    public function show(int $id): void
    {
        $cita = Cita::obtenerPorId($id);

        if (!$cita) {
            Response::error('Cita no encontrada.', 404);
        }

        Response::success('Cita obtenida correctamente.', $cita);
    }

    public function porCliente(string $cliente_id): void
    {
        $cliente_id = Validator::sanitizeString($cliente_id);

        if (empty($cliente_id)) {
            Response::error('Cliente ID inválido.', 400);
        }

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Cita::obtenerPorCliente($cliente_id, $pagina, $limite);
        Response::success('Citas del cliente listadas correctamente.', $result);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();

        $data = [
            'phone' => isset($payload['phone']) ? (is_numeric($payload['phone']) ? (int) $payload['phone'] : $payload['phone']) : null,
            'cliente_id' => Validator::sanitizeString($payload['cliente_id'] ?? null),
            'fecha_cita' => Validator::sanitizeString($payload['fecha_cita'] ?? null),
            'hora_cita' => Validator::sanitizeString($payload['hora_cita'] ?? null),
            'tipo_cita' => Validator::sanitizeString($payload['tipo_cita'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
            'notas' => Validator::sanitizeString($payload['notas'] ?? null),
            'recordatorio_24h' => isset($payload['recordatorio_24h']) ? $payload['recordatorio_24h'] : 0,
            'recordatorio_1h' => isset($payload['recordatorio_1h']) ? $payload['recordatorio_1h'] : 0,
        ];

        $rules = [
            'cliente_id' => 'required',
            'fecha_cita' => 'required',
            'hora_cita' => 'required',
            'tipo_cita' => 'required|max:100',
            'phone' => 'nullable|numeric',
            'estado' => 'nullable|in:Pendiente,Confirmada,Cancelada,Completada',
            'recordatorio_24h' => 'nullable|boolean',
            'recordatorio_1h' => 'nullable|boolean',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        // Validar formatos de fecha y hora y normalizar
        $fecha = $data['fecha_cita'];
        $hora = $data['hora_cita'];

        $fechaHora = null;

        // fecha can be YYYY-MM-DD or YYYY-MM-DD HH:MM:SS
        $dtFecha = DateTime::createFromFormat('Y-m-d H:i:s', $fecha);
        if ($dtFecha && $dtFecha->format('Y-m-d H:i:s') === $fecha) {
            $fechaHora = $dtFecha->format('Y-m-d H:i:s');
        } else {
            $dtFecha2 = DateTime::createFromFormat('Y-m-d', $fecha);
            if ($dtFecha2 && $dtFecha2->format('Y-m-d') === $fecha) {
                // hora can be H:i or H:i:s
                $dtHora = DateTime::createFromFormat('H:i:s', $hora) ?: DateTime::createFromFormat('H:i', $hora);
                if (!$dtHora) {
                    Response::error('Hora con formato inválido. Use HH:MM o HH:MM:SS.', 422);
                }
                $horaNormalized = $dtHora->format('H:i:s');
                $fechaHora = $dtFecha2->format('Y-m-d') . ' ' . $horaNormalized;
                // keep hora as normalized
                $data['hora_cita'] = $horaNormalized;
            } else {
                Response::error('Fecha con formato inválido. Use YYYY-MM-DD o YYYY-MM-DD HH:MM:SS.', 422);
            }
        }

        // If fecha included time but hora provided separately, accept fecha's time or set hora accordingly
        if ($fechaHora !== null) {
            $data['fecha_cita'] = $fechaHora;
        }

        // Ensure cliente exists
        if (!Cita::existeCliente($data['cliente_id'])) {
            Response::error('El cliente especificado no existe.', 404);
        }

        $insertId = Cita::crear($data);

        if ($insertId === null) {
            Response::error('No se pudo crear la cita.', 500);
        }

        Response::success('Cita creada correctamente.', ['id' => $insertId], 201);
    }

    public function update(int $id): void
    {
        $citaExistente = Cita::obtenerPorId($id);
        if (!$citaExistente) {
            Response::error('Cita no encontrada.', 404);
        }

        $payload = $this->getJsonInput();

        $data = [
            'phone' => isset($payload['phone']) ? (is_numeric($payload['phone']) ? (int) $payload['phone'] : $payload['phone']) : null,
            'cliente_id' => array_key_exists('cliente_id', $payload) ? Validator::sanitizeString($payload['cliente_id']) : null,
            'fecha_cita' => array_key_exists('fecha_cita', $payload) ? Validator::sanitizeString($payload['fecha_cita']) : null,
            'hora_cita' => array_key_exists('hora_cita', $payload) ? Validator::sanitizeString($payload['hora_cita']) : null,
            'tipo_cita' => array_key_exists('tipo_cita', $payload) ? Validator::sanitizeString($payload['tipo_cita']) : null,
            'estado' => array_key_exists('estado', $payload) ? Validator::sanitizeString($payload['estado']) : null,
            'notas' => array_key_exists('notas', $payload) ? Validator::sanitizeString($payload['notas']) : null,
            'recordatorio_24h' => array_key_exists('recordatorio_24h', $payload) ? $payload['recordatorio_24h'] : null,
            'recordatorio_1h' => array_key_exists('recordatorio_1h', $payload) ? $payload['recordatorio_1h'] : null,
        ];

        // Remove nulls that weren't provided
        $updateData = [];
        foreach ($data as $k => $v) {
            if ($v !== null) {
                $updateData[$k] = $v;
            }
        }

        if (empty($updateData)) {
            Response::success('Nada que actualizar.', []);
        }

        // Validate if present
        $rules = [];
        if (isset($updateData['cliente_id'])) {
            $rules['cliente_id'] = 'required';
        }
        if (isset($updateData['fecha_cita'])) {
            $rules['fecha_cita'] = 'required';
        }
        if (isset($updateData['hora_cita'])) {
            $rules['hora_cita'] = 'required';
        }
        if (isset($updateData['tipo_cita'])) {
            $rules['tipo_cita'] = 'nullable|max:100';
        }
        if (isset($updateData['estado'])) {
            $rules['estado'] = 'nullable|in:Pendiente,Confirmada,Cancelada,Completada';
        }
        if (isset($updateData['recordatorio_24h'])) {
            $rules['recordatorio_24h'] = 'nullable|boolean';
        }
        if (isset($updateData['recordatorio_1h'])) {
            $rules['recordatorio_1h'] = 'nullable|boolean';
        }

        $errors = Validator::validate($updateData, $rules);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        // If cliente_id provided, validate existence
        if (isset($updateData['cliente_id']) && !Cita::existeCliente($updateData['cliente_id'])) {
            Response::error('El cliente especificado no existe.', 404);
        }

        // If fecha/hora provided validate and normalize
        if (isset($updateData['fecha_cita'])) {
            $fecha = $updateData['fecha_cita'];
            $dtFecha = DateTime::createFromFormat('Y-m-d H:i:s', $fecha);
            if ($dtFecha && $dtFecha->format('Y-m-d H:i:s') === $fecha) {
                $updateData['fecha_cita'] = $dtFecha->format('Y-m-d H:i:s');
            } else {
                $dtFecha2 = DateTime::createFromFormat('Y-m-d', $fecha);
                if ($dtFecha2 && $dtFecha2->format('Y-m-d') === $fecha) {
                    // need hora to combine
                    $hora = $updateData['hora_cita'] ?? $citaExistente['hora_cita'] ?? null;
                    $dtHora = DateTime::createFromFormat('H:i:s', $hora) ?: DateTime::createFromFormat('H:i', $hora);
                    if (!$dtHora) {
                        Response::error('Hora con formato inválido o faltante para combinar con fecha.', 422);
                    }
                    $updateData['hora_cita'] = $dtHora->format('H:i:s');
                    $updateData['fecha_cita'] = $dtFecha2->format('Y-m-d') . ' ' . $updateData['hora_cita'];
                } else {
                    Response::error('Fecha con formato inválido. Use YYYY-MM-DD o YYYY-MM-DD HH:MM:SS.', 422);
                }
            }
        }

        if (isset($updateData['hora_cita']) && !isset($updateData['fecha_cita'])) {
            // normalize hora
            $dtHora = DateTime::createFromFormat('H:i:s', $updateData['hora_cita']) ?: DateTime::createFromFormat('H:i', $updateData['hora_cita']);
            if (!$dtHora) {
                Response::error('Hora con formato inválido. Use HH:MM o HH:MM:SS.', 422);
            }
            $updateData['hora_cita'] = $dtHora->format('H:i:s');
        }

        if (!Cita::actualizar($id, $updateData)) {
            Response::error('No se pudo actualizar la cita.', 500);
        }

        Response::success('Cita actualizada correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $citaExistente = Cita::obtenerPorId($id);
        if (!$citaExistente) {
            Response::error('Cita no encontrada.', 404);
        }

        if (!Cita::eliminar($id)) {
            Response::error('No se pudo cancelar la cita.', 500);
        }

        Response::success('Cita cancelada correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $citaExistente = Cita::obtenerPorId($id);
        if (!$citaExistente) {
            Response::error('Cita no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|in:Pendiente,Confirmada,Cancelada,Completada']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!Cita::cambiarEstado($id, $estado)) {
            Response::error('No se pudo cambiar el estado de la cita.', 500);
        }

        Response::success('Estado de la cita actualizado correctamente.', []);
    }

    public function recordatorios(): void
    {
        $result = Cita::obtenerRecordatorios();
        Response::success('Recordatorios obtenidos correctamente.', $result);
    }

    public function marcarRecordatorio(int $id): void
    {
        $citaExistente = Cita::obtenerPorId($id);
        if (!$citaExistente) {
            Response::error('Cita no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $tipo = Validator::sanitizeString($payload['tipo'] ?? null);

        $errors = Validator::validate(['tipo' => $tipo], ['tipo' => 'required|in:24h,1h']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!Cita::marcarRecordatorio($id, $tipo)) {
            Response::error('No se pudo marcar el recordatorio.', 500);
        }

        Response::success('Recordatorio marcado como enviado correctamente.', []);
    }

    private function getJsonInput(): array
    {
        $body = file_get_contents('php://input');
        $input = json_decode($body, true);

        if (!is_array($input)) {
            Response::error('Entrada JSON inválida.', 400);
        }

        // sanitize strings
        return array_map(function ($v) {
            if (is_string($v)) {
                return Validator::sanitizeString($v);
            }
            return $v;
        }, $input);
    }
}
