<?php

namespace App\AdminModule\presenters;

use App\BaLib\Interfaces\IClientSearch;
use Nette;
use App\Model\ClientsManager;
use App\Model\EmailsManager;
use App\Model\ActionsManager;
use App\Model\TicketsManager;

final class ClientsPresenter extends Nette\Application\UI\Presenter implements IClientSearch
{
    private $clientsManager;
    private $emailsManager;
    private $actionsManager;
    private $ticketsManager;

    public function __construct
    (
        ClientsManager $clientsManager,
        EmailsManager $emailsManager,
        ActionsManager $actionsManager,
        TicketsManager $ticketsManager
    )
    {
        $this->clientsManager = $clientsManager;
        $this->emailsManager = $emailsManager;
        $this->actionsManager = $actionsManager;
        $this->ticketsManager = $ticketsManager;
    }

    public function beforeRender()
    {
        if (!$this->getUser()->isLoggedIn()){
            $this->redirect('Login:default');
        }

        $this->template->title = 'Klienti';
        $this->template->pageClass = str_replace('Admin:', '', $this->getName()) . $this->getAction();
        $this->template->user = $this->getUser()->getIdentity();
    }

    public function setRenderDefault($clientId)
    {
        if (!$this->getUser()->isLoggedIn()){
            $this->redirect('Login:default');
        }

        if ($clientId) {
            $this->template->client = $this->clientsManager->loadClient($clientId);
        } else {
            //$this->template->clients = $this->clientsManager->loadClients();
        }


    }
    public function renderDefault(int $page = 1)
    {
        $clientsCount = $this->clientsManager->getAllClientsCount();

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($clientsCount);
        $paginator->setItemsPerPage(20);
        $paginator->setPage($page);

        $this->template->clients = $this->clientsManager->loadClients($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;

        $this->template->title = 'Klienti > Seznam';
    }

    public function renderDetail($id)
    {
        $this->setRenderDefault($id);
        $this->template->actions = $this->actionsManager->getAllActiveActions();
        $this->template->tickets = $this->ticketsManager->getTicketsByClientId($id);
        $this->template->title = 'Klient > Detail';
    }

    protected function createComponentClientForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->addHidden('id');
        $form->addText('firstName', 'Jméno')
            ->setRequired('Zadejte prosím jméno.');
        $form->addText('lastName', 'Příjmení')
            ->setRequired('Zadejte prosím příjmení.');
        $form->addText('email', 'Email')
            ->setRequired('Zadejte prosím email.')
            ->addRule($form::EMAIL, 'Zadejte prosím platný email.');

        $form->addSubmit('save', 'Uložit');

        if (isset($this->template->client)) {
            $form->setDefaults($this->template->client[0]);
        }

        $form->onSuccess[] = [$this, 'clientFormSucceeded'];
        return $form;
    }

    public function clientFormSucceeded($form, $values)
    {
        if ($this->clientsManager->saveClient($values)) {
            $this->flashMessage('Klient byl úspěšně uložen.', 'success');
            $this->redirect('Clients:detail', $values->id);
        }else{
            $this->flashMessage('Klient se nepodařilo uložit.', 'danger');
        }
    }

    protected function createComponentAddNewClientForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->addText('firstName', 'Jméno')
            ->setRequired('Zadejte prosím jméno.');
        $form->addText('lastName', 'Příjmení')
            ->setRequired('Zadejte prosím příjmení.');
        $form->addText('email', 'Email')
            ->setRequired('Zadejte prosím email.')
            ->addRule($form::EMAIL, 'Zadejte prosím platný email.');

        $form->addSubmit('save', 'Uložit');

        $form->onSuccess[] = [$this, 'addNewClientFormSucceeded'];
        return $form;
    }

    public function addNewClientFormSucceeded($form, $values)
    {
        $insertedId = $this->clientsManager->addNewClient($values);
        if ($insertedId !== false) {
            $this->flashMessage('Klient byl úspěšně uložen.', 'success');
            $this->redirect('Clients:default', $insertedId);
        }else{
            $this->flashMessage('Klient se nepodařilo uložit.', 'danger');
        }
    }

    public function handleSearch($searchedText)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->clientsManager->searchClient($searchedText)));
    }

    public function handleSendEmail($forEmail, $subject, $emailText)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->emailsManager->sendEmail($forEmail, $subject, $emailText)));
    }

    public function handleCreateNewTicket($actionId, $clientId)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->ticketsManager->createNewTicketAjax($actionId, $clientId)));
    }

    public function handleChangeTicketStatus($ticketId)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->ticketsManager->changeTicketStatusAjax($ticketId)));
    }

    public function handleSendEmailWithQrcode($actionName, $clientEmail, $ticketPath)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->emailsManager->sendEmailWithQrcodeAjax($actionName, $clientEmail, $ticketPath)));
    }

    public function handleDeleteTicket($ticketId, $ticketPath)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->ticketsManager->deleteTicketAjax($ticketId, $ticketPath)));
    }
}