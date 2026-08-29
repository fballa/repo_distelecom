<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../models/ConfiguracionEmpresa.php';

class ConfiguracionController
{
    public function obtenerConfiguracion(): void
    {
        $configuracion = ConfiguracionEmpresa::obtener();

        if (!$configuracion) {
            Response::error('Configuración no encontrada.', 404);
        }

        Response::success('Configuración obtenida correctamente.', $configuracion);
    }

    public function crearConfiguracion(): void
    {
        $input = $this->getJsonInput();

        $rules = [
            'nombre_empresa' => 'required|max:255',
            'slogan' => 'nullable|max:255',
            'direccion' => 'nullable|max:500',
            'telefono' => 'nullable|max:50',
            'whatsapp' => 'nullable|max:50',
            'correo' => 'nullable|max:255',
            'sitio_web' => 'nullable|max:255',
            'facebook' => 'nullable|max:255',
            'instagram' => 'nullable|max:255',
            'youtube' => 'nullable|max:255',
            'logo' => 'nullable|max:255',
            'favicon' => 'nullable|max:255',
        ];

        $errors = Validator::validate($input, $rules);
        if (!empty($errors)) {
            Response::error('Validación fallida.', 422);
        }

        if (ConfiguracionEmpresa::existeConfiguracion()) {
            Response::error('La configuración ya existe. Use PUT para actualizar.', 409);
        }

        $createdId = ConfiguracionEmpresa::crear($input);
        if (!$createdId) {
            Response::error('No se pudo crear la configuración.', 500);
        }

        Response::success('Configuración creada correctamente.', ['id' => $createdId], 201);
    }

    public function actualizarConfiguracion(): void
    {
        $input = $this->getJsonInput();

        $rules = [
            'nombre_empresa' => 'nullable|max:255',
            'slogan' => 'nullable|max:255',
            'direccion' => 'nullable|max:500',
            'telefono' => 'nullable|max:50',
            'whatsapp' => 'nullable|max:50',
            'correo' => 'nullable|max:255',
            'sitio_web' => 'nullable|max:255',
            'facebook' => 'nullable|max:255',
            'instagram' => 'nullable|max:255',
            'youtube' => 'nullable|max:255',
            'logo' => 'nullable|max:255',
            'favicon' => 'nullable|max:255',
        ];

        $errors = Validator::validate($input, $rules);
        if (!empty($errors)) {
            Response::error('Validación fallida.', 422);
        }

        $updated = ConfiguracionEmpresa::actualizar($input);
        if (!$updated) {
            Response::error('No se pudo actualizar la configuración.', 500);
        }

        Response::success('Configuración actualizada correctamente.');
    }

    public function actualizarLogo(): void
    {
        $input = $this->getJsonInput();

        $errors = Validator::validate($input, ['logo' => 'required|max:255']);
        if (!empty($errors)) {
            Response::error('Validación fallida.', 422);
        }

        $updated = ConfiguracionEmpresa::actualizarLogo($input['logo']);
        if (!$updated) {
            Response::error('No se pudo actualizar el logo.', 500);
        }

        Response::success('Logo actualizado correctamente.');
    }

    public function actualizarFavicon(): void
    {
        $input = $this->getJsonInput();

        $errors = Validator::validate($input, ['favicon' => 'required|max:255']);
        if (!empty($errors)) {
            Response::error('Validación fallida.', 422);
        }

        $updated = ConfiguracionEmpresa::actualizarFavicon($input['favicon']);
        if (!$updated) {
            Response::error('No se pudo actualizar el favicon.', 500);
        }

        Response::success('Favicon actualizado correctamente.');
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
