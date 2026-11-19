<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/env.php';
}

$site_name = "Gavel.net";
$page_title = isset($titulo) && !empty($titulo) ? "{$titulo} | {$site_name}" : "Gavel.net | Subastas en Línea El Salvador";
$page_desc = isset($meta_descripcion) && !empty($meta_descripcion) 
    ? $meta_descripcion 
    : "Gavel.net es la plataforma de subastas en línea líder en El Salvador. Compra, vende y puja en tiempo real por coleccionables, arte, tecnología y más.";

$page_url = isset($canonical_url) && !empty($canonical_url) ? $canonical_url : (BASE_URL . ltrim($_SERVER['REQUEST_URI'] ?? '', '/'));
$og_img = isset($og_imagen) && !empty($og_imagen) ? $og_imagen : BASE_URL . "assets/img/newsletter-1.jpg";
$og_type = isset($meta_tipo) && !empty($meta_tipo) ? $meta_tipo : "website";
?>
<meta name="description" content="<?= htmlspecialchars($page_desc, ENT_QUOTES, 'UTF-8') ?>">
<meta name="author" content="Gavel.net">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= htmlspecialchars($page_url, ENT_QUOTES, 'UTF-8') ?>">

<link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/img/favicon.svg">
<link rel="alternate icon" type="image/x-icon" href="<?= BASE_URL ?>assets/img/favicon.svg">
<link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/img/favicon.svg">

<meta property="og:locale" content="es_SV">
<meta property="og:site_name" content="<?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:type" content="<?= htmlspecialchars($og_type, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($page_desc, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:url" content="<?= htmlspecialchars($page_url, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image" content="<?= htmlspecialchars($og_img, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image:alt" content="<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($page_desc, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($og_img, ENT_QUOTES, 'UTF-8') ?>">
