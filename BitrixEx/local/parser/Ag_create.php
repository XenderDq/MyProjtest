<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

CAgent::RemoveAgent('CleanUpOldLogs();', '');

$agentId = CAgent::AddAgent(
    'CleanUpOldLogs();',
    '',     
    'N',    
    3600,   
    '',     
    'Y',    
    date('d.m.Y H:i:s', time() + 10), 
    100    
);

if ($agentId) {
    echo "Агент успешно зарегистрирован! ID: " . $agentId . "<br>";
    
    $dbAgent = CAgent::GetList([], ['NAME' => 'CleanUpOldLogs();']);
    if ($agent = $dbAgent->Fetch()) {
        echo "Агент найден в базе:<br>";
        echo "ID: " . $agent['ID'] . "<br>";
        echo "Активен: " . $agent['ACTIVE'] . "<br>";
        echo "Следующий запуск: " . $agent['NEXT_EXEC'] . "<br>";
        echo "Интервал: " . $agent['AGENT_INTERVAL'] . "<br>";
    }
    
    echo "<br>Теперь подождите 1-2 минуты и проверьте журнал событий.";
} else {
    echo "Ошибка регистрации агента!";
}
?>