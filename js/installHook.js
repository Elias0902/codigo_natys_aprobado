$.ajax({
    url: '/path/to/your/api',
    type: 'GET',
    success: function(response) {
        try {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            // ...procesar data normalmente...
        } catch (e) {
            alert("Error: la respuesta del servidor no es válida. Puede que el servidor esté devolviendo HTML en vez de JSON.");
            console.error("Respuesta recibida:", response);
        }
    },
    error: function(xhr, status, error) {
        alert("Error de conexión: " + error);
    }
});