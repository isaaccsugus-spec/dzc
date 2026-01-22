</div> <footer class="text-center py-4 mt-auto border-top bg-light">
    <div class="container">
        <p class="mb-0 text-muted">
            &copy; <?php echo date('Y'); ?> Gestor de Contenidos - Tarea 8
        </p>
        <small class="text-secondary opacity-75">ISAAC SANCHEZ GARCIA</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        if ($('#contenido').length) {
            $('#contenido').summernote({
                placeholder: 'Escribe aquí el contenido...',
                tabsize: 2, height: 250,
                toolbar: [
                  ['style', ['bold', 'italic', 'underline', 'clear']],
                  ['font', ['strikethrough', 'superscript', 'subscript']],
                  ['fontsize', ['fontsize']],
                  ['color', ['color']],
                  ['para', ['ul', 'ol', 'paragraph']],
                  ['height', ['height']],
      ['view', ['codeview']]
                ]
            });
        }
    });

    // --- LÓGICA DE MODO OSCURO (DARK MODE) ---
    const toggleButton = document.getElementById('darkModeToggle');
    const icon = document.getElementById('iconMode');
    const body = document.body;
    const footer = document.querySelector('footer');

    // 1. Al cargar la página, miramos si el usuario ya eligió modo oscuro
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        icon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
        if(footer) {
            footer.classList.add('bg-dark', 'text-white', 'border-secondary');
            footer.classList.remove('bg-light', 'text-muted');
        }
    }

    // 2. Al hacer clic en el botón
    toggleButton.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        
        if (body.classList.contains('dark-mode')) {
            // Activar Oscuro
            localStorage.setItem('theme', 'dark');
            icon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
            if(footer) {
                footer.classList.add('bg-dark', 'text-white', 'border-secondary');
                footer.classList.remove('bg-light', 'text-muted');
            }
        } else {
            // Activar Claro
            localStorage.setItem('theme', 'light');
            icon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
            if(footer) {
                footer.classList.add('bg-light', 'text-muted');
                footer.classList.remove('bg-dark', 'text-white', 'border-secondary');
            }
        }
    });
</script>

</body>
</html>