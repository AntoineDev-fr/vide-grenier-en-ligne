<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Utility\Upload;
use \Core\View;

/**
 * Product controller
 */
class Product extends \Core\Controller
{
    const CONTACT_SUCCESS_MESSAGE = 'Votre message a bien ete valide.';

    /**
     * Affiche la page d'ajout
     * @return void
     */
    public function indexAction()
    {

        if(isset($_POST['submit'])) {

            try {
                $f = $_POST;

                // TODO: Validation

                $f['user_id'] = $_SESSION['user']['id'];
                $id = Articles::save($f);

                $pictureName = Upload::uploadFile($_FILES['picture'], $id);

                Articles::attachPicture($id, $pictureName);

                header('Location: /product/' . $id);
            } catch (\Exception $e){
                    var_dump($e);
            }
        }

        View::renderTemplate('Product/Add.html');
    }

    /**
     * Affiche la page d'un produit
     * @return void
     */
    public function showAction()
    {
        $id = $this->route_params['id'];
        $contactForm = $this->getDefaultContactFormData();
        $contactErrors = [];
        $contactSuccess = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
            $contactForm = $this->getContactFormData($_POST);
            $contactErrors = $this->validateContactForm($contactForm);

            if (empty($contactErrors)) {
                $contactSuccess = self::CONTACT_SUCCESS_MESSAGE;
                $contactForm = $this->getDefaultContactFormData();
            }
        }

        try {
            $suggestions = Articles::getSuggest();
            $article = Articles::getOne($id);

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                Articles::addOneView($id);
                if (!empty($article)) {
                    $article[0]['views']++;
                }
            }
        } catch(\Exception $e){
            var_dump($e);
        }

        View::renderTemplate('Product/Show.html', [
            'article' => $article[0],
            'suggestions' => $suggestions,
            'contactForm' => $contactForm,
            'contactErrors' => $contactErrors,
            'contactSuccess' => $contactSuccess
        ]);
    }

    private function getDefaultContactFormData()
    {
        return [
            'name' => isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : '',
            'email' => '',
            'message' => ''
        ];
    }

    private function getContactFormData($data)
    {
        return [
            'name' => trim($data['contact_name'] ?? ''),
            'email' => trim($data['contact_email'] ?? ''),
            'message' => trim($data['contact_message'] ?? '')
        ];
    }

    private function validateContactForm($data)
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'Le nom est obligatoire.';
        }

        if ($data['email'] === '') {
            $errors[] = "L'email est obligatoire.";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email est invalide.";
        }

        if ($data['message'] === '') {
            $errors[] = 'Le message est obligatoire.';
        }

        return $errors;
    }
}
