<?php
namespace App\Service;

use App\DataRequest;
use App\Database;
use App\Session;
use App\Model\Subscription;
use App\Model\User;

class Subscriptions {
    public static function create($params = null) {
        $record = self::find($params);

        if(!empty($record)) {
            throw new \Exception('Subscription already exists');
        }

        $record = new Subscription();
        $record->category->id = $params->categoryID;
        $record->user = Session::get()->user;
        $record->owner = 0;
        $record->save();
        Database::getWriter()->commit();
        return true;
    }

    public static function delete($params = null) {
        $record = self::find($params);

        if(empty($record)) {
            throw new \Exception('Subscription not found');
        }

        $record->delete();
        Database::getWriter()->commit();
        return true;
    }

    private static function find($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'categoryID') || !is_int($params->categoryID)) {
            throw new \InvalidArgumentException('Category ID expected');
        }

        $category = DataRequest::get('Category')->withFields('id')
            ->where('', 'Category', 'id', '=', $params->categoryID)
            ->mapAsObject();

        if(empty($category)) {
            throw new \Exception('Category not found');
        }

        $record = DataRequest::get('Subscription')->withFields('id')
            ->where('', 'Subscription', 'user', '=', $session->user->id)
            ->where('AND', 'Subscription', 'category', '=', $category->id)
            ->mapAsObject();
        return $record;
    }

    public static function promote($params = null) {
        return self::setOwner($params, 1);
    }

    public static function demote($params = null) {
        return self::setOwner($params, 0);
    }

    private static function setOwner($params = null, $value) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'subscriptionID') || !is_int($params->subscriptionID)) {
            throw new \InvalidArgumentException('Subscription ID expected');
        }

        $record = DataRequest::get('Subscription')->withFields('id', 'category')
            ->where('', 'Subscription', 'id', '=', $params->subscriptionID)
            ->mapAsObject();

        if(empty($record)) {
            throw new \Exception('Inscription non trouvée');
        }

        if(($session->user->role & User::ROLE_ADMINISTRATOR) === 0) {
            $check = DataRequest::get('Subscription')->withFields('id', 'owner')
                ->where('', 'Subscription', 'category', '=', $record->category->id)
                ->where('AND', 'Subscription', 'user', '=', $session->user->id)
                ->where('AND', 'Subscription', 'owner', '=', 1)
                ->mapAsObject();

            if(empty($check)) {
                throw new \Exception('Action refusée');
            }
        }

        $record->owner = $value;
        $record->save();
        Database::getWriter()->commit();
        return true;
    }
}
