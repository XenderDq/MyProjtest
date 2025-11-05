<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}
\Bitrix\Main\Loader::includeModule('iblock');

if (!CModule::IncludeModule('iblock')) {
    die();
}

class NewsParser 
{
    private $iblockId;
    private $arElem = []; 
    private $propCountList = [];
    
    public function __construct($iblockId) {
        $this->iblockId = $iblockId;
        $this->loadItems();
    }

    public function loadItems() {
       $arRes = CIBlockElement::GetList(
            false,
            [
                'IBLOCK_ID' => $this->iblockId,
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'PREVIEW_TEXT',
                'DETAIL_TEXT',
            ]
        );
        while ($res = $arRes->GetNext()) {
            unset($res['PREVIEW_TEXT_TYPE']);
            unset($res['DETAIL_TEXT_TYPE']);
            $this->arElem[$res['ID']] = $res;
        }
        $text = '';
        foreach ($this->arElem as $key => $value) {
            if($value['PREVIEW_TEXT']) {
               $text = $value['PREVIEW_TEXT'];
               $this->arElem[$key]['PREVIEW_TEXT'] = $text;
               $text = '';
            }
            if($value['DETAIL_TEXT']) {
               $text = $value['DETAIL_TEXT'];
               $this->arElem[$key]['DETAIL_TEXT'] = $text;
               $text = '';
            }
        }
        $this->loadPropersties();
    }

    public function getPropUnq() {
        $arRes = CIBlockProperty::GetList(
            [],
            [
            'IBLOCK_ID' => $this->iblockId,
            'PROPERTY_TYPE' => 'L',
            ],
        );
        while ($res = $arRes->GetNext()) {
            $this->propCountList['ID'] = $res['ID'];
        }

        $enumValues = CIBlockPropertyEnum::GetList(
            [
                'SORT' => 'ASC'
            ],
            [
                'PROPERTY_ID' => $this->propCountList['ID']
            ]
        );
        while ($arEnum = $enumValues->GetNext()) {
            $this->propCountList['PROPERTY_ID'][] = $arEnum;
        }     
    }   
    
    public function loadPropersties() {
        $this->getPropUnq();

        foreach ($this->arElem as $key => $value) {
            $arRes = CIBlockElement::GetProperty(
                $this->iblockId,
                $key,
                [],
                [
                    'ID',
                    'NAME',
                    'CODE',
                    'VALUE',
                ]
            );
            while ($res = $arRes->GetNext()) {
                if ($key) {
                    if($res['PROPERTY_TYPE'] === 'L') {
                        if(empty($res['VALUE_ENUM']) && empty($res['DEFAULT_VALUE'])) {
                            $countValues = count($this->propCountList['PROPERTY_ID']);
                            $randomIndex = rand(0, $countValues - 1);
                            $this->arElem[$key][$res['CODE']] = $this->propCountList['PROPERTY_ID'][$randomIndex]['VALUE'];
                        }
                        if(empty($res['VALUE_ENUM']) && !empty($res['DEFAULT_VALUE'])) {
                           $this->arElem[$key][$res['CODE']] = $res['DEFAULT_VALUE'];
                        }
                        if (!empty($res['VALUE_ENUM'])) {
                            $this->arElem[$key][$res['CODE']] = $res['VALUE_ENUM'];
                        }
                    } else {
                        $this->arElem[$key][$res['CODE']] = $res['VALUE'];
                    }
                }
            }
        }

        $this->delIncorrSymbol();
    }

    public function delIncorrSymbol() {
        foreach ($this->arElem as $key => &$value) {
            foreach ($value as $key1 => $value1) {
                if (substr($key1, 0, 1) === '~') {
                    unset($value[$key1]);
                }
            }
        }
    }

    public function deleteElems() {
        $rsElements = CIBlockElement::GetList(
            [], 
            [
                'IBLOCK_ID' => $this->iblockId,
            ], 
            false, 
            false, 
            [
                'ID',
            ]
        );
        while ($element = $rsElements->GetNext()) {
            CIBlockElement::Delete($element['ID']);
        }
    }

    public function generateCsv() {
        try {
            $randomName = 'data_' . date('Y-m-d_H-i-s') . '_' . rand(1000, 9999) . '.csv';
            $filePath = __DIR__ . '/' . $randomName;
            
            if (empty($this->arElem)) {
                throw new Exception("Нет данных для экспорта");
            }

            $file = fopen($filePath, 'w');
            if (!$file) {
                throw new Exception("Не удалось создать файл: " . $filePath);
            }

            fwrite($file, "\xEF\xBB\xBF");

            $headers = array_keys($this->arElem[array_key_first($this->arElem)]);
            fputcsv($file, $headers, '|', '"');

            foreach ($this->arElem as $item) {
                fputcsv($file, $item, '|', '"');
            }
            
            fclose($file);
            
            if (!file_exists($filePath)) {
                throw new Exception("Файл не был создан");
            }
            
            return [
                'success' => true,
                'file_path' => $filePath,
                'file_name' => $randomName,
                'rows_count' => count($this->arElem)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}


$iblockId = 6; 

$parser = new NewsParser($iblockId);
$result = $parser->generateCsv();

if ($result['success']) {
    echo "CSV создан: " . $result['file_name'] . "\n";
    echo "Записей: " . $result['rows_count'] . "\n";

    $parser->deleteElems();

    $filePath = $result['file_path'];
    $dataArray = [];
    
    $file = fopen($filePath, 'r');
    if ($file) {
        $headers = fgetcsv($file, 0, '|', '"');
        while ($row = fgetcsv($file, 0, '|', '"')) {
            $dataArray[] = array_combine($headers, $row);
        }

        foreach ($dataArray as $key => $value) {
            $dataArray[$key]['PROPS'] = array_slice($value, -2);
        }
        

    $el = new CIBlockElement;

    foreach ($dataArray as $key => $value) {
        $propertyValues = [];
        
        foreach ($value['PROPS'] as $propCode => $propValue) {
            $property = CIBlockProperty::GetList(
                [], 
                [
                'IBLOCK_ID' => $iblockId,
                'CODE' => $propCode
                ]
            )->Fetch();
            
            if ($property && $property['PROPERTY_TYPE'] == 'L') {
                $enumValue = CIBlockPropertyEnum::GetList(
                    [], 
                    [
                    'PROPERTY_ID' => $property['ID'],
                    'VALUE' => $propValue
                    ]
                )->Fetch();
                
                if ($enumValue) {
                    $propertyValues[$propCode] = $enumValue['ID'];
                } else {
                    $enum = new CIBlockPropertyEnum;
                    $newEnumId = $enum->Add([
                        'PROPERTY_ID' => $property['ID'],
                        'VALUE' => $propValue,
                        'XML_ID' => CUtil::translit($propValue, 'ru') . '_' . time(),
                        'SORT' => 500
                    ]);
                    
                    if ($newEnumId) {
                        $propertyValues[$propCode] = $newEnumId;
                    }
                }
            } else {
                $propertyValues[$propCode] = $propValue;
            }
        }
        
        $PRODUCT_ID = $el->Add([
            "IBLOCK_SECTION_ID" => false,        
            "IBLOCK_ID" => $iblockId,
            "PROPERTY_VALUES" => $propertyValues,
            "NAME" => $value['NAME'],
            "ACTIVE" => "Y",          
            "PREVIEW_TEXT" => $value['PREVIEW_TEXT'],
            "DETAIL_TEXT" => $value['DETAIL_TEXT'],
        ]);
        
        if ($PRODUCT_ID) {
            echo "Добавлен элемент ID: " . $PRODUCT_ID . "\n";
        } else {
            echo "Ошибка: " . $el->LAST_ERROR . "\n";
        }
    }


    fclose($file);
    } else {
        echo "Ошибка открытия файла\n";
    }
} else {
    echo "Ошибка: " . $result['error'] . "\n";
}
?>
