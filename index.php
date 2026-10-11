
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>ValidaGT Pro | Panel Administrativo</title>

    <meta name="description"
          content="Sistema de prácticas de validaciones y restricciones SQL">

    <link rel="stylesheet" href="assets/style.css">

    <script src="assets/app.js" defer></script>
</head>

<body>

<div class="app-shell">

    <!-- MENÚ LATERAL -->
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">✓</div>

            <div>
                <h2>ValidaGT</h2>
                <span>PRO · SISTEMA WEB</span>
            </div>
        </div>

        <p class="menu-label">MENÚ PRINCIPAL</p>

        <nav class="nav-menu">
            <a href="#inicio" class="nav-item active">
                <span>◫</span> Panel general
            </a>

            <a href="#registro" class="nav-item">
                <span>＋</span> Nuevo registro
            </a>

            <a href="#historial" class="nav-item">
                <span>▤</span> Historial
            </a>

            <a href="#restricciones" class="nav-item">
                <span>◇</span> Restricciones SQL
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="version-box">
                <strong>ValidaGT Pro v1.0</strong>
                <p>PHP · MySQL · JavaScript</p>
            </div>
        </div>

    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">

        <header class="topbar">
            <div>
                <p class="eyebrow">CENTRO DE CONTROL</p>
                <h1>Panel administrativo</h1>
                <p class="muted">
                    Validación, registros y estadísticas.
                </p>
            </div>

            <div class="top-actions">
                <span id="estadoConexion" class="connection">
                    ● Comprobando conexión...
                </span>

                <button id="btnTema" class="btn btn-outline"
                        type="button">
                    ☀ Modo claro
                </button>
            </div>
        </header>

        <!-- PANEL DE ESTADÍSTICAS -->
        <section id="inicio">

            <div class="section-title">
                <div>
                    <h2>Resumen general</h2>
                    <p>Datos obtenidos desde MySQL</p>
                </div>

                <button id="btnActualizar"
                        type="button"
                        class="btn btn-outline">
                    ↻ Actualizar
                </button>
            </div>

            <div class="stats-grid">

                <article class="stat-card">
                    <div class="stat-top">
                        <span>Registros guardados</span>
                        <div class="stat-icon blue">▤</div>
                    </div>

                    <strong id="statRegistros">0</strong>
                    <p>Personas almacenadas</p>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span>Intentos aprobados</span>
                        <div class="stat-icon green">✓</div>
                    </div>

                    <strong id="statAprobados">0</strong>
                    <p>Validaciones aceptadas</p>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span>Intentos rechazados</span>
                        <div class="stat-icon red">!</div>
                    </div>

                    <strong id="statRechazados">0</strong>
                    <p>Validaciones incorrectas</p>
                </article>

                <article class="stat-card">
                    <div class="stat-top">
                        <span>Tasa de aprobación</span>
                        <div class="stat-icon purple">%</div>
                    </div>

                    <strong id="statPorcentaje">0%</strong>
                    <p>Intentos aceptados / intentos totales</p>
                </article>

            </div>

            <!-- GRÁFICA Y ACTIVIDAD -->
            <div class="dashboard-grid">

                <article class="panel">

                    <div class="panel-header">
                        <div>
                            <h3>Resultados de validaciones</h3>
                            <p>Distribución de los intentos</p>
                        </div>
                        <span class="pill">Análisis</span>
                    </div>

                    <div class="chart-content">

                        <div id="grafica" class="donut vacio">
                            <div class="donut-center">
                                <strong id="graficaPorcentaje">0%</strong>
                                <small>Aprobación</small>
                            </div>
                        </div>

                        <div class="chart-legend">

                            <div class="legend-row">
                                <span>
                                    <i class="dot success-dot"></i>
                                    Aprobados
                                </span>
                                <strong id="leyendaAprobados">0</strong>
                            </div>

                            <div class="legend-row">
                                <span>
                                    <i class="dot danger-dot"></i>
                                    Rechazados
                                </span>
                                <strong id="leyendaRechazados">0</strong>
                            </div>

                            <div class="legend-row">
                                <span>Total de intentos</span>
                                <strong id="leyendaTotal">0</strong>
                            </div>

                        </div>

                    </div>

                    <div class="substats">

                        <div>
                            <span>Errores de formato</span>
                            <strong id="erroresFormato">0</strong>
                        </div>

                        <div>
                            <span>Registros duplicados</span>
                            <strong id="erroresDuplicados">0</strong>
                        </div>

                    </div>

                </article>

                <article class="panel">

                    <div class="panel-header">
                        <div>
                            <h3>Actividad reciente</h3>
                            <p>Últimos intentos procesados</p>
                        </div>
                        <span class="pill">Historial</span>
                    </div>

                    <ul id="listaActividad" class="activity-list">
                        <li>Cargando actividad...</li>
                    </ul>

                </article>

            </div>

        </section>

        <!-- FORMULARIO -->
        <section id="registro" class="panel section-panel">

            <div class="section-title">
                <div>
                    <h2>Registrar nueva persona</h2>
                    <p>
                        Comprueba los formatos y guarda
                        la información en MySQL.
                    </p>
                </div>

                <span class="pill">Formulario RegEx</span>
            </div>

            <div class="info-note">
                Este sistema es una demostración.
                Utiliza solamente datos ficticios.
                El DPI se comprueba por formato, no por autenticidad.
            </div>

            <div id="alerta" class="alerta" role="alert"
                 aria-live="polite"></div>

            <form id="formRegistro" novalidate>

                <div class="form-grid">

                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>

                        <input id="nombre" name="nombre"
                               type="text"
                               placeholder="Ej. Persona de prueba"
                               maxlength="100"
                               autocomplete="off">

                        <small id="msgNombre"></small>
                    </div>

                    <div class="form-group">
                        <label for="edad">Edad</label>

                        <input id="edad" name="edad"
                               type="number"
                               min="1" max="120"
                               placeholder="Ej. 20">

                        <small id="msgEdad"></small>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>

                        <input id="telefono" name="telefono"
                               type="text"
                               maxlength="8"
                               inputmode="numeric"
                               placeholder="8 dígitos">

                        <small id="msgTelefono"></small>
                    </div>

                    <div class="form-group">
                        <label for="dpi">DPI / CUI</label>

                        <input id="dpi" name="dpi"
                               type="text"
                               maxlength="13"
                               inputmode="numeric"
                               placeholder="13 dígitos">

                        <small id="msgDpi"></small>
                    </div>

                    <div class="form-group full">
                        <label for="correo">Correo electrónico</label>

                        <input id="correo" name="correo"
                               type="email"
                               maxlength="150"
                               placeholder="prueba@example.com"
                               autocomplete="off">

                        <small id="msgCorreo"></small>
                    </div>

                </div>

                <div class="form-footer">
                    <span class="muted">
                        Todos los campos son obligatorios.
                    </span>

                    <div class="form-buttons">
                        <button type="reset" class="btn btn-outline">
                            Limpiar
                        </button>

                        <button type="submit"
                                id="btnGuardar"
                                class="btn btn-primary">
                            ＋ Guardar registro
                        </button>
                    </div>
                </div>

            </form>
        </section>

        <!-- HISTORIAL -->
        <section id="historial" class="panel section-panel">

            <div class="section-title">
                <div>
                    <h2>Historial de registros</h2>
                    <p>
                        Últimos 50 registros almacenados.
                        Los datos sensibles se ocultan.
                    </p>
                </div>

                <input id="buscar"
                       type="search"
                       class="search-input"
                       placeholder="Buscar por nombre...">
            </div>

            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Edad</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>

                    <tbody id="tablaRegistros">
                        <tr>
                            <td colspan="7">
                                Cargando registros...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>

        <!-- RESTRICCIONES DE BASE DE DATOS -->
        <section id="restricciones" class="panel section-panel">

            <div class="section-title">
                <div>
                    <h2>Restricciones de base de datos</h2>
                    <p>Reglas de integridad implementadas en SQL</p>
                </div>
            </div>

            <div class="rules-grid">

                <article class="rule-card">
                    <span class="rule-label">NOT NULL</span>
                    <h3>Campos obligatorios</h3>
                    <p>
                        Evita que los campos requeridos
                        almacenen valores NULL.
                    </p>
                </article>

                <article class="rule-card">
                    <span class="rule-label">UNIQUE</span>
                    <h3>Registros únicos</h3>
                    <p>
                        Impide que dos personas utilicen
                        el mismo DPI o correo.
                    </p>
                </article>

                <article class="rule-card">
                    <span class="rule-label">CHECK</span>
                    <h3>Rangos permitidos</h3>
                    <p>
                        Comprueba que la edad esté dentro
                        del rango establecido de 1 a 120.
                    </p>
                </article>

            </div>

        </section>

        <footer>
            ValidaGT Pro · PHP · MySQL · HTML · CSS · JavaScript
            <p>Demostración de validaciones y restricciones SQL</p>
        </footer>

    </main>

</div>

</body>
</html>
