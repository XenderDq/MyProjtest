<?php
$commAdd = []; 

 foreach ($arResult['arQuestions'] as $key => $value) {
    $commAdd[$key] = $value['COMMENTS']; 
 }
 foreach ($commAdd as $key => $value) {
    $arResult['QUESTIONS'][$key]['COMMENTS'] = $value;
 }
 $arResult['LAST_ELEM'] = array_pop($arResult['QUESTIONS']);
?>
