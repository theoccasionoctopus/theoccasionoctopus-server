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

        $params->getRepositoryQuery()->setPublicOnly();
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

        return $this->render('account/public/event/calendar.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'now' =>  new \DateTime(),
        ]));

    }

    public function calendarData($account_username, Request $request)
    {

        $this->setUpAccountPublic($account_username, $request);

        $from = new \DateTime($request->query->get('start'));
        $from->setTime(0,0,0);
        $to = new \DateTime($request->query->get('end'));
        $to->setTime(23, 59, 59);

        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        $repositoryQuery->setPublicOnly();
        $repositoryQuery->setFrom($from);
        $repositoryQuery->setTo($to);

        $events = $repositoryQuery->getEvents();

        $data = [];
        /** @var Event $event */
        foreach($events as $event) {
            $data[] = array(
                'id'=>$event->getId(),
                'title'=> $event->getTitle(),
                'start'=> $event->getStart('UTC')->format('Y-m-d'),
                'end'=> $event->getEnd('UTC')->format('Y-m-d'),
            );
        }

        return $this->json($data);

    }


}
