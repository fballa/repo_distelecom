<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../models/WhatsAppConversacion.php';
require_once __DIR__ . '/../models/WhatsAppMensaje.php';

class WhatsAppController
{
    public function index(): void
    {
        $filtros = [
            'estado' => $_GET['estado'] ?? null,
            'telefono' => $_GET['telefono'] ?? null,
            'nombre' => $_GET['nombre'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = WhatsAppConversacion::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Conversaciones de WhatsApp listadas correctamente.', $result);
    }

    public function show(int $id): void
    {
        $conversacion = WhatsAppConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de WhatsApp no encontrada.', 404);
        }

        Response::success('Conversación obtenida correctamente.', $conversacion);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();

        $data = [
            'telefono' => Validator::sanitizeString($payload['telefono'] ?? null),
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'telefono' => 'required|max:30',
            'nombre' => 'nullable|max:120',
            'estado' => 'nullable|in:Abierta,Cerrada',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $id = WhatsAppConversacion::crear($data);

        if ($id === null) {
            Response::error('No se pudo crear la conversación de WhatsApp.', 500);
        }

        Response::success('Conversación creada correctamente.', ['id' => $id], 201);
    }

    public function update(int $id): void
    {
        $conversacion = WhatsAppConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de WhatsApp no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = [
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'nombre' => 'nullable|max:120',
            'estado' => 'nullable|in:Abierta,Cerrada',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!WhatsAppConversacion::actualizar($id, $data)) {
            Response::error('No se pudo actualizar la conversación de WhatsApp.', 500);
        }

        Response::success('Conversación actualizada correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $conversacion = WhatsAppConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de WhatsApp no encontrada.', 404);
        }

        if (!WhatsAppConversacion::eliminar($id)) {
            Response::error('No se pudo eliminar la conversación de WhatsApp.', 500);
        }

        Response::success('Conversación eliminada correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $conversacion = WhatsAppConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de WhatsApp no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|in:Abierta,Cerrada']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!WhatsAppConversacion::cambiarEstado($id, $estado)) {
            Response::error('No se pudo cambiar el estado de la conversación.', 500);
        }

        Response::success('Estado de la conversación actualizado correctamente.', []);
    }

    public function mensajes(int $id): void
    {
        $conversacion = WhatsAppConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de WhatsApp no encontrada.', 404);
        }

        Response::success('Mensajes listados correctamente.', $conversacion['mensajes']);
    }

    public function guardarMensajePorTelefono(): void
    {
        $payload = $this->getJsonInput();
        $phonenumber = $payload['phonenumber'] ?? null;
        $remitente = Validator::sanitizeString($payload['remitente'] ?? null);
        $mensaje = Validator::sanitizeString($payload['mensaje'] ?? null);

        if ($phonenumber === null || $remitente === null || $mensaje === null) {
            Response::error('Los campos phonenumber, remitente y mensaje son obligatorios.', 422);
        }

        $phonenumber = $this->normalizarTelefono((string) $phonenumber);
        if ($phonenumber === '' || mb_strlen($phonenumber) < 6 || mb_strlen($phonenumber) > 30) {
            Response::error('El campo phonenumber tiene un formato inválido.', 422);
        }

        if (!in_array($remitente, ['Cliente', 'Asesor'], true)) {
            Response::error('El campo remitente tiene un valor inválido.', 422);
        }

        if (trim($mensaje) === '') {
            Response::error('El campo mensaje es obligatorio.', 422);
        }

        $conversacion = WhatsAppConversacion::obtenerPorTelefono($phonenumber);
        $conversacionId = null;

        if ($conversacion) {
            $conversacionId = (int) $conversacion['id'];
        } else {
            $conversacionId = WhatsAppConversacion::crearPorTelefono([
                'phonenumber' => $phonenumber,
                'telefono' => $phonenumber,
                'nombre' => null,
                'estado' => 'Abierta',
            ]);
        }

        if ($conversacionId === null || $conversacionId <= 0) {
            Response::error('No se pudo crear o recuperar la conversación.', 500);
        }

        $mensajeId = WhatsAppMensaje::crear([
            'conversacion_id' => $conversacionId,
            'remitente' => $remitente,
            'mensaje' => $mensaje,
        ]);

        if ($mensajeId === null) {
            Response::error('No se pudo enviar el mensaje.', 500);
        }

        Response::success('Mensaje enviado correctamente.', ['id' => $mensajeId], 201);
    }

    public function enviarMensaje(int $id): void
    {
        $conversacion = WhatsAppConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de WhatsApp no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = [
            'conversacion_id' => $id,
            'remitente' => Validator::sanitizeString($payload['remitente'] ?? null),
            'mensaje' => Validator::sanitizeString($payload['mensaje'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'conversacion_id' => 'required|integer',
            'remitente' => 'required|in:Cliente,Asesor',
            'mensaje' => 'required',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $mensajeId = WhatsAppMensaje::crear($data);
        if ($mensajeId === null) {
            Response::error('No se pudo enviar el mensaje.', 500);
        }

        Response::success('Mensaje enviado correctamente.', ['id' => $mensajeId], 201);
    }

    public function eliminarMensaje(int $id): void
    {
        if (!WhatsAppMensaje::eliminar($id)) {
            Response::error('No se pudo eliminar el mensaje de WhatsApp.', 500);
        }

        Response::success('Mensaje eliminado correctamente.', []);
    }

    private function getJsonInput(): array
    {
        $body = file_get_contents('php://input');
        $input = json_decode($body, true);

        if (!is_array($input)) {
            Response::error('Entrada JSON inválida.', 400);
        }

        return $input;
    }

    private function normalizarTelefono(string $telefono): string
    {
        $telefono = trim($telefono);
        $telefono = preg_replace('/\s+/', '', $telefono);
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);

        if (strpos($telefono, '+') !== false) {
            $telefono = '+' . preg_replace('/[^0-9]/', '', str_replace('+', '', $telefono));
        } else {
            $telefono = preg_replace('/[^0-9]/', '', $telefono);
        }

        return $telefono;
    }
}
