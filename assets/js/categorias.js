document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('filtro-cat-q');
    const select = document.getElementById('filtro-cat-orden');
    const grid = document.getElementById('grid-categorias');
    const vacio = document.getElementById('categorias-vacio');
    if (!input || !select || !grid) return;

    const items = Array.from(grid.querySelectorAll('.cat-dir-item'));

    function normalizar(texto) {
        return (texto || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    }

    function aplicar() {
        const q = normalizar(input.value);
        const orden = select.value;

        const ordenados = items.slice().sort((a, b) => {
            const na = normalizar(a.dataset.nombre);
            const nb = normalizar(b.dataset.nombre);
            return orden === 'za' ? nb.localeCompare(na) : na.localeCompare(nb);
        });

        let visibles = 0;
        ordenados.forEach((el) => {
            const coincide = !q
                || normalizar(el.dataset.nombre).includes(q)
                || normalizar(el.dataset.desc).includes(q);
            el.classList.toggle('d-none', !coincide);
            if (coincide) visibles++;
            grid.appendChild(el);
        });

        if (vacio) {
            vacio.classList.toggle('d-none', visibles > 0);
        }
    }

    input.addEventListener('input', aplicar);
    select.addEventListener('change', aplicar);
});
