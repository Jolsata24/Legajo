<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bell = document.getElementById('notification-bell');
        const dropdownList = document.getElementById('notification-dropdown-list');
        
        // Función para actualizar TODO (Badge + Lista)
        function refreshNotifications() {
            if (!bell) return; // Si no hay campana en esta página, salir

            fetch('../php/get_notificaciones.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) return;

                    // A. ACTUALIZAR EL CONTADOR ROJO (BADGE)
                    let badge = bell.querySelector('.notification-count');
                    
                    if (data.unread_total > 0) {
                        // Si no existe el badge, lo creamos
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'notification-count';
                            bell.appendChild(badge);
                        }
                        badge.textContent = data.unread_total;
                        badge.style.display = 'inline-block';
                    } else {
                        // Si es 0, lo ocultamos
                        if (badge) badge.style.display = 'none';
                    }

                    // B. ACTUALIZAR LA LISTA DESPLEGABLE (Si existe)
                    if (dropdownList) {
                        const dropdownBody = dropdownList.querySelector('.dropdown-body');
                        if (dropdownBody) {
                            dropdownBody.innerHTML = ''; // Limpiar

                            if (data.list.length === 0) {
                                dropdownBody.innerHTML = '<p style="padding: 15px; color:#777; text-align:center;">Sin notificaciones</p>';
                            } else {
                                data.list.forEach(notif => {
                                    const link = document.createElement('a');
                                    // Al hacer clic, va a marcar_leido.php
                                    link.href = `../php/marcar_leido.php?id=${notif.id}`;
                                    
                                    // Estilo para no leídas
                                    if (notif.leido == 0) {
                                        link.style.backgroundColor = '#f0f8ff';
                                        link.style.borderLeft = '3px solid #0d6efd';
                                    }

                                    // Fecha amigable
                                    const date = new Date(notif.fecha_creacion);
                                    const fechaStr = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                                    link.innerHTML = `
                                        <p style="margin:0; font-size:13px; color:#333;">${notif.mensaje}</p>
                                        <small style="color:#888; font-size:11px;">${fechaStr}</small>
                                    `;
                                    dropdownBody.appendChild(link);
                                });
                            }
                        }
                    }
                })
                .catch(err => console.error('Error notificaciones:', err));
        }

        // 1. Cargar notificaciones al iniciar la página (Corrige el número al volver de otra página)
        refreshNotifications();

        // 2. Eventos de la Campana
        if (bell && dropdownList) {
            bell.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropdownList.classList.toggle('show');
                
                // Si abrimos, refrescamos por si acaso llegaron nuevas
                if (dropdownList.classList.contains('show')) {
                    refreshNotifications();
                }
            });

            // Cerrar al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!bell.contains(e.target) && !dropdownList.contains(e.target)) {
                    dropdownList.classList.remove('show');
                }
            });
        }
    });
    </script>
</body>
</html>