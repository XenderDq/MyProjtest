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
            <?php foreach ($arResult['arQuestions'] as $key => $value):?>
                <div class="input contact-form__input">
                    <?php if ($value['TITLE']==='Email'):?> 
                        <label class="input__label" for="form_email_<?=$value['ID']?>">
                    <?php endif;?>
                    <?php if ($value['TITLE']!='Email'):?> 
                        <label class="input__label" for="form_text_<?=$value['ID']?>">
                    <?php endif;?>
                        <div class="input__label-text"><?=$value['TITLE']?>*</div>
                    <?php if ($value['TITLE']!='Email' && $value['TITLE']!='Номер телефона'):?>
                        <input class="input__input" type="text" id="form_text_<?=$value['ID']?>" name="form_text_<?=$value['ID']?>" value="<?=htmlspecialchars($arResult['arrVALUES']['form_text_' . $value['ID']] ?? '')?>" required>
                    <?php endif;?>
                    <?php if ($value['TITLE']==='Email'):?>
                        <input class="input__input" type="email" id="form_email_<?=$value['ID']?>" name="form_email_<?=$value['ID']?>" value="<?=htmlspecialchars($arResult['arrVALUES']['form_email_' . $value['ID']] ?? '')?>" required>
                    <?php endif;?>
                    <?php if ($value['TITLE']==='Номер телефона'):?>
                        <input class="input__input" type="tel" id="form_text_<?=$value['ID']?>" name="form_text_<?=$value['ID']?>" value="<?=htmlspecialchars($arResult['arrVALUES']['form_text_' . $value['ID']] ?? '')?>" 
                            data-inputmask="'mask': '+79999999999', 'clearIncomplete': 'true'" maxlength="12" x-autocompletetype="phone-full" required>
                    <?php endif;?>
                    <?php if ($value['TITLE']!='Сообщение' && $value['TITLE']!='Номер телефона'):?>
                        <div class="input__notification"><?=$value['COMMENTS']?></div>
                    <?php endif;?>
                    </label>
                </div>
            <?php endforeach;?>
        </div>
        
        <div class="contact-form__form-message">
            <div class="input">
                <label class="input__label" for="form_textarea_<?=$arResult['LAST_ELEM']['ID']?>">
                    <div class="input__label-text"><?=$arResult['LAST_ELEM']['TITLE']?></div>
                    <textarea class="input__input" id="form_textarea_<?=$arResult['LAST_ELEM']['ID']?>" name="form_textarea_<?=$arResult['LAST_ELEM']['ID']?>"><?=htmlspecialchars($arResult['arrVALUES']['form_text_' . $arResult['LAST_ELEM']['ID']] ?? '')?></textarea>
                    <div class="input__notification"></div>
                </label>
            </div>
        </div>

        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что
                ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных данных&raquo;.
            </div>
            <button class="form-button contact-form__bottom-button" type="submit" name="web_form_submit" data-success="Отправлено" data-error="Ошибка отправки">
                <div class="form-button__title"><?=$arResult['arForm']['BUTTON'];?></div>
            </button>
        </div>

    </div>

    <?=$arResult["FORM_FOOTER"]?>
    <?php endif; ?>
</div>
