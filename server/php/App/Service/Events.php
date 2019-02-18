<?php
namespace App\Service;

use App\Helpers;
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

        if(!property_exists($params, 'guest')) {
            $attendee = DataRequest::get('Attendee')->withFields('id')
                ->where('', 'Attendee', 'event', '=', $event->id)
                ->where('AND', 'Attendee', 'createdBy', '=', $session->user->id)
                ->where('AND', 'Attendee', 'guest', 'IS NULL')
                ->mapAsObject();
        }
        else {
            if(!is_string($params->guest)) {
                throw new \InvalidArgumentException('Invalid value for guest');
            }

            $guest = strip_tags($params->guest);

            if(mb_strlen($guest) < 3) {
                throw new \InvalidArgumentException('Le nom de l\'invité est trop court');
            }
        }

        if(empty($attendee)) {
            $attendee = new Attendee();
            $attendee->createdBy = $session->user;
            $attendee->event = $event;
        }

        if(!empty($guest)) {
            $attendee->guest = $guest;
        }

        // TODO check $params->status is in $event->statuses list
        $attendee->status = $params->status;
        $attendee->createdAt = new \DateTime();
        $attendee->save();
        Database::getWriter()->commit();

        return self::get($params);
    }

    public static function unattend($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'attendeeID') || !is_int($params->attendeeID)) {
            throw new \InvalidArgumentException('Attendee ID expected');
        }

        $attendee = DataRequest::get('Attendee')->withFields('id', 'event')
            ->where('', 'Attendee', 'id', '=', $params->attendeeID)
            ->where('AND', 'Attendee', 'createdBy', '=', $session->user->id)
            ->mapAsObject();

        if(empty($attendee)) {
            throw new \Exception('Could not find attendee');
        }

        $params->eventID = $attendee->event->id;

        $attendee->delete();
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

    public static function create($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        Helpers::checkParams($params, 'categoryID', 'is_int');

        $category = DataRequest::get('Category')->withFields('id')
            ->where('', 'Category', 'id', '=', $params->categoryID)
            ->mapAsObject();
    
        if(empty($category)) {
            throw new \Exception('Catégorie non trouvée');
        }

        if(($session->user->role & User::ROLE_ADMINISTRATOR) === 0) {
            $check = DataRequest::get('Subscription')->withFields('id', 'owner')
                ->where('', 'Subscription', 'category', '=', $category->id)
                ->where('AND', 'Subscription', 'user', '=', $session->user->id)
                ->where('AND', 'Subscription', 'owner', '=', 1)
                ->mapAsObject();

            if(empty($check)) {
                throw new \Exception('Action refusée');
            }
        }


        return true;
    }
}
