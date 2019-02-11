<?php
namespace App\Service;

use App\DataRequest;
use App\Database;
use App\Mail;
use App\Session;
use App\Model\User;

class Users {
	public static function create($params = null) {
        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'email') || !is_string($params->email)) {
            throw new \InvalidArgumentException('Parameter "email" expected');
        }

        $email = trim($params->email);
        $email = strtolower($email);

        if(!Mail::checkAddress($email)) {
            throw new Exception('Adresse email invalide');
        }

        $user = DataRequest::get('User')->withFields('id', 'email', 'nickname', 'secretKey')
            ->where('', 'User', 'email', '=', $email)
            ->mapAsObject();

        if(empty($user)) {
            $user = new User();
            $user->email = $email;
            $user->secretKey = sha1($email.uniqid());
            $user->nickname = substr($email, 0, strpos($email, '@'));
            $user->save();
            Database::getWriter()->commit();
        }

        try {
            Mail::setDomain('osyra.net');

            $text = 'Ceci est un email automatique de '.STAN_FQDN
                ."\n\t\t".'/!\ Ne transférez pas cet email /!\ '
                ."\n\n".'Adresse d\'identification pour '.$user->email.' :'
                ."\n\t\t".'https://'.STAN_FQDN.'/#'.$user->secretKey
                ."\n".'Copiez cette adresse dans votre navigateur afin de vous identifier pour utiliser l\'application d\'inscription'
                ."\n".'Si vous recontrez un problème, envoyez un mail à '.STAN_EMAIL;

            $mail = new Mail();
            $mail->setSender(STAN_EMAIL);
            $mail->addRecipient($user->email);
            $mail->setSubject('Votre compte sur '.STAN_FQDN);
            $mail->textBody($text, 'utf-8');
            $mail->send();
        }
		catch(\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
			throw new \Exception('Erreur lors de l\'envoi de mail');
		}

        return true;
	}

    public static function rename($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'name') || !is_string($params->name)) {
            throw new \InvalidArgumentException('Parameter "name" expected');
        }

        $name = strip_tags($params->name);
        // TODO check unicity ?

        $session->user->nickname = $name;
        $session->user->save();
        Database::getWriter()->commit();
        return true;
    }
}
