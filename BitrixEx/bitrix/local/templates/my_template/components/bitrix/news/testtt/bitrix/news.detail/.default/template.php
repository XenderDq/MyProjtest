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

<div class="article-card">
		<div class="article-card__title"><?=$arResult['NAME']?></div>
		<div class="article-card__date"><?=$arResult['PROPERTY_DATADATA_VALUE']?></div>
		<div class="article-card__content">
				<div class="article-card__image sticky">
					<img src="<?=$arResult['DETAIL_PICTURE']['SRC']?>" alt="" data-object-fit="cover"/>
				</div>
				<div class="article-card__text">
						<div class="block-content" data-anim="anim-3"><?=$arResult['PREVIEW_TEXT']?></div>
						<a class="article-card__button" href="<?=$arResult['SECTION']['PATH'][0]['SECTION_PAGE_URL']?>">Назад к разделу</a>
				</div>
		</div>
</div>