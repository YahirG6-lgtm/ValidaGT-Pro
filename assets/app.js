
// =====================================
// VALIDAGT PRO - JAVASCRIPT
// =====================================

const API = "api.php";

const obtener = id => document.getElementById(id);

const formulario = obtener("formRegistro");
const alerta = obtener("alerta");
const btnGuardar = obtener("btnGuardar");
const buscar = obtener("buscar");

let registros = [];

// =====================================
// EXPRESIONES REGULARES
// =====================================

// Teléfono de Guatemala: 8 números.
// Comienza entre 2 y 7.
const regexTelefono = /^[234567]\d{7}$/;

// DPI: exactamente 13 dígitos.
const regexDpi = /^\d{13}$/;

// Correo electrónico: formato básico.
const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// =====================================
// REGLAS DEL FORMULARIO
// =====================================

const reglas = {
    nombre: {
        validar: valor =>
            [...valor].length >= 3 &&
            [...valor].length <= 100,

        mensaje: "Escribe un nombre entre 3 y 100 caracteres."
    },

    edad: {
        validar: valor =>
            /^\d+$/.test(valor) &&
            Number(valor) >= 1 &&
            Number(valor) <= 120,

        mensaje: "Ingresa una edad entre 1 y 120."
    },

    telefono: {
        validar: valor => regexTelefono.test(valor),

        mensaje: "Debe contener 8 dígitos y comenzar del 2 al 7."
    },

    dpi: {
        validar: valor => regexDpi.test(valor),

        mensaje: "El DPI debe tener exactamente 13 números."
    },

    correo: {
        validar: valor =>
            valor.length <= 150 &&
            regexCorreo.test(valor),

        mensaje: "Ingresa un correo con formato válido."
    }
};

const campos = Object.keys(reglas);

// =====================================
// VALIDACIÓN EN TIEMPO REAL
// =====================================

function validarCampo(nombreCampo) {

    const input = obtener(nombreCampo);
    const mensaje = obtener(
        "msg" + nombreCampo.charAt(0).toUpperCase() +
        nombreCampo.slice(1)
    );

    const valor = input.value.trim();
    const valido = reglas[nombreCampo].validar(valor);

    input.classList.remove("valido", "invalido");
    mensaje.classList.remove("valido", "invalido");

    if (valor === "") {
        input.classList.add("invalido");
        mensaje.classList.add("invalido");
        mensaje.textContent = "Este campo es obligatorio.";
        return false;
    }

    if (valido) {
        input.classList.add("valido");
        mensaje.classList.add("valido");
        mensaje.textContent = "✓ Formato correcto";
    } else {
        input.classList.add("invalido");
        mensaje.classList.add("invalido");
        mensaje.textContent = reglas[nombreCampo].mensaje;
    }

    return valido;
}

// Activar validación mientras escribe
campos.forEach(campo => {

    obtener(campo).addEventListener("input", () => {
        validarCampo(campo);
    });

});

// =====================================
// MOSTRAR MENSAJES
// =====================================

function mostrarAlerta(texto, tipo) {
    alerta.textContent = texto;
    alerta.className = "alerta " + tipo;
}

// =====================================
// ENVIAR FORMULARIO A PHP
// =====================================

formulario.addEventListener("submit", async evento => {

    evento.preventDefault();

    // Comprobar todos los campos visualmente.
    // Enviamos también los intentos incorrectos
    // para que PHP pueda contarlos como rechazados.

    campos.forEach(campo => validarCampo(campo));

    const datos = {
        nombre: obtener("nombre").value.trim(),
        edad: obtener("edad").value.trim(),
        telefono: obtener("telefono").value.trim(),
        dpi: obtener("dpi").value.trim(),
        correo: obtener("correo").value.trim()
    };

    btnGuardar.disabled = true;
    btnGuardar.textContent = "Procesando...";

    try {

        const respuesta = await fetch(API, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(datos)
        });

        const tipo = respuesta.headers.get("content-type") || "";

        if (!tipo.includes("application/json")) {
            throw new Error(
                "El servidor devolvió una respuesta inesperada. " +
                "Comprueba Apache y la configuración del hosting."
            );
        }

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.ok) {
            throw new Error(
                resultado.mensaje || "No se pudo guardar."
            );
        }

        mostrarAlerta(resultado.mensaje, "exito");

        formulario.reset();

    } catch (error) {

        mostrarAlerta(error.message, "error");

    } finally {

        btnGuardar.disabled = false;
        btnGuardar.textContent = "＋ Guardar registro";

        await cargarDatos();
    }
});

// =====================================
// LIMPIAR FORMULARIO
// =====================================

formulario.addEventListener("reset", () => {

    campos.forEach(campo => {
        const input = obtener(campo);

        const mensaje = obtener(
            "msg" + campo.charAt(0).toUpperCase() +
            campo.slice(1)
        );

        input.classList.remove("valido", "invalido");
        mensaje.classList.remove("valido", "invalido");
        mensaje.textContent = "";
    });
});

// =====================================
// CONSULTAR DATOS DESDE MYSQL
// =====================================

async function cargarDatos() {

    try {

        const respuesta = await fetch(API, {
            cache: "no-store"
        });

        const tipo = respuesta.headers.get("content-type") || "";

        if (!tipo.includes("application/json")) {
            throw new Error(
                "La API no devolvió JSON. " +
                "Revisa la conexión con PHP."
            );
        }

        const datos = await respuesta.json();

        if (!respuesta.ok || !datos.ok) {
            throw new Error(
                datos.mensaje || "No fue posible cargar los datos."
            );
        }

        // Actualizar estado de la conexión
        const estado = obtener("estadoConexion");

        estado.textContent = "● Base de datos conectada";
        estado.className = "connection online";

        // Actualizar estadísticas
        const e = datos.estadisticas;

        obtener("statRegistros").textContent = e.total;
        obtener("statAprobados").textContent = e.aprobados;
        obtener("statRechazados").textContent = e.rechazados;
        obtener("statPorcentaje").textContent = e.porcentaje + "%";

        obtener("erroresFormato").textContent = e.errores_formato;
        obtener("erroresDuplicados").textContent = e.duplicados;

        // Actualizar gráfica circular
        actualizarGrafica(e);

        // Actualizar historial
        registros = datos.registros;
        mostrarRegistros();

        // Actualizar actividad reciente
        mostrarActividad(datos.actividad);

    } catch (error) {

        const estado = obtener("estadoConexion");

        estado.textContent = "● Sin conexión";
        estado.className = "connection offline";

        console.error(error);
        mostrarAlerta(error.message, "error");
    }
}

// =====================================
// GRÁFICA CIRCULAR
// =====================================

function actualizarGrafica(e) {

    const aprobados = Number(e.aprobados);
    const rechazados = Number(e.rechazados);
    const total = aprobados + rechazados;

    const porcentaje = total > 0
        ? (aprobados / total) * 100
        : 0;

    const grafica = obtener("grafica");

    grafica.style.setProperty(
        "--avance",
        porcentaje + "%"
    );

    grafica.classList.toggle("vacio", total === 0);

    obtener("graficaPorcentaje").textContent =
        e.porcentaje + "%";

    obtener("leyendaAprobados").textContent = aprobados;
    obtener("leyendaRechazados").textContent = rechazados;
    obtener("leyendaTotal").textContent = total;
}

// =====================================
// HISTORIAL Y BÚSQUEDA
// =====================================

function mostrarRegistros() {

    const tabla = obtener("tablaRegistros");

    tabla.replaceChildren();

    const texto = buscar.value.trim().toLowerCase();

    const filtrados = registros.filter(persona =>
        persona.nombre.toLowerCase().includes(texto)
    );

    if (filtrados.length === 0) {

        const fila = document.createElement("tr");
        const celda = document.createElement("td");

        celda.colSpan = 7;
        celda.textContent = "No se encontraron registros.";

        fila.appendChild(celda);
        tabla.appendChild(fila);
        return;
    }

    filtrados.forEach(persona => {

        const fila = document.createElement("tr");

        const valores = [
            persona.id,
            persona.nombre,
            persona.edad,
            persona.telefono,
            persona.correo
        ];

        valores.forEach(valor => {

            const celda = document.createElement("td");
            celda.textContent = valor;

            fila.appendChild(celda);
        });

        // Columna estado
        const tdEstado = document.createElement("td");
        const estado = document.createElement("span");

        estado.className = "table-status";
        estado.textContent = "✓ Validado";

        tdEstado.appendChild(estado);
        fila.appendChild(tdEstado);

        // Columna fecha
        const tdFecha = document.createElement("td");

        tdFecha.textContent = persona.fecha_registro;

        fila.appendChild(tdFecha);
        tabla.appendChild(fila);
    });
}

buscar.addEventListener("input", mostrarRegistros);

// =====================================
// ACTIVIDAD RECIENTE
// =====================================

function mostrarActividad(actividad) {

    const lista = obtener("listaActividad");

    lista.replaceChildren();

    if (actividad.length === 0) {

        const elemento = document.createElement("li");

        elemento.textContent =
            "Todavía no existen intentos registrados.";

        lista.appendChild(elemento);
        return;
    }

    actividad.forEach(item => {

        const aprobado = item.resultado === "APROBADO";

        const elemento = document.createElement("li");
        elemento.className = "activity-item";

        const icono = document.createElement("span");

        icono.className = aprobado
            ? "activity-icon ok"
            : "activity-icon fail";

        icono.textContent = aprobado ? "✓" : "!";

        const contenido = document.createElement("div");

        const titulo = document.createElement("strong");

        titulo.textContent = aprobado
            ? "Registro aprobado"
            : "Registro rechazado";

        const detalle = document.createElement("p");

        let motivo = "Validación correcta";

        if (item.motivo === "FORMATO") {
            motivo = "Error de formato";
        }

        if (item.motivo === "DUPLICADO") {
            motivo = "Dato duplicado";
        }

        detalle.textContent = motivo + " · " + item.fecha;

        contenido.appendChild(titulo);
        contenido.appendChild(detalle);

        elemento.appendChild(icono);
        elemento.appendChild(contenido);

        lista.appendChild(elemento);
    });
}

// =====================================
// CAMBIAR MODO OSCURO / CLARO
// =====================================

const btnTema = obtener("btnTema");

function actualizarTextoTema() {

    const claro = document.body.classList.contains("light");

    btnTema.textContent = claro
        ? "☾ Modo oscuro"
        : "☀ Modo claro";
}

if (localStorage.getItem("validagt-tema") === "claro") {
    document.body.classList.add("light");
}

actualizarTextoTema();

btnTema.addEventListener("click", () => {

    document.body.classList.toggle("light");

    const claro = document.body.classList.contains("light");

    localStorage.setItem(
        "validagt-tema",
        claro ? "claro" : "oscuro"
    );

    actualizarTextoTema();
});

// =====================================
// NAVEGACIÓN
// =====================================

document.querySelectorAll(".nav-item").forEach(enlace => {

    enlace.addEventListener("click", () => {

        document.querySelectorAll(".nav-item").forEach(item => {
            item.classList.remove("active");
        });

        enlace.classList.add("active");
    });
});

// =====================================
// ACTUALIZAR DATOS MANUALMENTE
// =====================================

obtener("btnActualizar").addEventListener(
    "click",
    cargarDatos
);

// =====================================
// INICIAR SISTEMA
// =====================================

cargarDatos();
