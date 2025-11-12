<?php
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\IblockTable;

AddEventHandler('iblock', 'OnAfterIBlockElementAdd', ['LogHandler', 'handleElementChange']);
AddEventHandler('iblock', 'OnAfterIBlockElementUpdate', ['LogHandler', 'handleElementChange']);

class LogHandler
{
  public static function handleElementChange(&$arFields)
  {
    if (!Loader::includeModule('iblock')) {
        return;
    }

    $elementIblockId = $arFields['IBLOCK_ID'];
    
    $logIblock = self::getLogIblock();
    if (!$logIblock) {
        return;
    }

    if ($elementIblockId == $logIblock['ID']) {
        return;
    }

    try {
        self::processElementLog($arFields, $logIblock, $elementIblockId);
    } catch (Exception $e) {
        AddMessage2Log("LogHandler error: " . $e->getMessage());
    }
  }

  private static function getLogIblock()
  {
      $iblock = IblockTable::getList([
          'filter' => ['CODE' => 'LOG'],
          'select' => ['ID', 'CODE']
      ])->fetch();

      return $iblock ?: false;
  }

  private static function processElementLog(&$arFields, $logIblock, $elementIblockId)
  {
      $elementId = $arFields['ID'];
      
      $sourceIblock = IblockTable::getList([
          'filter' => ['ID' => $elementIblockId],
          'select' => ['ID', 'NAME', 'CODE']
      ])->fetch();

      if (!$sourceIblock) {
          return;
      }

      $logSectionId = self::getOrCreateLogSection($sourceIblock, $logIblock['ID']);
      $elementPath = self::getElementPath($elementId, $elementIblockId);
      self::createOrUpdateLogElement($elementId, $logIblock['ID'], $logSectionId, $elementPath);
  }

  private static function getOrCreateLogSection($sourceIblock, $logIblockId)
  {
      $section = SectionTable::getList([
          'filter' => [
              'IBLOCK_ID' => $logIblockId,
              'CODE' => $sourceIblock['CODE']
          ],
          'select' => ['ID']
      ])->fetch();

      if ($section) {
          return $section['ID'];
      }

      $section = new CIBlockSection;
      $sectionFields = [
          'ACTIVE' => 'Y',
          'IBLOCK_ID' => $logIblockId,
          'NAME' => $sourceIblock['NAME'],
          'CODE' => $sourceIblock['CODE']
      ];

      $newSectionId = $section->Add($sectionFields);
      
      return $newSectionId ?: 0;
  }

  private static function getElementPath($elementId, $iblockId)
  {
      $element = CIBlockElement::GetByID($elementId)->Fetch();
      
      if (!$element) {
          return '';
      }

      $pathParts = [];
      
      $iblock = IblockTable::getList([
          'filter' => ['ID' => $iblockId],
          'select' => ['NAME']
      ])->fetch();
      
      if ($iblock) {
          $pathParts[] = $iblock['NAME'];
      }

      if ($element['IBLOCK_SECTION_ID']) {
          $sectionPath = self::getSectionPath($element['IBLOCK_SECTION_ID']);
          if ($sectionPath) {
              $pathParts[] = $sectionPath;
          }
      }

      $pathParts[] = $element['NAME'];

      return implode(' -> ', $pathParts);
  }

  private static function getSectionPath($sectionId)
  {
      $path = [];
      $currentSectionId = $sectionId;

      while ($currentSectionId) {
          $section = CIBlockSection::GetByID($currentSectionId)->Fetch();
          
          if (!$section) {
              break;
          }

          $path[] = $section['NAME'];
          $currentSectionId = $section['IBLOCK_SECTION_ID'];
      }

      return implode(' -> ', array_reverse($path));
  }

  private static function createOrUpdateLogElement($elementId, $logIblockId, $logSectionId, $elementPath)
  {
      $element = new CIBlockElement;
      
      $fields = [
          'ACTIVE' => 'Y',
          'IBLOCK_ID' => $logIblockId,
          'IBLOCK_SECTION_ID' => $logSectionId,
          'NAME' => (string)$elementId,
          'ACTIVE_FROM' => ConvertTimeStamp(time(), 'FULL'),
          'PREVIEW_TEXT' => $elementPath,
          'PREVIEW_TEXT_TYPE' => 'text'
      ];

      $dbElement = CIBlockElement::GetList(
          [],
          [
              'IBLOCK_ID' => $logIblockId,
              'NAME' => (string)$elementId
          ],
          false,
          false,
          ['ID']
      );
      
      $existingLog = $dbElement->Fetch();

      if ($existingLog) {
          $element->Update($existingLog['ID'], $fields);
      } else {
          $element->Add($fields);
      }
  }
}

function CleanUpOldLogs() {
    CEventLog::Add([
        "SEVERITY" => "INFO",
        "AUDIT_TYPE_ID" => "AGENT_CLEANUP",
        "MODULE_ID" => "main",
        "ITEM_ID" => "LOG",
        "DESCRIPTION" => "Агент CleanUpOldLogs запущен в " . date('Y-m-d H:i:s'),
    ]);

    if (!CModule::IncludeModule('iblock')) {
        CEventLog::Add([
            "SEVERITY" => "ERROR",
            "AUDIT_TYPE_ID" => "AGENT_CLEANUP_ERROR",
            "MODULE_ID" => "main", 
            "ITEM_ID" => "LOG",
            "DESCRIPTION" => "Модуль iblock не доступен",
        ]);
        return "CleanUpOldLogs();";
    }

    $logIblockId = 7;
    
    $res = CIBlock::GetList( 
      [],
      [
        'ID' => $logIblockId,
        'CODE' => 'LOG',
      ],
      false
    );
    
    if($iblock = $res->Fetch()) {
      if (!$iblock['ID']) {
         CEventLog::Add([
            "SEVERITY" => "ERROR", 
            "AUDIT_TYPE_ID" => "AGENT_CLEANUP_ERROR",
            "MODULE_ID" => "main",
            "ITEM_ID" => "LOG", 
            "DESCRIPTION" => "Инфоблок с ID=" . $logIblockId . " не найден",
        ]);
        return "CleanUpOldLogs();";
      }
    }

    $dbElements = CIBlockElement::GetList(
        [
          'DATE_CREATE' => 'DESC', 
          'ID' => 'DESC'
        ],
        [
          'IBLOCK_ID' => $logIblockId
        ],
        false,
        false,
        [
          'ID', 
          'NAME', 
          'DATE_CREATE'
        ]
    );
    
    $allElements = [];
    while ($element = $dbElements->Fetch()) {
        $allElements[] = $element;
    }

    $totalElements = count($allElements);
    
    CEventLog::Add([
        "SEVERITY" => "INFO",
        "AUDIT_TYPE_ID" => "AGENT_CLEANUP", 
        "MODULE_ID" => "main",
        "ITEM_ID" => "LOG",
        "DESCRIPTION" => "Найдено элементов в логе: " . $totalElements,
    ]);

    if ($totalElements <= 10) {
        CEventLog::Add([
            "SEVERITY" => "INFO",
            "AUDIT_TYPE_ID" => "AGENT_CLEANUP",
            "MODULE_ID" => "main",
            "ITEM_ID" => "LOG", 
            "DESCRIPTION" => "Удаление не требуется (элементов <= 10)",
        ]);
        return "CleanUpOldLogs();";
    }

    $keepElements = array_slice($allElements, 0, 10); 
    $deleteElements = array_slice($allElements, 10);  

    CEventLog::Add([
        "SEVERITY" => "INFO",
        "AUDIT_TYPE_ID" => "AGENT_CLEANUP",
        "MODULE_ID" => "main",
        "ITEM_ID" => "LOG",
        "DESCRIPTION" => "Будет удалено элементов: " . count($deleteElements),
    ]);

    $deletedCount = 0;
    $errorCount = 0;
    
    foreach ($deleteElements as $element) {
        $result = CIBlockElement::Delete($element['ID']);
        if ($result) {
            $deletedCount++;
            CEventLog::Add([
                "SEVERITY" => "INFO",
                "AUDIT_TYPE_ID" => "AGENT_CLEANUP",
                "MODULE_ID" => "main",
                "ITEM_ID" => "LOG",
                "DESCRIPTION" => "Удален элемент ID: " . $element['ID'] . " (NAME: " . $element['NAME'] . ")",
            ]);
        } else {
            $errorCount++;
            CEventLog::Add([
                "SEVERITY" => "ERROR",
                "AUDIT_TYPE_ID" => "AGENT_CLEANUP_ERROR",
                "MODULE_ID" => "main",
                "ITEM_ID" => "LOG",
                "DESCRIPTION" => "Ошибка удаления элемента ID: " . $element['ID'],
            ]);
        }
    }

    CEventLog::Add([
        "SEVERITY" => "INFO",
        "AUDIT_TYPE_ID" => "AGENT_CLEANUP",
        "MODULE_ID" => "main",
        "ITEM_ID" => "LOG",
        "DESCRIPTION" => "Удаление завершено. Удалено: " . $deletedCount . ", ошибок: " . $errorCount . ", оставлено: 10",
    ]);

    return "CleanUpOldLogs();";
}