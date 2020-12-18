<?php

namespace App\Controller;

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
        $this->build($account_username);


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
        $this->build($account_username);

        // TODO use EventListFilterParams

        return $this->render('account/manage/event/calendar.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'now' =>  new \DateTime(),
        ]));
    }

    public function calendarData($account_username, Request $request)
    {
        $this->build($account_username);

        $from = new \DateTime($request->query->get('start'));
        $from->setTime(0, 0, 0);
        $to = new \DateTime($request->query->get('end'));
        $to->setTime(23, 59, 59);

        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        $repositoryQuery->setFrom($from);
        $repositoryQuery->setTo($to);
        $repositoryQuery->setShowDeleted(false);

        $events = $repositoryQuery->getEvents();

        $data = [];
        /** @var Event $event */
        foreach ($events as $event) {
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
