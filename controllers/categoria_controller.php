<?php
require_once __DIR__ . '/../dao/CategoriaDAO.php';

function obtenerCategoriasCatalogo(): array
{
    $dao = new CategoriaDAO();
    return $dao->listarCategorias();
}

function obtenerCategoriasPopularesNav(int $limite = 6): array
{
    $dao = new CategoriaDAO();
    return $dao->listarPopulares($limite);
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header("Content-Type: application/json; charset=UTF-8");
    $opcion = $_GET['opcion'] ?? $_POST['opcion'] ?? 'listar';

    if ($opcion === 'populares') {
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 6;
        echo json_encode([
            'estado'     => true,
            'categorias' => obtenerCategoriasPopularesNav($limite)
        ]);
        exit;
    }

    echo json_encode([
        'estado'     => true,
        'categorias' => obtenerCategoriasCatalogo()
    ]);
    exit;
}
