
<?php
$cutIdSec = [];

foreach ($arResult['ITEMS'] as $key => $value) {
  if ($value['IBLOCK_SECTION_ID']) {
      $cutIdSec[] = $value['IBLOCK_SECTION_ID'];
  }
}

$cutIdSec = array_unique($cutIdSec);

$rsData = CIBlockSection::GetList(
  [
    'SORT' => 'asc'
  ],
  [
    'ID' => $cutIdSec, 
    'ACTIVE' => 'Y',
    'IBLOCK_ID' => 5,
  ],
  false, 
  [ 
    'ID',
    'NAME',
    'SECTION_PAGE_URL',
    'CODE',
    'UF_CHARACT'
  ]
);
    
while ($arData = $rsData->GetNext()) {
  $arResult['SECTIONS_COLLECT'][] = $arData;
}

$dir = $APPLICATION->GetCurDir();
$partsOfUrl = explode("/", $dir);
$newUrlArray = [];

foreach ($partsOfUrl as $element) {
  if (!empty(trim($element))) {
    $newUrlArray[] = $element;
  }
}

foreach ($arResult['SECTIONS_COLLECT'] as $key => $value) {
  if($newUrlArray[1] === $value['CODE']) {
    $newUrlArray['NAME'] = $value['NAME'];
  }
}

if (isset($newUrlArray[1])) {
  $rsElements = CIBlockElement::GetList(
    [
      'SORT' => 'asc',
    ],
    [
      'IBLOCK_ID' => 5,
      'SECTION_CODE' => $newUrlArray[1],
      'ACTIVE' => 'Y',
    ],
    false,
    false,
    [ 
      'ID',
      'NAME',
      'CODE',
      'PREVIEW_TEXT',
      'PREVIEW_PICTURE',
      'DETAIL_TEXT',
      'DETAIL_PICTURE',
      'DATE_ACTIVE_FROM',
      'DATE_CREATE',
      'TIMESTAMP_X',
      'IBLOCK_SECTION_ID',
      'PROPERTY_DATADATA',
      'DETAIL_PAGE_URL'
    ]
  );
  $arResult['SECTION_ITEMS'][0]['SECTION_ITEMS_NAME'] = $newUrlArray['NAME'];

  while ($arElement = $rsElements->GetNext()) {
      $arResult['SECTION_ITEMS'][] = $arElement;
  }  
}
