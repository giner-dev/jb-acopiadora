<?php
require_once __DIR__ . '/../repositories/GranoRepository.php';

class GranoService {
    private $granoRepository;

    public function __construct() {
        $this->granoRepository = new GranoRepository();
    }

    public function obtenerTodos($filters = [], $page = 1, $perPage = 20) {
        return $this->granoRepository->findAllPaginated($filters, $page, $perPage);
    }

    public function obtenerPorId($id) {
        return $this->granoRepository->findById($id);
    }

    public function crear($datos) {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if ($this->granoRepository->existeNombre($datos['nombre'])) {
            return ['success' => false, 'errors' => ['El nombre del grano ya está registrado']];
        }

        $datosInsert = [
            'nombre' => $datos['nombre'],
            'unidad_id' => !empty($datos['unidad_id']) ? $datos['unidad_id'] : null,
            'descripcion' => $datos['descripcion'] ?? null,
            'estado' => 'activo'
        ];

        $id = $this->granoRepository->create($datosInsert);

        if ($id) {
            logMessage("Grano creado: ID $id - {$datos['nombre']}", 'info');
            return ['success' => true, 'id' => $id];
        }

        return ['success' => false, 'errors' => ['Error al crear el grano']];
    }

    public function actualizar($id, $datos) {
        $grano = $this->granoRepository->findById($id);
        if (!$grano) {
            return ['success' => false, 'errors' => ['Grano no encontrado']];
        }

        $errores = $this->validarDatos($datos, $id);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if ($this->granoRepository->existeNombre($datos['nombre'], $id)) {
            return ['success' => false, 'errors' => ['El nombre del grano ya está registrado en otro registro']];
        }

        $datosUpdate = [
            'nombre' => $datos['nombre'],
            'unidad_id' => !empty($datos['unidad_id']) ? $datos['unidad_id'] : null,
            'descripcion' => $datos['descripcion'] ?? null,
            'estado' => $datos['estado']
        ];

        $resultado = $this->granoRepository->update($id, $datosUpdate);

        if ($resultado) {
            logMessage("Grano actualizado: ID $id", 'info');
            return ['success' => true];
        }

        return ['success' => false, 'errors' => ['Error al actualizar el grano']];
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $grano = $this->granoRepository->findById($id);

        if (!$grano) {
            return ['success' => false, 'message' => 'Grano no encontrado'];
        }

        if ($grano->estado === $nuevoEstado) {
            return ['success' => false, 'message' => 'El grano ya tiene ese estado'];
        }

        $resultado = $this->granoRepository->cambiarEstado($id, $nuevoEstado);

        if ($resultado) {
            logMessage("Estado cambiado para grano ID $id: {$grano->estado} -> $nuevoEstado", 'info');
            return ['success' => true, 'message' => 'Estado actualizado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al cambiar el estado'];
    }

    public function eliminar($id) {
        $grano = $this->granoRepository->findById($id);

        if (!$grano) {
            return ['success' => false, 'message' => 'Grano no encontrado'];
        }

        if ($this->granoRepository->tieneAcopios($id)) {
            return [
                'success' => false, 
                'message' => 'No se puede eliminar un grano con acopios registrados. Inactívelo en su lugar.'
            ];
        }

        $resultado = $this->granoRepository->delete($id);

        if ($resultado) {
            logMessage("Grano eliminado: ID $id - {$grano->nombre}", 'info');
            return ['success' => true, 'message' => 'Grano eliminado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar el grano'];
    }

    public function obtenerActivos() {
        return $this->granoRepository->getActivos();
    }

    public function contarTotal($filters = []) {
        return $this->granoRepository->count($filters);
    }

    public function obtenerUnidades() {
        return $this->granoRepository->getAllUnidades();
    }

    public function obtenerPrecioActual($granoId) {
        return $this->granoRepository->getPrecioActual($granoId);
    }

    public function obtenerHistorialPrecios($granoId, $limit = 30) {
        return $this->granoRepository->getHistorialPrecios($granoId, $limit);
    }

    public function registrarPrecio($granoId, $precio, $fecha = null) {
        if (empty($precio) || $precio <= 0) {
            return ['success' => false, 'message' => 'El precio debe ser mayor a 0'];
        }

        $grano = $this->granoRepository->findById($granoId);
        if (!$grano) {
            return ['success' => false, 'message' => 'Grano no encontrado'];
        }

        if (empty($fecha)) {
            $fecha = date('Y-m-d');
        }

        $resultado = $this->granoRepository->registrarPrecio(
            $granoId,
            $precio,
            $fecha,
            authUserId()
        );

        if ($resultado) {
            logMessage("Precio registrado para grano ID $granoId: Bs $precio en fecha $fecha", 'info');
            return ['success' => true, 'message' => 'Precio registrado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al registrar el precio'];
    }

    private function validarDatos($datos, $excludeId = null) {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es obligatorio';
        } elseif (strlen($datos['nombre']) < 3) {
            $errores[] = 'El nombre debe tener al menos 3 caracteres';
        }

        return $errores;
    }
}