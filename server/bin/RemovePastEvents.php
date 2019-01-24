<?php
chdir(dirname(__FILE__).'/../../..');
require('etc/Configuration.php');
require('code/server/php/ThirdParty/autoload.php');

try {
    $events = \App\DataRequest::get('Event')->withFields('id')
        ->leftJoin('Attendee', 'Attendees')->on('Event', 'id', 'event')->withFields('id')
        ->where('', 'Event', 'endAt', '<', time())
        ->mapAsObjects();

    foreach($events as $event) {
        foreach($event->myAttendees as $attendee) {
            $attendee->delete();
        }

        $event->delete();
    }

    \App\Database::getWriter()->commit();
}
catch(\Exception $e) {
    print($e->getMessage()."\n");
    print($e->getTraceAsString()."\n");
}
