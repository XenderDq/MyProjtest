<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<?php
$dir = $APPLICATION->GetCurDir();
$partsOfUrl = explode("/", $dir);
$newUrlArray = [];

foreach ($partsOfUrl as $element) {
	if (!empty(trim($element))) {
			$newUrlArray[] = $element;
	}
}
?>

<?php if (isset($newUrlArray[1])):?>
	<h1 class="section-title"><?=$arResult['SECTION_ITEMS'][0]['SECTION_ITEMS_NAME']?></h1>
	<?foreach ($arResult['SECTION_ITEMS'] as $key => $value):?>
		<div class="section-item">
				<a href="<?=$value['DETAIL_PAGE_URL']?>"><?=$value['NAME']?></a>
		</div>
	<?php endforeach;?>
<?php elseif (isset($newUrlArray[0])):?>
    <div class="news-section">
			<?php foreach ($arResult['SECTIONS_COLLECT'] as $key => $value):?>
        <div class="news-item">
            <a href="<?=$value['SECTION_PAGE_URL']?>"><?=$value['NAME']?></a>
        </div>
			<?php endforeach;?>
    </div>
<?php endif;?>

<div class="back_page_url">
	<a href="/novosti">Назад к странице новостей</a>
</div>
