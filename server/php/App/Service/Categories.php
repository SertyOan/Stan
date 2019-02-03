<?php
namespace App\Service;

use App\Session;
use App\Database;
use App\DataRequest;
use App\Model\User;
use App\Model\Category;

class Categories {
    public static function search($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'subscribed') || !is_bool($params->subscribed)) {
            throw new \InvalidArgumentException('Parameter "subscribed" expected');
        }

        $request = DataRequest::get('Category')->withFields('id', 'name', 'color')
            ->leftJoin('Subscription')->on('Category', 'id', 'category')->with('', 'user', '=', $session->user->id);

        if($params->subscribed === true) {
            $request->where('', 'Subscription', 'user', '=', $session->user->id);
        }
        else {
            $request->where('', 'Subscription', 'user', 'IS NULL');
        }

        return $request->mapAsArrays();
    }

    public static function get($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'categoryID') || !is_int($params->categoryID)) {
            throw new \InvalidArgumentException('Parameter "categoryID" expected');
        }

        $category = DataRequest::get('Category')->withFields('id', 'name', 'color')
            ->leftJoin('Recurrence', 'Recurrences')->on('Category', 'id', 'category')->withFields('id', 'from', 'to', 'timezone', 'weekDay', 'monthDay', 'hour', 'minute', 'duration', 'type', 'statuses')
            ->leftJoin('Subscription', 'Subscriptions')->on('Category', 'id', 'category')->withFields('id', 'role')
                ->leftJoin('User')->on('Subscription', 'user')->withFields('id', 'nickname')
            ->where('', 'Category', 'id', '=', $params->categoryID)
            ->mapAsObject();
        return $category->asArray();
    }

     public static function create($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(($session->user->role & User::ROLE_ADMINISTRATOR) === 0) {
            throw new \Exception('Not allowed');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'name') || !is_string($params->name)) {
            throw new \InvalidArgumentException('Parameter "name" expected');
        }

        $name = strip_tags($params->name);
        $name = trim($name);

        if(mb_strlen($name) < 4) {
            throw new \Exception('Nom trop court');
        }

        if(!property_exists($params, 'color') || !is_string($params->color)) {
            throw new \InvalidArgumentException('Parameter "color" expected');
        }

        $color = $params->color;

        if(!preg_match('/^[0-9A-F]{6}$/', $color)) {
            throw new \Exception('Couleur invalide');
        }

        $found = DataRequest::get('Category')->withFields('id', 'name', 'color')
            ->where('', 'Category', 'name', '=', $name)
            ->where('OR', 'Category', 'color', '=', $color)
            ->mapAsObject();

        if(!empty($found)) {
            if($found->name === $name) {
                throw new \Exception('Nom déjà utilisé');
            }

            if($found->color === $color) {
                throw new \Exception('Couleur déjà utilisée');
            }
        }

        $category = new Category();
        $category->name = $name;
        $category->color = $color;
        $category->save();
        Database::getWriter()->commit();
        return true;
    }

    public function delete() {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(($session->user->role & User::ROLE_ADMINISTRATOR) === 0) {
            throw new \Exception('Not allowed');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'categoryID') || !is_int($params->categoryID)) {
            throw new \InvalidArgumentException('Parameter "categoryID" expected');
        }

        $category = DataRequest::get('Category')->withFields('id')
            ->leftJoin('Event', 'Events')->on('Category', 'id', 'category')->withFields('id')
                ->leftJoin('Attendee', 'Attendees')->on('Event', 'id', 'event')->withFields('id')
            ->leftJoin('Message', 'Messages')->on('Category', 'id', 'category')->withFields('id')
            ->leftJoin('Recurrence', 'Recurrences')->on('Category', 'id', 'category')->withFields('id')
            ->leftJoin('Subscription', 'Subscriptions')->on('Category', 'id', 'category')->withFields('id')
            ->where('', 'Category', 'id', '=', $params->categoryID)
            ->mapAsObject();

        foreach($category->myMessages as $message) {
            $message->delete();
        }

        foreach($category->myRecurrences as $recurrence) {
            $recurrence->delete();
        }

        foreach($category->mySubscriptions as $subscription) {
            $subscription->delete();
        }

        foreach($category->myEvents as $event) {
            foreach($event->myAttendees as $attendee) {
                $attendee->delete();
            }

            $event->delete();
        }

        $category->delete();
        Database::getWriter()->commit();
        return true;
    }
}
