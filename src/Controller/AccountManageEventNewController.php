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
    public function newEvent($account_username, Request $request, HistoryWorkerService $historyWorkerService)
    {
        $this->setUpAccountManage($account_username, $request);

        // build the form
        $event = new Event();
        $event->setNewIdAndSlug();
        $event->setAccount($this->account);
        $event->setTimezone($this->account->getAccountLocal()->getDefaultTimezone());

        $form = $this->createForm(EventNewType::class, $event, array(
            'account' => $this->account,
        ));

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Non Mapped Fields
            $startDate = $form->get('start_date')->getData();
            $endDate = $form->get('end_date')->getData();
            if ($form->get('all_day')->getData()) {
                $event->setStartWithInts(
                    $startDate['year'],
                    $startDate['month'],
                    $startDate['day'],
                    null,
                    null,
                    null
                );
                $event->setEndWithInts(
                    $endDate['year'],
                    $endDate['month'],
                    $endDate['day'],
                    null,
                    null,
                    null
                );
            } else {
                $startTime = $form->get('start_time')->getData();
                $endTime = $form->get('end_time')->getData();
                $event->setStartWithInts(
                    $startDate['year'],
                    $startDate['month'],
                    $startDate['day'],
                    $startTime['hour'],
                    $startTime['minute'],
                    $startTime['second']
                );
                $event->setEndWithInts(
                    $endDate['year'],
                    $endDate['month'],
                    $endDate['day'],
                    $endTime['hour'],
                    $endTime['minute'],
                    $endTime['second']
                );
            }

            // Save
            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addEvent($event);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            $this->addFlash(
                'success',
                'Event created!'
            );
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_slug' => $event->getSlug() ]);
        }

        return $this->render('account/manage/event/new.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'form' => $form->createView(),
        ]));
    }
}
