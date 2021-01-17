<?php

namespace App\Controller;

use App\Entity\Tag;
use App\FilterParams\EventListFilterParams;
use App\Library;
use App\RepositoryQuery\EventRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;

class AccountPublicEventListController extends AccountPublicController
{
    public function index($account_username, Request $request)
    {
        $this->setUpAccountPublic($account_username, $request);


        $params = new EventListFilterParams($this->getDoctrine(), $this->account);
        $params->build($request->query);

        if ($this->account_permission_read_only_followers) {
            $params->getRepositoryQuery()->setPrivacyLevelOnlyFollowers();
        } else {
            $params->getRepositoryQuery()->setPublicOnly();
        }
        $eventOccurrences = $params->getRepositoryQuery()->getEventOccurrences();

        return $this->render('account/public/event/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'eventOccurrences' => $eventOccurrences,
            'eventListFilterParams'=>$params,
        ]));
    }

    public function calendar($account_username, Request $request)
    {
        $this->setUpAccountPublic($account_username, $request);

        // TODO use EventListFilterParams

        return $this->render('account/public/event/calendar.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'now' =>  new \DateTime(),
        ]));
    }

    public function calendarData($account_username, Request $request)
    {
        $this->setUpAccountPublic($account_username, $request);

        $from = new \DateTime($request->query->get('start'));
        $from->setTime(0, 0, 0);
        $to = new \DateTime($request->query->get('end'));
        $to->setTime(23, 59, 59);

        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        if ($this->account_permission_read_only_followers) {
            $repositoryQuery->setPrivacyLevelOnlyFollowers();
        } else {
            $repositoryQuery->setPublicOnly();
        }
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
