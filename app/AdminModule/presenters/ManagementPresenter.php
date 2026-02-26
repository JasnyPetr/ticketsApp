<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use Nette;
use App\BaLib\Interfaces\IClientSearch;
use App\Model\ActionsManager;
use App\Model\ClientsManager;
use App\Model\TicketsManager;

class ManagementPresenter extends Nette\Application\UI\Presenter implements IClientSearch
{
    private $actionsManager;
    private $clientsManager;
    private $ticketsManager;
    public function __construct
    (
        ActionsManager $actionsManager,
        ClientsManager $clientsManager,
        TicketsManager $ticketsManager
    )
    {
        $this->actionsManager = $actionsManager;
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
        $this->template->title = 'Management';
        $this->template->pageClass = str_replace('Admin:', '', $this->getName()) . $this->getAction();
        $this->template->user = $this->getUser()->getIdentity();
    }

    public function renderActions(int $page = 1)
    {
        $actionsCount = $this->actionsManager->getAllActionsCount();

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($actionsCount);
        $paginator->setItemsPerPage(20);
        $paginator->setPage($page);

        $this->template->actions = $this->actionsManager->loadActions($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;

        $this->template->title = 'Management > akce';
    }

    protected function createComponentAddNewActionForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->addText('name', 'Název akce')
            ->setRequired('Zadejte název akce');
        $form->addDate('dateEvent', 'Datum akce')
            ->setRequired('Zadejte datum akce');
        $form->addCheckbox('isActive', 'Aktivní')
            ->setDefaultValue(true);
        $form->addSubmit('addNewAction', 'Vytvořit akci');
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');
        $form->onSuccess[] = [$this, 'addNewActionFormSucceeded'];
        return $form;
    }

    public function addNewActionFormSucceeded($form, $values)
    {
        $insertedId = $this->actionsManager->addNewAction($values);
        if ($insertedId !== false) {
            $this->flashMessage('Akce byla úspěšně vytvořena', 'success');
            $this->redirect('this');
        } else {
            $this->flashMessage('Akci se nepodařilo vytvořit', 'error');
            $this->redirect('this');
        }
    }

    public function renderDefault()
    {

    }
    public function handleSearch($searchedText)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->clientsManager->searchClient($searchedText)));
    }

    public function handleGetActionDetail($actionId)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->ticketsManager->getActionDetailAjax($actionId)));
    }

    public function handleChangeTicketStatus($ticketId)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->ticketsManager->changeTicketStatusAjax($ticketId)));
    }

    public function handleChangeActionStatus($actionId, $isChangeStatusTickets)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->actionsManager->changeActionStatusAjax($actionId, $isChangeStatusTickets)));
    }

    public function handleDeleteAction($actionId)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->actionsManager->deleteActionAjax($actionId)));
    }

    public function handleSaveEditedAction($actionId, $actionName, $dateEvent, $isActive)
    {
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($this->actionsManager->saveEditedActionAjax($actionId, $actionName, $dateEvent, $isActive)));
    }
}