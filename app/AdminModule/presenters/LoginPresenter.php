<?php

namespace App\AdminModule\presenters;

use Nette;
use App\Model\UsersManager;

class LoginPresenter extends \Nette\Application\UI\Presenter
{
    private $usersManager;

    public function __construct
    (
        UsersManager $usersManager
    )
    {
        $this->usersManager = $usersManager;
    }

    public function beforeRender()
    {
        $this->template->title = 'Přihlášení';
    }

    protected function createComponentLoginForm()
    {
        $form = new \Nette\Application\UI\Form;
        $form->addText('username', 'Uživatelské jméno')
            ->setRequired('Zadejte uživatelské jméno.');
        $form->addPassword('password', 'Heslo')
            ->setRequired('Zadejte heslo.');
        $form->addSubmit('login', 'Přihlásit se');
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        return $form;
    }

    public function loginFormSucceeded($form, $values)
    {
        try {
            //$this->usersManager->loginUser($values->username, $values->password);
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:default');
        } catch (Nette\Security\AuthenticationException $e) {
            $form['password']->addError('Nesprávné uživatelské jméno nebo heslo.');
            $this->redrawControl('loginFormSnippet');
        }
    }

    public function renderDefault()
    {

    }

    public function renderLogout()
    {
        $this->getUser()->logout();
        $this->redirect('Login:default');
    }
}