<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class UsuarioController
{
    public function index(): void
    {
        $filtros = [
            'nombre' => $_GET['nombre'] ?? null,
            'correo' => $_GET['correo'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'rol_id' => $_GET['rol_id'] ?? null,
        ];

        $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && (int) $_GET['pagina'] > 0 ? (int) $_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) && is_numeric($_GET['limite']) && (int) $_GET['limite'] > 0 ? (int) $_GET['limite'] : 20;

        $result = Usuario::obtenerTodos($filtros, $pagina, $limite);
        Response::success('Usuarios listados correctamente.', $result);
    }

    public function show(int $id): void
    {
        $usuario = Usuario::obtenerPorId($id);

        if (!$usuario) {
            Response::error('Usuario no encontrado.', 404);
        }

        Response::success('Usuario obtenido correctamente.', $usuario);
    }

    public function store(): void
    {
        $payload = $this->getJsonInput();
        $data = [
            'rol_id' => isset($payload['rol_id']) ? (int) $payload['rol_id'] : null,
            'nombre' => Validator::sanitizeString($payload['nombre'] ?? null),
            'apellido' => Validator::sanitizeString($payload['apellido'] ?? null),
            'correo' => Validator::sanitizeString($payload['correo'] ?? null),
            'telefono' => Validator::sanitizeString($payload['telefono'] ?? null),
            'password' => Validator::sanitizeString($payload['password'] ?? null),
            'estado' => Validator::sanitizeString($payload['estado'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'rol_id' => 'required|integer',
            'nombre' => 'required|max:100',
            'apellido' => 'nullable|max:100',
            'correo' => 'required|email|max:150',
            'telefono' => 'nullable|max:30',
            'password' => 'required|min:6',
            'estado' => 'nullable|in:Activo,Inactivo',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!Rol::obtenerPorId($data['rol_id'])) {
            Response::error('El rol no existe.', 422);
        }

        if (Usuario::existeCorreo($data['correo'])) {
            Response::error('Ya existe un usuario con ese correo.', 409);
        }

        $usuarioId = Usuario::crear($data);
        if ($usuarioId === null) {
            Response::error('No se pudo crear el usuario.', 500);
        }

        Response::success('Usuario creado correctamente.', ['id' => $usuarioId], 201);
    }

    public function update(int $id): void
    {
        $usuarioExistente = Usuario::obtenerPorId($id);

        if (!$usuarioExistente) {
            Response::error('Usuario no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $data = [];

        if (array_key_exists('rol_id', $payload)) {
            $data['rol_id'] = (int) $payload['rol_id'];
        }
        if (array_key_exists('nombre', $payload)) {
            $data['nombre'] = Validator::sanitizeString($payload['nombre']);
        }
        if (array_key_exists('apellido', $payload)) {
            $data['apellido'] = Validator::sanitizeString($payload['apellido']);
        }
        if (array_key_exists('correo', $payload)) {
            $data['correo'] = Validator::sanitizeString($payload['correo']);
        }
        if (array_key_exists('telefono', $payload)) {
            $data['telefono'] = Validator::sanitizeString($payload['telefono']);
        }
        if (array_key_exists('password', $payload)) {
            $data['password'] = Validator::sanitizeString($payload['password']);
        }
        if (array_key_exists('estado', $payload)) {
            $data['estado'] = Validator::sanitizeString($payload['estado']);
        }

        $errors = Validator::validate($data, [
            'rol_id' => 'nullable|integer',
            'nombre' => 'nullable|max:100',
            'apellido' => 'nullable|max:100',
            'correo' => 'nullable|email|max:150',
            'telefono' => 'nullable|max:30',
            'password' => 'nullable|min:6',
            'estado' => 'nullable|in:Activo,Inactivo',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if ($data['rol_id'] !== null && !Rol::obtenerPorId($data['rol_id'])) {
            Response::error('El rol no existe.', 422);
        }

        if (!empty($data['correo']) && Usuario::existeCorreo($data['correo'], $id)) {
            Response::error('Ya existe un usuario con ese correo.', 409);
        }

        if (!Usuario::actualizar($id, $data)) {
            Response::error('No se pudo actualizar el usuario.', 500);
        }

        Response::success('Usuario actualizado correctamente.', []);
    }

    public function destroy(int $id): void
    {
        $usuarioExistente = Usuario::obtenerPorId($id);

        if (!$usuarioExistente) {
            Response::error('Usuario no encontrado.', 404);
        }

        if (!Usuario::eliminar($id)) {
            Response::error('No se pudo eliminar el usuario.', 500);
        }

        Response::success('Usuario eliminado correctamente.', []);
    }

    public function cambiarEstado(int $id): void
    {
        $usuarioExistente = Usuario::obtenerPorId($id);

        if (!$usuarioExistente) {
            Response::error('Usuario no encontrado.', 404);
        }

        $payload = $this->getJsonInput();
        $estado = Validator::sanitizeString($payload['estado'] ?? null);

        $errors = Validator::validate(['estado' => $estado], ['estado' => 'required|in:Activo,Inactivo']);
        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        if (!Usuario::cambiarEstado($id, $estado)) {
            Response::error('No se pudo cambiar el estado del usuario.', 500);
        }

        Response::success('Estado del usuario actualizado correctamente.', []);
    }

    public function login(): void
    {
        $payload = $this->getJsonInput();
        $data = [
            'correo' => Validator::sanitizeString($payload['correo'] ?? null),
            'password' => Validator::sanitizeString($payload['password'] ?? null),
        ];

        $errors = Validator::validate($data, [
            'correo' => 'required|email|max:150',
            'password' => 'required|min:6',
        ]);

        if (!empty($errors)) {
            Response::error(array_values($errors)[0], 422);
        }

        $usuario = Usuario::autenticar($data['correo'], $data['password']);

        if (!$usuario) {
            Response::error('Credenciales incorrectas.', 401);
        }

        Response::success('Inicio de sesión exitoso.', $usuario);
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
