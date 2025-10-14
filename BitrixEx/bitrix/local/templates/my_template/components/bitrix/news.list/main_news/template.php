<?php foreach ($arResult['ITEMS'] as $item): ?>
    <a class="article-item article-list__item" href="for-individuals.html" data-anim="anim-3">
        <div class="article-item__background">
            <img src="<?=$item['PREVIEW_PICTURE']['SRC'] ?? ''?>"
                 data-src="<?=$item['DETAIL_PICTURE']['SRC'] ?? ''?>"
                 alt="<?=$item['NAME']?>"/>
        </div>
        <div class="article-item__wrapper">
            <div class="article-item__title"><?=$item['NAME']?></div>
            <div class="article-item__content">
                <?= strip_tags($item['PREVIEW_TEXT']) ?>
            </div>
        </div>
    </a>
<?php endforeach; ?>
