<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    exit;

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/assets/JS/script.js');
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/assets/CSS/main.css');

?>