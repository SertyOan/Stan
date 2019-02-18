<?php
namespace App\Service;

use App\Helpers;
use App\DataRequest;
use App\Database;
use App\Session;
use App\Model\Attendee;
use App\Model\Event;
use App\Model\User;

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
        Helpers::checkParams($params, 'year', 'is_int');
        Helpers::checkParams($params, 'month', 'is_int');
        Helpers::checkParams($params, 'day', 'is_int');
        Helpers::checkParams($params, 'hour', 'is_int');
        Helpers::checkParams($params, 'minute', 'is_int');
        Helpers::checkParams($params, 'statuses', 'is_array');

        $statuses = [];

        foreach($params->statuses as $status) {
            if(is_string($status)) {
                $status = strip_tags($status);
                $status = trim($status);

                if(!empty($status)) {
                    $statuses[] = $status;
                }
            }
        }

        if(sizeof($statuses) < 1) {
            throw new \Exception('Not enough statuses');
        }

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

        if($params->year < date('Y')) {
            throw new \Exception('Invalid year');
        }

        if($params->month < 1 || $params->month > 12) {
            throw new \Exception('Invalid month');
        }

        if($params->day < 1 || $params->day > 31) {
            throw new \Exception('Invalid day');
        }

        if($params->hour < 0 || $params->hour > 23) {
            throw new \Exception('Invalid hour');
        }

        if($params->minute < 0 || $params->minute > 59) {
            throw new \Exception('Invalid minute');
        }

        $stringDate = $params->year.'-';
        $stringDate .= str_pad($params->month, 2, '0', STR_PAD_LEFT).'-';
        $stringDate .= str_pad($params->day, 2, '0', STR_PAD_LEFT).' ';
        $stringDate .= str_pad($params->hour, 2, '0', STR_PAD_LEFT).':';
        $stringDate .= str_pad($params->minute, 2, '0', STR_PAD_LEFT);

        $date = new \DateTime($stringDate, new \DateTimeZone('Europe/Paris')); // TODO review
        $timestamp = $date->getTimestamp();

        $event = new Event();
        $event->category = $category;
        $event->startAt = $timestamp;
        $event->endAt = $timestamp + $params->duration * 60;
        $event->statuses = implode('|', $statuses);

        $event->save();
        Database::getWriter()->commit();
        return true;
    }

    public static function delete($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        Helpers::checkParams($params, 'eventID', 'is_int');

        $request = DataRequest::get('Event')->withFields('id', 'recurrence')
            ->where('', 'Event', 'id', '=', $params->eventID);

        if(($session->user->role & User::ROLE_ADMINISTRATOR) === 0) {
            $request->innerJoin('Category')->on('Event', 'category')
                ->innerJoin('Subscription')->on('Category', 'id', 'category')->with('', 'user', '=', $session->user->id);
        }

        $event = $request->mapAsObject();
    
        if(empty($event)) {
            throw new \Exception('Evènement non trouvée');
        }

        $event->delete();
        Database::getWriter()->commit();
        return true;
    }
}
