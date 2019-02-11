<?php
namespace App\Service;

use App\Session;
use App\DataRequest;
use App\Mail;
use App\Model\User;

class Application {
    public static function session() {
        $session = Session::get();
        $user = $session->user;

        return [
            'id' => empty($user) ? null : $user->id,
            'role' => empty($user) ? null : $user->role,
            'nickname' => empty($user) ? null : $user->nickname
        ];
    }

    public static function mail($params = null) {
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

        if(!property_exists($params, 'title') || !is_string($params->title)) {
            throw new \InvalidArgumentException('Parameter "title" expected');
        }

        if(!property_exists($params, 'content') || !is_string($params->content)) {
            throw new \InvalidArgumentException('Parameter "content" expected');
        }

        $title = strip_tags($params->title);
        $content = strip_tags($params->content);

        if(mb_strlen($title) < 5) {
            throw new \InvalidArgumentException('Title is too short');
        }

        if(mb_strlen($content) < 15) {
            throw new \InvalidArgumentException('Content is too short');
        }

        $users = DataRequest::get('User')->withFields('id', 'email', 'nickname', 'secretKey')
            ->mapAsObjects();

        $successful = 0;
        $errors = [];

        foreach($users as $user) {
            try {
                Mail::setDomain('osyra.net');

                $text = 'Ceci est un email de l\'administrateur de '.STAN_FQDN
                    ."\n\t\t".'/!\ Ne transférez pas cet email /!\ '
                    ."\n\n\n".$content
                    ."\n\n\n".'Adresse d\'identification pour '.$user->email.' :'
                    ."\n\t\t".'https://'.STAN_FQDN.'/#'.$user->secretKey
                    ."\n".'Copiez cette adresse dans votre navigateur afin de vous identifier pour utiliser l\'application d\'inscription'
                    ."\n".'Si vous recontrez un problème, envoyez un mail à '.STAN_EMAIL;

                $mail = new Mail();
                $mail->setSender(STAN_EMAIL);
                $mail->addRecipient($user->email);
                $mail->setSubject($title. ' - '.STAN_FQDN);
                $mail->textBody($text, 'utf-8');
                $mail->send();

                $successful++;
            }
            catch(\Exception $e) {
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
                $errors[] = $user->email;
            }
        }

        return [
            'successful' => $successful,
            'errors' => $errors
        ];
    }
}
