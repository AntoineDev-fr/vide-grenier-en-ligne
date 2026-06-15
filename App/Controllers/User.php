<?php

namespace App\Controllers;

use App\Auth;
use App\Repositories\UserRepository;
use App\Services\AuthenticationService;
use App\Services\RegistrationService;
use App\Models\Articles;
use \Core\View;
use DomainException;
use Exception;
use InvalidArgumentException;

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

            try {
                $userID = $this->register($f);
            } catch (InvalidArgumentException | DomainException $ex) {
                View::renderTemplate('User/register.html', [
                    'error' => $ex->getMessage()
                ]);
                return;
            }

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
            return $this->getRegistrationService()->register($data);

        } catch (Exception $ex) {
            if ($ex instanceof InvalidArgumentException || $ex instanceof DomainException) {
                throw $ex;
            }

            return false;
        }
    }

    private function login($data){
        try {
            return $this->getAuthenticationService()->attempt($data);

        } catch (Exception $ex) {
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

    private function getAuthenticationService(): AuthenticationService
    {
        return new AuthenticationService(new UserRepository());
    }

    private function getRegistrationService(): RegistrationService
    {
        return new RegistrationService(new UserRepository());
    }

}
