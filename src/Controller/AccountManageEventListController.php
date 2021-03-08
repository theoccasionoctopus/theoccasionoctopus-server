<?php

namespace App\Controller;

use App\Entity\EventOccurrence;
use App\Exception\AccessDeniedRedirectToPublicURLIfPossibleException;
use App\FilterParams\EventListFilterParams;
use App\RepositoryQuery\EventRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;

class AccountManageEventListController extends AccountManageController
{
    public function indexManageEvent($account_username, Request $request)
    {
        try {
            $this->setUpAccountManage($account_username, $request);
        } catch (AccessDeniedRedirectToPublicURLIfPossibleException $e) {
            return $this->redirectToRoute('account_public_event', ['account_username' => $this->account->getUsername() ]);
        }

        $params = new EventListFilterParams($this->getDoctrine(), $this->account);
        $params->build($request->query);

        $eventOccurrences = $params->getRepositoryQuery()->getEventOccurrences();

        return $this->render('account/manage/event/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'eventListFilterParams'=>$params,
            'eventOccurrences' => $eventOccurrences,
        ]));
    }


    public function calendar($account_username, Request $request)
    {
        try {
            $this->setUpAccountManage($account_username, $request);
        } catch (AccessDeniedRedirectToPublicURLIfPossibleException $e) {
            return $this->redirectToRoute('account_public_event_calendar', ['account_username' => $this->account->getUsername() ]);
        }

        // TODO use EventListFilterParams

        return $this->render('account/manage/event/calendar.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'now' =>  new \DateTime(),
        ]));
    }

    public function calendarData($account_username, Request $request)
    {
        $this->setUpAccountManage($account_username, $request);

        $from = new \DateTime($request->query->get('start'));
        $from->setTime(0, 0, 0);
        $to = new \DateTime($request->query->get('end'));
        $to->setTime(23, 59, 59);

        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        $repositoryQuery->setFrom($from);
        $repositoryQuery->setTo($to);
        $repositoryQuery->setShowDeleted(false);

        $eventOccurrences = $repositoryQuery->getEventOccurrences();

        $data = [];
        /** @var EventOccurrence $event */
        foreach ($eventOccurrences as $eventOccurrence) {
            $data[] = array(
                // TODO need to find a way to link to the right event occurrence - at moment will always link to first event occurrence
                'id'=>$eventOccurrence->getEvent()->getId(),
                'title'=> $eventOccurrence->getEvent()->getTitle(),
                'start'=> $eventOccurrence->getStart('UTC')->format('Y-m-d'),
                'end'=> $eventOccurrence->getEnd('UTC')->format('Y-m-d'),
            );
        }

        return $this->json($data);
    }
}
