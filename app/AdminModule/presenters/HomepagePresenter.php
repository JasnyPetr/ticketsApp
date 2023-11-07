<?php

namespace App\AdminModule\presenters;

use App\BaLib\Interfaces\IClientSearch;
use App\Model\ClientsManager;
use Nette;
final class HomepagePresenter extends Nette\Application\UI\Presenter implements IClientSearch
{
    private $clientsManager;

    public function __construct
    (
        ClientsManager $clientsManager
    )
    {
        $this->clientsManager = $clientsManager;
    }

    public function beforeRender()
    {
        $this->template->title = 'Home';
        $this->template->pageClass = str_replace('Admin:', '', $this->getName()) . $this->getAction();
        $this->template->user = $this->getUser()->getIdentity();
    }

    public function renderDefault()
    {
        if(!$this->getUser()->isLoggedIn()){
            $this->redirect('Login:default');
        }
    }

    public function handleSearch($searchedText)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->clientsManager->searchClient($searchedText)));
    }
}