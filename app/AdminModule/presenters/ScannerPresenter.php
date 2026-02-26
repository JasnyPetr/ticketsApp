<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use App\BaLib\Interfaces\IClientSearch;
use App\Model\ClientsManager;
use App\Model\TicketsManager;
use Nette;

class ScannerPresenter extends Nette\Application\UI\Presenter implements IClientSearch
{
    private $clientsManager;
    private $ticketsManager;
    public function __construct
    (
        ClientsManager $clientsManager,
        TicketsManager $ticketsManager
    )
    {
        $this->clientsManager = $clientsManager;
        $this->ticketsManager = $ticketsManager;
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
        $this->template->title = 'Scanner';
        $this->template->pageClass = str_replace('Admin:', '', $this->getName()) . $this->getAction();
        $this->template->user = $this->getUser()->getIdentity();
    }

    public function renderDefault()
    {
        $this->template->title = 'Scanner';
    }

    public function handleSearch($searchedText)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->clientsManager->searchClient($searchedText)));
    }

    public function handleAnalyzeLoadedCode($qrcode)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->ticketsManager->analyzeLoadedCodeAjax($qrcode)));
    }

    public function handleDecideOfTicket($qrcode)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->ticketsManager->decideOfTicketAjax($qrcode)));
    }
}