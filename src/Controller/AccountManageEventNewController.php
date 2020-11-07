<?php

namespace App\Controller;

use App\Entity\TimeZone;
use App\Service\HistoryWorker\HistoryWorkerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;



class AccountManageEventNewController extends AccountManageController
{


    public function newEvent($account_username,  Request $request,  HistoryWorkerService $historyWorkerService)
    {

        $this->build($account_username);

        // build the form
        $event = new Event();
        $event->setId(Library::GUID());
        $event->setAccount($this->account);

        // TODO : Setting default timezone here is a hack. Probably breaks this form/controller for working with multiple time zones!
        $event->setTimezone($this->account->getAccountLocal()->getDefaultTimezone());

        $timeZone = $this->account->getAccountLocal()->getDefaultTimezone()->getCode();
        if (isset($_POST['event_new']) && isset($_POST['event_new']['timezone'])) {
            $timeZoneObject  = $this->getDoctrine()->getRepository(TimeZone::class)->findOneBy(['id'=>$_POST['event_new']['timezone']]);
            if ($timeZoneObject) {
                $timeZone = $timeZoneObject->getCode();
            }
        }
        $form = $this->createForm(EventNewType::class, $event, array(
            'account' => $this->account,
            'timeZoneName' => $timeZone,
        ));

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Non Mapped Fields
            $event->setStartWithObject($form->get('start_at')->getData());
            $event->setEndWithObject($form->get('end_at')->getData());

            // Save
            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addEvent($event);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            $this->addFlash(
                'success',
                'Event created!'
            );
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_id' => $event->getId() ]);
        }

        return $this->render('account/manage/event/new.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'form' => $form->createView(),
        ]));

    }


}
