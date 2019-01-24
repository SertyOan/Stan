<?php
chdir(dirname(__FILE__).'/../../..');
require('etc/Configuration.php');
require('code/server/php/ThirdParty/autoload.php');

try {
    $recurrences = \App\DataRequest::get('Recurrence')->withFields('id', 'category', 'duration', 'timezone', 'from', 'to', 'hour', 'minute', 'type', 'options', 'statuses')
        ->where('', 'Recurrence', 'from', '<', time())
        ->where('AND', 'Recurrence', 'to', '>', time())
        ->mapAsObjects();

    foreach($recurrences as $recurrence) {
        $recurrence->createEvents();
    }

    \App\Database::getWriter()->commit();
}
catch(\Exception $e) {
    print($e->getMessage()."\n");
    print($e->getTraceAsString()."\n");
}
