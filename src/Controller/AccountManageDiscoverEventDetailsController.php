<?php

namespace App\Controller;

use App\Entity\EventHasSourceEvent;
use App\FilterParams\AccountDiscoverEventListFilterParams;
use App\Service\HistoryWorker\HistoryWorkerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;



class AccountManageDiscoverEventDetailsController extends AccountManageController
{


    /** @var  Account */
    protected $discoverAccount;

    /** @var  Event */
    protected $discoverEvent;

    protected function buildEvent($account_username, $discover_account_id, $discover_event_id) {

        $this->build($account_username);

        $doctrine = $this->getDoctrine();

        $this->discoverAccount = $doctrine->getRepository(Account::class)->findOneBy(array('id'=>$discover_account_id));
        if (!$this->discoverAccount) {
            throw new  NotFoundHttpException('Not found');
        }

        $this->discoverEvent = $doctrine->getRepository(Event::class)->findOneBy(array('account'=>$this->discoverAccount, 'id'=>$discover_event_id));
        if (!$this->discoverEvent) {
            throw new  NotFoundHttpException('Not found');
        }
        if ($this->discoverEvent->getPrivacy() > 0) {
            throw new  NotFoundHttpException('Not found');
        }

    }


    public function indexEventDetails($account_username, $discover_account_id, $discover_event_id, Request $request) {

        $this->buildEvent($account_username, $discover_account_id, $discover_event_id);

        // TODO look up details of existing links, show to user

        return $this->render('account/manage/discover/event/details/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'discoverAccount' => $this->discoverAccount,
            'discoverEvent' => $this->discoverEvent,
        ]));

    }

    public function indexEventAdd($account_username, $discover_account_id, $discover_event_id, HistoryWorkerService $historyWorkerService, Request $request) {

        $this->buildEvent($account_username, $discover_account_id, $discover_event_id);

        // TODO CSFR

        // TODO Check we haven't already added this event

        $event = new Event();
        $event->setAccount($this->account);
        $event->setId(Library::GUID());
        $event->setPrivacy($this->account->getAccountLocal()->getDefaultPrivacy());
        $event->copyFromEvent($this->discoverEvent);

        $eventHasSourceEvent = new EventHasSourceEvent();
        $eventHasSourceEvent->setSourceEvent($this->discoverEvent);
        $eventHasSourceEvent->setEvent($event);

        $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
        $historyWorker->addEvent($event);
        $historyWorker->addEventHasSourceEvent($eventHasSourceEvent);
        $historyWorkerService->persistHistoryWorker($historyWorker);

        // TODO write a record to say we have added this event

        return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_id' => $event->getId() ]);

    }

}