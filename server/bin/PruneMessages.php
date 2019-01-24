<?php
chdir(dirname(__FILE__).'/../../..');
require('etc/Configuration.php');
require('code/server/php/ThirdParty/autoload.php');

try {
    $messages = \App\DataRequest::get('Message')->withFields('id')
        ->where('', 'Message', 'createdAt', '<', time() - 14 * 86400)
        ->mapAsObjects();

    foreach($messages as $message) {
        $message->delete();
    }

    \App\Database::getWriter()->commit();
}
catch(\Exception $e) {
    print($e->getMessage()."\n");
    print($e->getTraceAsString()."\n");
}
