<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use App\Model\UsersManager;
use Nette;

class RegisterPresenter extends \Nette\Application\UI\Presenter
{
    private $usersManager;

    public function __construct
    (
        UsersManager $usersManager
    )
    {
        $this->usersManager = $usersManager;
    }

    public function startup(): void
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Login:default');
        }
    }

    public function beforeRender()
    {
        $this->template->title = 'Registrace';
    }

    protected function createComponentRegisterForm()
    {
        $form = new \Nette\Application\UI\Form;
        $form->addText('first_name', 'Jméno')
            ->setRequired('Zadejte jméno.');
        $form->addText('last_name', 'Příjmení')
            ->setRequired('Zadejte příjmení.');
        $form->addText('nick_name', 'Přezdívka')
            ->setRequired('Zadejte přezdívku.');
        $form->addText('username', 'Uživatelské jméno')
            ->setRequired('Zadejte uživatelské jméno.');
        $form->addPassword('password', 'Heslo')
            ->setRequired('Zadejte heslo.');
        $form->addPassword('password_confirm', 'Potvrzení hesla')
            ->setRequired('Zadejte heslo znovu.');
        $form->addSubmit('register', 'Registrovat se');
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');

        $form->onValidate[] = [$this, 'registerFormValidate'];
        $form->onSuccess[] = [$this, 'registerFormSucceeded'];

        return $form;
    }

    public function registerFormValidate($form, $values)
    {
        if ($values->first_name == '') {
            $form['first_name']->addError('Zadejte jméno.');
            $this->redrawControl('registerFormSnippet');
        }
        if ($values->last_name == '') {
            $form['last_name']->addError('Zadejte příjmení.');
            $this->redrawControl('registerFormSnippet');
        }
        if ($values->nick_name == '') {
            $form['nick_name']->addError('Zadejte přezdívku.');
            $this->redrawControl('registerFormSnippet');
        }
        if ($values->username == '') {
            $form['username']->addError('Zadejte uživatelské jméno.');
            $this->redrawControl('registerFormSnippet');
        }
        if (!$this->usersManager->checkUsernameUnique($values->username)) {
            $form['username']->addError('Uživatelské jméno je již zabrané.');
            $this->redrawControl('registerFormSnippet');
        }
        if ($values->password !== $values->password_confirm) {
            $form['password_confirm']->addError('Hesla se neshodují.');
            $this->redrawControl('registerFormSnippet');
        }
    }

    public function registerFormSucceeded($form, $values)
    {
        if ($this->usersManager->registerUser($values)) {
            $this->flashMessage('Registrace proběhla úspěšně.', 'success');
            $this->redirect('Login:default');
        } else {
            $this->flashMessage('Registrace se nezdařila.', 'danger');
            $this->redirect('Register:default');
        }
    }

    public function renderDefault()
    {

    }
}