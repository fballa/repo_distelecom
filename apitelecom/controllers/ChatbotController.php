<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../models/ChatbotConversacion.php';
require_once __DIR__ . '/../models/ChatbotMensaje.php';

class ChatbotController
{
    public function index(): void
    {
        $filtros = [
            'estado' => $_GET['estado'] ?? null,
            'ip' => $_GET['ip'] ?? null,
            'nombre' => $_GET['nombre'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = ChatbotConversacion::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Conversaciones de chatbot listadas correctamente.', $result);
    }

    public function show(int $id): void
    {
        $conversacion = ChatbotConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de chatbot no encontrada.', 404);
        }

        Response::success('Conversación obtenida correctamente.', $conversacion);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();

        $data = [
            'uuid' => Validator::sanitizeString($payload['uuid'] ?? null),
            'ip' => Validator::sanitizeString($payload['ip'] ?? null),
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'uuid' => 'required|max:100',
            'ip' => 'nullable|max:45',
            'nombre' => 'nullable|max:120',
            'estado' => 'nullable|in:Activa,Finalizada',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (ChatbotConversacion::obtenerPorUuid($data['uuid'])) {
            Response::error('El UUID ya existe.', 409);
        }

        $id = ChatbotConversacion::crear($data);

        if ($id === null) {
            Response::error('No se pudo crear la conversación de chatbot.', 500);
        }

        Response::success('Conversación creada correctamente.', ['id' => $id], 201);
    }

    public function update(int $id): void
    {
        $conversacion = ChatbotConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de chatbot no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = [
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'nombre' => 'nullable|max:120',
            'estado' => 'nullable|in:Activa,Finalizada',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!ChatbotConversacion::actualizar($id, $data)) {
            Response::error('No se pudo actualizar la conversación de chatbot.', 500);
        }

        Response::success('Conversación actualizada correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $conversacion = ChatbotConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de chatbot no encontrada.', 404);
        }

        if (!ChatbotConversacion::eliminar($id)) {
            Response::error('No se pudo eliminar la conversación de chatbot.', 500);
        }

        Response::success('Conversación eliminada correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $conversacion = ChatbotConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de chatbot no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|in:Activa,Finalizada']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!ChatbotConversacion::cambiarEstado($id, $estado)) {
            Response::error('No se pudo cambiar el estado de la conversación.', 500);
        }

        Response::success('Estado de la conversación actualizado correctamente.', []);
    }

    public function mensajes(int $id): void
    {
        $conversacion = ChatbotConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de chatbot no encontrada.', 404);
        }

        Response::success('Mensajes listados correctamente.', $conversacion['mensajes']);
    }

    public function enviarMensaje(int $id): void
    {
        $conversacion = ChatbotConversacion::obtenerPorId($id);

        if (!$conversacion) {
            Response::error('Conversación de chatbot no encontrada.', 404);
        }

        $payload = $this->getJsonInput();
        $data = [
            'conversacion_id' => $id,
            'remitente' => Validator::sanitizeString($payload['remitente'] ?? null),
            'mensaje' => Validator::sanitizeString($payload['mensaje'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'conversacion_id' => 'required|integer',
            'remitente' => 'required|in:Usuario,Bot',
            'mensaje' => 'required',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $mensajeId = ChatbotMensaje::crear($data);
        if ($mensajeId === null) {
            Response::error('No se pudo enviar el mensaje.', 500);
        }

        Response::success('Mensaje enviado correctamente.', ['id' => $mensajeId], 201);
    }

    public function eliminarMensaje(int $id): void
    {
        if (!ChatbotMensaje::eliminar($id)) {
            Response::error('No se pudo eliminar el mensaje de chatbot.', 500);
        }

        Response::success('Mensaje eliminado correctamente.', []);
    }

    public function iniciar(): void
    {
        $payload = $this->getJsonInput();
        $data = [
            'uuid' => Validator::sanitizeString($payload['uuid'] ?? null),
            'ip' => Validator::sanitizeString($payload['ip'] ?? null),
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'uuid' => 'required|max:100',
            'ip' => 'nullable|max:45',
            'nombre' => 'nullable|max:120',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (ChatbotConversacion::obtenerPorUuid($data['uuid'])) {
            Response::error('El UUID ya existe.', 409);
        }

        $id = ChatbotConversacion::crear($data);
        if ($id === null) {
            Response::error('No se pudo iniciar la conversación de chatbot.', 500);
        }

        Response::success('Conversación iniciada correctamente.', ['id' => $id], 201);
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
}
