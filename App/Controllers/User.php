<?php

namespace App\Controllers;

use App\Auth;
use App\Config;
use App\Model\UserRegister;
use App\Models\Articles;
use App\Utility\Hash;
use App\Utility\Session;
use \Core\View;
use Exception;
use http\Env\Request;
use http\Exception\InvalidArgumentException;

/**
 * User controller
 */
class User extends \Core\Controller
{
    /**
     * Affiche la page de login
     */
    public function loginAction()
    {
        if(isset($_POST['submit'])){
            $f = $_POST;

            // TODO: Validation

            if (!$this->login($f)) {
                View::renderTemplate('User/login.html', [
                    'error' => 'Email ou mot de passe invalide.'
                ]);
                return;
            }

            // Si login OK, redirige vers le compte
            header('Location: /account');
            exit;
        }

        View::renderTemplate('User/login.html');
    }

    /**
     * Page de création de compte
     */
    public function registerAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $f = $_POST;

            if ($f['password'] !== $f['password-check']) {
                View::renderTemplate('User/register.html', [
                    'error' => 'Les mots de passe ne correspondent pas.'
                ]);
                return;
            }

            $userID = $this->register($f);

            if (!$userID || !$this->login($f)) {
                View::renderTemplate('User/register.html', [
                    'error' => 'Impossible de creer le compte.'
                ]);
                return;
            }

            header('Location: /account');
            exit;
        }

        View::renderTemplate('User/register.html');
    }

    /**
     * Affiche la page du compte
     */
    public function accountAction()
    {
        $articles = Articles::getByUser($_SESSION['user']['id']);

        View::renderTemplate('User/account.html', [
            'articles' => $articles
        ]);
    }

    /*
     * Fonction privée pour enregister un utilisateur
     */
    private function register($data)
    {
        try {
            $salt = Hash::generateSalt(32);

            $userID = \App\Models\User::createUser([
                "email" => $data['email'],
                "username" => $data['username'],
                "password" => Hash::generate($data['password'], $salt),
                "salt" => $salt
            ]);

            return $userID;

        } catch (Exception $ex) {
            // TODO : Set flash if error
            /* Utility\Flash::danger($ex->getMessage());*/
            return false;
        }
    }

    private function login($data){
        try {
            if(!isset($data['email'])){
                throw new Exception('TODO');
            }

            $user = \App\Models\User::getByLogin($data['email']);

            if (!$user) {
                return false;
            }

            if (Hash::generate($data['password'], $user['salt']) !== $user['password']) {
                return false;
            }

            // TODO: Create a remember me cookie if the user has selected the option
            // to remained logged in on the login form.
            // https://github.com/andrewdyer/php-mvc-register-login/blob/development/www/app/Model/UserLogin.php#L86

            Auth::login($user, !empty($data['remember_me']));

            return true;

        } catch (Exception $ex) {
            // TODO : Set flash if error
            /* Utility\Flash::danger($ex->getMessage());*/
            return false;
        }
    }


    /**
     * Logout: Delete cookie and session. Returns true if everything is okay,
     * otherwise turns false.
     * @access public
     * @return boolean
     * @since 1.0.2
     */
    public function logoutAction() {
        Auth::logout();

        header ("Location: /");
        exit;

        return true;
    }

}
