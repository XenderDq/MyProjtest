<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title"><?=$arResult['arForm']['NAME'];?></div>
        <div class="contact-form__head-text"><?=$arResult['FORM_DESCRIPTION'];?></div>
    </div>

    <?php if($arResult['isFormErrors'] == 'Y'): ?>
        <div class="error-message" style="color: red; padding: 10px; margin: 10px 0; border: 1px solid red;">
            <?=$arResult['FORM_ERRORS_TEXT']?>
        </div>
    <?php endif; ?>

    <?php if($arResult['isFormNote'] == 'Y'): ?>
        <div class="success-message" style="color: green; padding: 10px; margin: 10px 0; border: 1px solid green;">
            Форма успешно отправлена!
        </div>
    <?php else: ?>

    <?=$arResult["FORM_HEADER"]?>

    <div class="contact-form__form">
        <div class="contact-form__form-inputs">
            <?foreach ($arResult['QUESTIONS'] as $key => $value):?>
                <div class="input contact-form__input">
                    <label class="input__label">
                        <div class="input__label-text"><?=$value['CAPTION']?><?if($value['REQUIRED'] == 'Y'):?>*<?endif;?></div>
                        <?=$arResult['QUESTIONS'][$key]['HTML_CODE']?>
                        <div class="input__notification"><?=$value['COMMENTS']?></div>
                    </label>
                </div>
            <?php endforeach;?>

        <!-- Поле: Сообщение -->
        <div class="contact-form__form-message">
            <div class="input">
                <label class="input__label">
                    <div class="input__label-text"><?=$arResult['LAST_ELEM']['CAPTION']?></div>
                    <?=$arResult['LAST_ELEM']['HTML_CODE']?>
                    <div class="input__notification"></div>
                </label>
            </div>
        </div>

        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных данных&raquo;.</div>
            <button class="form-button contact-form__bottom-button" type="submit" name="web_form_submit" data-success="Отправлено" data-error="Ошибка отправки">
                <div class="form-button__title"><?=$arResult["SUBMIT_BUTTON"]?></div>
            </button>
        </div>
    </div>

    <?=$arResult["FORM_FOOTER"]?>
    <?php endif; ?>
</div>
