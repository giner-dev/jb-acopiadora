<?php
require_once __DIR__ . '/../repositories/ProductoRepository.php';

class ProductoService {
    private $productoRepository;

    public function __construct() {
        $this->productoRepository = new ProductoRepository();
    }

    public function obtenerTodos($filters = [], $page = 1, $perPage = 20) {
        return $this->productoRepository->findAllPaginated($filters, $page, $perPage);
    }

    public function obtenerPorId($id) {
        return $this->productoRepository->findById($id);
    }

    public function crear($datos) {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if (!empty($datos['codigo']) && $this->productoRepository->existeCodigo($datos['codigo'])) {
            return ['success' => false, 'errors' => ['El código ya está registrado']];
        }

        $datosInsert = [
            'codigo' => $datos['codigo'] ?? null,
            'nombre' => $datos['nombre'],
            'categoria_id' => !empty($datos['categoria_id']) ? $datos['categoria_id'] : null,
            'unidad_id' => !empty($datos['unidad_id']) ? $datos['unidad_id'] : null,
            'precio_venta' => $datos['precio_venta'],
            'stock_actual' => $datos['stock_actual'] ?? 0,
            'stock_minimo' => $datos['stock_minimo'] ?? 0,
            'stock_ilimitado' => isset($datos['stock_ilimitado']) ? 1 : 0,
            'estado' => 'activo'
        ];

        $id = $this->productoRepository->create($datosInsert);

        if ($id) {
            logMessage("Producto creado: ID $id - {$datos['nombre']}", 'info');
            return ['success' => true, 'id' => $id];
        }

        return ['success' => false, 'errors' => ['Error al crear el producto']];
    }

    public function actualizar($id, $datos) {
        $producto = $this->productoRepository->findById($id);
        if (!$producto) {
            return ['success' => false, 'errors' => ['Producto no encontrado']];
        }

        $errores = $this->validarDatos($datos, $id);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if (!empty($datos['codigo']) && $this->productoRepository->existeCodigo($datos['codigo'], $id)) {
            return ['success' => false, 'errors' => ['El código ya está registrado en otro producto']];
        }

        $datosUpdate = [
            'codigo' => $datos['codigo'] ?? null,
            'nombre' => $datos['nombre'],
            'categoria_id' => !empty($datos['categoria_id']) ? $datos['categoria_id'] : null,
            'unidad_id' => !empty($datos['unidad_id']) ? $datos['unidad_id'] : null,
            'precio_venta' => $datos['precio_venta'],
            'stock_actual' => $datos['stock_actual'] ?? 0,
            'stock_minimo' => $datos['stock_minimo'] ?? 0,
            'stock_ilimitado' => isset($datos['stock_ilimitado']) ? 1 : 0,
            'estado' => $datos['estado']
        ];

        $resultado = $this->productoRepository->update($id, $datosUpdate);

        if ($resultado) {
            logMessage("Producto actualizado: ID $id", 'info');
            return ['success' => true];
        }

        return ['success' => false, 'errors' => ['Error al actualizar el producto']];
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $producto = $this->productoRepository->findById($id);

        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        if ($producto->estado === $nuevoEstado) {
            return ['success' => false, 'message' => 'El producto ya tiene ese estado'];
        }

        $resultado = $this->productoRepository->cambiarEstado($id, $nuevoEstado);

        if ($resultado) {
            logMessage("Estado cambiado para producto ID $id: {$producto->estado} -> $nuevoEstado", 'info');
            return ['success' => true, 'message' => 'Estado actualizado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al cambiar el estado'];
    }

    public function eliminar($id) {
        $producto = $this->productoRepository->findById($id);

        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        if ($this->productoRepository->tieneMovimientos($id)) {
            return [
                'success' => false, 
                'message' => 'No se puede eliminar un producto con movimientos de inventario. Inactívelo en su lugar.'
            ];
        }

        $resultado = $this->productoRepository->delete($id);

        if ($resultado) {
            logMessage("Producto eliminado: ID $id - {$producto->nombre}", 'info');
            return ['success' => true, 'message' => 'Producto eliminado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar el producto'];
    }

    public function obtenerActivos() {
        return $this->productoRepository->getActivos();
    }

    public function contarTotal($filters = []) {
        return $this->productoRepository->count($filters);
    }

    public function obtenerCategorias() {
        return $this->productoRepository->getAllCategorias();
    }

    public function obtenerUnidades() {
        return $this->productoRepository->getAllUnidades();
    }

    public function generarCodigoAutomatico() {
        $sql = "SELECT MAX(CAST(SUBSTRING(codigo, 2) AS UNSIGNED)) as ultimo FROM productos WHERE codigo LIKE 'P%'";
        $db = Database::getInstance();
        $result = $db->queryOne($sql);
        
        $ultimo = $result['ultimo'] ?? 0;
        $nuevo = $ultimo + 1;
        
        return 'P' . str_pad($nuevo, 4, '0', STR_PAD_LEFT);
    }

    private function validarDatos($datos, $excludeId = null) {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es obligatorio';
        } elseif (strlen($datos['nombre']) < 3) {
            $errores[] = 'El nombre debe tener al menos 3 caracteres';
        }

        if (empty($datos['precio_venta'])) {
            $errores[] = 'El precio de venta es obligatorio';
        } elseif (!is_numeric($datos['precio_venta']) || $datos['precio_venta'] < 0) {
            $errores[] = 'El precio de venta debe ser un número válido';
        }

        if (isset($datos['stock_actual']) && (!is_numeric($datos['stock_actual']) || $datos['stock_actual'] < 0)) {
            $errores[] = 'El stock actual debe ser un número válido';
        }

        if (isset($datos['stock_minimo']) && (!is_numeric($datos['stock_minimo']) || $datos['stock_minimo'] < 0)) {
            $errores[] = 'El stock mínimo debe ser un número válido';
        }

        return $errores;
    }
}