<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('notification-bell');
    const dropdownList = document.getElementById('notification-dropdown-list');
    const dropdownBody = dropdownList.querySelector('.dropdown-body');

    if (!bell) return;

    // 1. Mostrar/Ocultar el menú al hacer clic en la campana
    bell.addEventListener('click', function(e) {
        e.preventDefault();
        dropdownList.classList.toggle('show');
        
        // Si acabamos de abrir el menú, cargamos las notificaciones
        if (dropdownList.classList.contains('show')) {
            loadNotifications();
        }
    });

    // 2. Función para cargar las notificaciones desde el API
    function loadNotifications() {
        fetch('../php/get_notificaciones.php')
            .then(response => response.json())
            .then(data => {
                dropdownBody.innerHTML = ''; // Limpiar lista anterior

                if (data.error) {
                    dropdownBody.innerHTML = '<p style="padding: 15px;">Error al cargar.</p>';
                    return;
                }

                if (data.length === 0) {
                    dropdownBody.innerHTML = '<p style="padding: 15px;">No hay notificaciones nuevas.</p>';
                    return;
                }

                data.forEach(notif => {
                    const link = document.createElement('a');
                    // Usamos el script que ya tienes: marcar_leido.php
                    link.href = `../php/marcar_leido.php?id=${notif.id}`; 
                    
                    // Si no está leída, la resaltamos
                    if (notif.leido == 0) { 
                        link.style.fontWeight = 'bold';
                        link.style.background = '#f0f8ff';
                    }

                    // Formatear la fecha (simple)
                    const date = new Date(notif.fecha_creacion);
                    const timeAgo = date.toLocaleString('es-ES', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });

                    link.innerHTML = `
                        <p>${notif.mensaje}</p>
                        <small>${timeAgo}</small>
                    `;
                    dropdownBody.appendChild(link);
                });
            })
            .catch(error => {
                dropdownBody.innerHTML = '<p style="padding: 15px;">Error de conexión.</p>';
            });
    }

    // 3. Cerrar el menú si se hace clic fuera de él
    window.addEventListener('click', function(e) {
        if (!bell.contains(e.target) && !dropdownList.contains(e.target)) {
            dropdownList.classList.remove('show');
        }
    });
});
</script>
    </body>
</html>