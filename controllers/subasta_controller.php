<?php
require_once __DIR__ . '/../dao/SubastaDAO.php';

function obtenerSubastaDestacadaHome(): ?array
{
    $dao = new SubastaDAO();
    return $dao->obtenerDestacada();
}

function obtenerSubastasTerminanProntoHome(int $limite = 4, ?int $excluirId = null): array
{
    $dao = new SubastaDAO();
    return $dao->obtenerTerminanPronto($limite, $excluirId);
}

function obtenerNuevasSubastasHome(int $limite = 4): array
{
    $dao = new SubastaDAO();
    return $dao->obtenerNuevas($limite);
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header("Content-Type: application/json; charset=UTF-8");
    $opcion = $_GET['opcion'] ?? 'home';

    if ($opcion === 'home') {
        $destacada = obtenerSubastaDestacadaHome();
        echo json_encode([
            'estado'    => true,
            'destacada' => $destacada,
            'pronto'    => obtenerSubastasTerminanProntoHome(5, $destacada['id'] ?? null),
            'nuevas'    => obtenerNuevasSubastasHome(4),
        ]);
        exit;
    }

    echo json_encode(['estado' => false, 'mensaje' => 'Opción no válida']);
    exit;
}
