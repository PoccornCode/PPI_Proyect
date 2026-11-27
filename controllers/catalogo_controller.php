<?php
require_once __DIR__ . '/../dao/CatalogoDAO.php';

// lee y limpia lo que llega por la URL 
function obtenerFiltrosCatalogo(): array
{
    $ordenesValidos = ['pronto', 'reciente', 'precio_asc', 'precio_desc', 'pujas'];

    $orden = $_GET['orden'] ?? 'pronto';
    if (!in_array($orden, $ordenesValidos, true)) {
        $orden = 'pronto';
    }

    $q = trim((string)($_GET['q'] ?? ''));

    return [
        'categoria' => trim((string)($_GET['categoria'] ?? '')),
        'q'         => mb_substr($q, 0, 80),
        'estado'    => (($_GET['estado'] ?? '') === 'hoy') ? 'hoy' : '',
        'orden'     => $orden,
    ];
}

// devuelve las subastas de la pagina pedida y paginacion
function obtenerCatalogo(array $filtros, int $pagina): array
{
    $dao = new CatalogoDAO();
    $porPagina = CatalogoDAO::POR_PAGINA;

    $total = $dao->contar($filtros);
    $paginas = max(1, (int)ceil($total / $porPagina));
    $pagina = min(max(1, $pagina), $paginas);

    return [
        'items'   => $dao->listar($filtros, $porPagina, ($pagina - 1) * $porPagina),
        'total'   => $total,
        'pagina'  => $pagina,
        'paginas' => $paginas,
    ];
}
 
// detalle de subasta null si no existe o el usuario no puede verla
function obtenerDetalleSubasta(int $id, int $idUsuario = 0, int $idRol = 0): ?array
{
    $dao = new CatalogoDAO();
    $subasta = $dao->obtenerSubasta($id);
    if (!$subasta) {
        return null;
    }

    // pendientes, rechazadas y canceladas solo las ven el dueno, moderadores y admin
    $publica = in_array($subasta['estado'], ['activa', 'finalizada', 'sin_ofertas'], true);
    $esDueno = $idUsuario > 0 && $idUsuario === $subasta['id_vendedor'];
    $esPersonal = in_array($idRol, [1, 3], true);
    if (!$publica && !$esDueno && !$esPersonal) {
        return null;
    }

    $subasta['es_dueno'] = $esDueno;
    $subasta['imagenes'] = $dao->obtenerImagenes($subasta['id_producto']);

    return $subasta;
}

// endpoint JSON para refresco en vivo del catalogo
if (
    isset($_SERVER['SCRIPT_FILENAME'])
    && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])
    && ($_GET['opcion'] ?? '') === 'listar'
) {
    header('Content-Type: application/json; charset=utf-8');
    $filtros = obtenerFiltrosCatalogo();
    $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
    $catalogo = obtenerCatalogo($filtros, $paginaActual);
    echo json_encode([
        'estado'  => true,
        'total'   => $catalogo['total'],
        'pagina'  => $catalogo['pagina'],
        'paginas' => $catalogo['paginas'],
        'items'   => $catalogo['items'],
    ]);
    exit;
}
