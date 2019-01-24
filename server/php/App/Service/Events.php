<?php
namespace App\Service;

use App\DataRequest;
use App\Database;
use App\Session;
use App\Model\Attendee;

class Events {
    public static function search($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        $request = DataRequest::get('Event')->withFields('id', 'startAt', 'endAt', 'statuses')
            ->leftJoin('Attendee', 'Attendees')->on('Event', 'id', 'event')->withFields('id', 'status', 'guest', 'createdBy')
            ->leftJoin('User')->on('Attendee', 'createdBy')->withFields('id', 'nickname')
            ->innerJoin('Category')->on('Event', 'category')->withFields('id', 'name', 'color')
            ->innerJoin('Subscription')->on('Category', 'id', 'category')->with('', 'user', '=', $session->user->id)
            ->orderAscBy('Event', 'startAt')
            ->orderAscBy('Attendee', 'createdAt');

        return $request->mapAsArrays();
    }

    public static function attend($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'eventID') || !is_int($params->eventID)) {
            throw new \InvalidArgumentException('Event ID expected');
        }

        if(!property_exists($params, 'status') || !is_string($params->status)) {
            throw new \InvalidArgumentException('Status expected');
        }

        $event = DataRequest::get('Event')->withFields('id', 'statuses')
            ->where('', 'Event', 'id', '=', $params->eventID)
            ->mapAsObject();

        if(empty($event)) {
            throw new \Exception('Could not find event');
        }

        $attendee = DataRequest::get('Attendee')->withFields('id')
            ->where('', 'Attendee', 'event', '=', $event->id)
            ->where('AND', 'Attendee', 'createdBy', '=', $session->user->id)
            ->mapAsObject();

        if(empty($attendee)) {
            $attendee = new Attendee();
            $attendee->createdBy = $session->user;
            $attendee->event = $event;
        }

        // TODO check $params->status is in $event->statuses list
        $attendee->status = $params->status;
        $attendee->createdAt = new \DateTime();
        $attendee->save();
        Database::getWriter()->commit();

        return self::get($params);
    }

    public static function get($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'eventID') || !is_int($params->eventID)) {
            throw new \InvalidArgumentException('Event ID expected');
        }

        $event = DataRequest::get('Event')->withFields('id', 'startAt', 'endAt', 'statuses')
            ->leftJoin('Attendee', 'Attendees')->on('Event', 'id', 'event')->withFields('id', 'status', 'guest')
            ->leftJoin('User')->on('Attendee', 'createdBy')->withFields('id', 'nickname')
            ->innerJoin('Category')->on('Event', 'category')->withFields('id', 'name', 'color')
            ->where('', 'Event', 'id', '=', $params->eventID)
            ->mapAsObject();

        if(empty($event)) {
            throw new \Exception('Could not find event');
        }

        return $event->asArray();
    }
}
