// Función para ver detalles del documento
function verDetallesDocumento(placa) {
    // Buscar los datos del documento en la tabla actual
    const filas = document.querySelectorAll('#tablaDocumentos tbody tr');
    let documentoEncontrado = null;
    
    filas.forEach(fila => {
        const placaFila = fila.querySelector('.placa-badge').textContent.trim();
        if (placaFila === placa) {
            documentoEncontrado = {
                placa: placaFila,
                soat: fila.cells[1].textContent.trim(),
                tecnomecanica: fila.cells[2].textContent.trim(),
                licencia: fila.cells[3].textContent.trim(),
                propietario: fila.cells[4].textContent.trim()
            };
        }
    });
    
    if (documentoEncontrado) {
        // Llenar los datos en el modal
        document.getElementById('verPlacaDocumento').innerHTML = 
            `<i class="bi bi-car-front me-2"></i><span class="placa-badge">${documentoEncontrado.placa}</span>`;
        document.getElementById('verPropietarioDocumento').innerHTML = 
            `<i class="bi bi-person me-2"></i><span>${documentoEncontrado.propietario}</span>`;
        
        // Actualizar estados de documentos
        actualizarEstadoDocumento('verEstadoSoat', documentoEncontrado.soat);
        actualizarEstadoDocumento('verEstadoTecno', documentoEncontrado.tecnomecanica);
        actualizarEstadoDocumento('verEstadoLicencia', documentoEncontrado.licencia);
        
        // Calcular resumen
        calcularResumenDocumentos(documentoEncontrado);
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('modalVerDocumento'));
        modal.show();
    }
}

// Función para actualizar el estado visual de un documento
function actualizarEstadoDocumento(elementId, estado) {
    const elemento = document.getElementById(elementId);
    let claseEstado = '';
    let textoEstado = estado;
    
    if (estado.toLowerCase().includes('vigente')) {
        claseEstado = 'status-vigente';
        textoEstado = 'Vigente';
    } else if (estado.toLowerCase().includes('vencer') || estado.toLowerCase().includes('próximo')) {
        claseEstado = 'status-proximo';
        textoEstado = 'Por vencer';
    } else if (estado.toLowerCase().includes('vencido')) {
        claseEstado = 'status-vencido';
        textoEstado = 'Vencido';
    }
    
    elemento.innerHTML = `<span class="${claseEstado}">${textoEstado}</span>`;
}

// Función para calcular el resumen de documentos
function calcularResumenDocumentos(documento) {
    let vigentes = 0;
    let porVencer = 0;
    let vencidos = 0;
    
    [documento.soat, documento.tecnomecanica, documento.licencia].forEach(estado => {
        if (estado.toLowerCase().includes('vigente')) {
            vigentes++;
        } else if (estado.toLowerCase().includes('vencer') || estado.toLowerCase().includes('próximo')) {
            porVencer++;
        } else if (estado.toLowerCase().includes('vencido')) {
            vencidos++;
        }
    });
    
    document.getElementById('verDocumentosVigentes').textContent = vigentes;
    document.getElementById('verDocumentosPorVencer').textContent = porVencer;
    document.getElementById('verDocumentosVencidos').textContent = vencidos;
}

// Función para descargar documento - ELIMINADA (no funcional)
// ELIMINAR o corregir estas funciones incompletas:
function descargarDocumento(tipo, placa) {
    // Implementar descarga de documento
    console.log(`Descargando ${tipo} para vehículo ${placa}`);
    // Aquí puedes agregar la lógica para descargar el documenSSto
    alert(`Funcionalidad de descarga para ${tipo} del vehículo ${placa} - Por implementar`);
}

function editarDocumentos() {
    const placa = document.getElementById('verPlacaDocumento').textContent.trim();
    // Implementar edición de documentos
    console.log(`Editando documentos para vehículo ${placa}`);
    alert(`Funcionalidad de edición para vehículo ${placa} - Por implementar`);
}