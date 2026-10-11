
<?php

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

// ========================================
// RESPUESTAS JSON
// ========================================

function responder($datos, $codigo = 200) {
    http_response_code($codigo);

    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// ========================================
// CONEXIÓN CON BASE DE DATOS
// ========================================

$archivoConfig = __DIR__ . "/config/db.php";

if (!is_file($archivoConfig)) {
    responder([
        "ok" => false,
        "mensaje" => "Falta configurar la conexión."
    ], 500);
}

$config = require $archivoConfig;

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
        $config["user"],
        $config["password"],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $error) {
    responder([
        "ok" => false,
        "mensaje" => "No se pudo conectar a MySQL."
    ], 500);
}

// ========================================
// GUARDAR RESULTADO DE UN INTENTO
// ========================================

function guardarIntento($pdo, $resultado, $motivo) {
    $sql = "INSERT INTO intentos (resultado, motivo)
            VALUES (?, ?)";

    $consulta = $pdo->prepare($sql);

    $consulta->execute([
        $resultado,
        $motivo
    ]);
}

// ========================================
// CONSULTAR REGISTROS Y ESTADÍSTICAS
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    try {
        // Evitamos enviar DPI o correos completos
        // a quienes consulten el historial público.

        $sql = "
            SELECT
                id,
                nombre,
                edad,
                CONCAT('****', RIGHT(telefono, 4)) AS telefono,
                CONCAT(LEFT(correo, 1), '***@***') AS correo,
                fecha_registro
            FROM personas
            ORDER BY id DESC
            LIMIT 50
        ";

        $registros = $pdo->query($sql)->fetchAll();

        $total = (int) $pdo->query(
            "SELECT COUNT(*) FROM personas"
        )->fetchColumn();

        $sqlEstadisticas = "
            SELECT
                COUNT(*) AS intentos,
                COALESCE(
                    SUM(CASE WHEN resultado = 'APROBADO'
                        THEN 1 ELSE 0 END), 0
                ) AS aprobados,
                COALESCE(
                    SUM(CASE WHEN resultado = 'RECHAZADO'
                        THEN 1 ELSE 0 END), 0
                ) AS rechazados,
                COALESCE(
                    SUM(CASE WHEN motivo = 'FORMATO'
                        THEN 1 ELSE 0 END), 0
                ) AS errores_formato,
                COALESCE(
                    SUM(CASE WHEN motivo = 'DUPLICADO'
                        THEN 1 ELSE 0 END), 0
                ) AS duplicados
            FROM intentos
        ";

        $datos = $pdo->query($sqlEstadisticas)->fetch();

        $intentos = (int) $datos["intentos"];
        $aprobados = (int) $datos["aprobados"];

        $porcentaje = $intentos > 0
            ? round(($aprobados / $intentos) * 100, 1)
            : 0;

        $actividad = $pdo->query("
            SELECT resultado, motivo, fecha
            FROM intentos
            ORDER BY id DESC
            LIMIT 6
        ")->fetchAll();

        responder([
            "ok" => true,

            "estadisticas" => [
                "total" => $total,
                "intentos" => $intentos,
                "aprobados" => $aprobados,
                "rechazados" => (int) $datos["rechazados"],
                "errores_formato" => (int) $datos["errores_formato"],
                "duplicados" => (int) $datos["duplicados"],
                "porcentaje" => $porcentaje
            ],

            "registros" => $registros,
            "actividad" => $actividad
        ]);

    } catch (PDOException $error) {
        responder([
            "ok" => false,
            "mensaje" => "Error al consultar la información."
        ], 500);
    }
}

// ========================================
// GUARDAR UNA NUEVA PERSONA
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $entrada = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($entrada)) {
        responder([
            "ok" => false,
            "mensaje" => "Los datos recibidos no son válidos."
        ], 400);
    }

    // Obtener únicamente valores de texto.

    $nombre = isset($entrada["nombre"]) &&
              is_string($entrada["nombre"])
        ? trim($entrada["nombre"]) : "";

    $edad = isset($entrada["edad"]) &&
            (is_string($entrada["edad"]) ||
             is_int($entrada["edad"]))
        ? trim((string)$entrada["edad"]) : "";

    $telefono = isset($entrada["telefono"]) &&
                is_string($entrada["telefono"])
        ? trim($entrada["telefono"]) : "";

    $dpi = isset($entrada["dpi"]) &&
           is_string($entrada["dpi"])
        ? trim($entrada["dpi"]) : "";

    $correo = isset($entrada["correo"]) &&
              is_string($entrada["correo"])
        ? trim($entrada["correo"]) : "";

    // ====================================
    // VALIDACIONES DEL SERVIDOR
    // ====================================

    $errores = [];

    // Nombre de 3 a 100 caracteres
    if (!preg_match('/^.{3,100}$/us', $nombre)) {
        $errores[] = "El nombre debe tener entre 3 y 100 caracteres.";
    }

    // Edad de 1 a 120
    if (
        !preg_match('/^\d+$/', $edad) ||
        (int)$edad < 1 ||
        (int)$edad > 120
    ) {
        $errores[] = "La edad debe estar entre 1 y 120.";
    }

    // REGEX 1: TELÉFONO
    if (!preg_match('/^[234567]\d{7}$/', $telefono)) {
        $errores[] = "El teléfono debe tener 8 dígitos y empezar del 2 al 7.";
    }

    // REGEX 2: DPI
    if (!preg_match('/^\d{13}$/', $dpi)) {
        $errores[] = "El DPI debe tener exactamente 13 dígitos.";
    }

    // REGEX 3: CORREO
    if (
        strlen($correo) > 150 ||
        !preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $correo) ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {
        $errores[] = "El correo electrónico no es válido.";
    }

    // No guardar datos incorrectos
    if (count($errores) > 0) {

        try {
            guardarIntento($pdo, "RECHAZADO", "FORMATO");
        } catch (PDOException $error) {
            responder([
                "ok" => false,
                "mensaje" => "No se pudo registrar el intento."
            ], 500);
        }

        responder([
            "ok" => false,
            "mensaje" => implode(" ", $errores)
        ], 422);
    }

    // ====================================
    // REGISTRO EN MYSQL
    // ====================================

    try {
        $pdo->beginTransaction();

        $sql = "
            INSERT INTO personas
            (nombre, edad, telefono, dpi, correo)
            VALUES (?, ?, ?, ?, ?)
        ";

        $consulta = $pdo->prepare($sql);

        $consulta->execute([
            $nombre,
            (int)$edad,
            $telefono,
            $dpi,
            $correo
        ]);

        guardarIntento(
            $pdo,
            "APROBADO",
            "NINGUNO"
        );

        $pdo->commit();

        responder([
            "ok" => true,
            "mensaje" => "Registro guardado correctamente."
        ], 201);

    } catch (PDOException $error) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Error 23000: por ejemplo, dato UNIQUE duplicado
        if ($error->getCode() === "23000") {

            try {
                guardarIntento(
                    $pdo,
                    "RECHAZADO",
                    "DUPLICADO"
                );
            } catch (PDOException $otroError) {
                responder([
                    "ok" => false,
                    "mensaje" => "No se pudo registrar el intento."
                ], 500);
            }

            responder([
                "ok" => false,
                "mensaje" => "El DPI o el correo ya existe en la base de datos."
            ], 409);
        }

        responder([
            "ok" => false,
            "mensaje" => "Ocurrió un error al guardar."
        ], 500);
    }
}

// Otros métodos HTTP no permitidos
header("Allow: GET, POST");

responder([
    "ok" => false,
    "mensaje" => "Método no permitido."
], 405);
