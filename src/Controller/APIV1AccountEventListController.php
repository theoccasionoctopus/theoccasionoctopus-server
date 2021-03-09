<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\EventOccurrence;
use App\Entity\Tag;
use App\Service\HistoryWorker\HistoryWorkerService;
use App\Library;
use App\RepositoryQuery\EventRepositoryQuery;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;

class APIV1AccountEventListController extends APIV1AccountController
{
    protected function getRepositoryQuery(Request $request): EventRepositoryQuery
    {
        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        if ($this->account_permission_read_private) {
            // Great
        } elseif ($this->account_permission_read_only_followers) {
            $repositoryQuery->setPrivacyLevelOnlyFollowers();
        } else {
            $repositoryQuery->setPublicOnly();
        }

        # set up custom filters
        if ($request->query->get('url')) {
            $repositoryQuery->setUrl($request->query->get('url'));
        }
        if ($request->query->get('tag')) {
            $tagRepository = $this->getDoctrine()->getRepository(Tag::class);
            $tag = $tagRepository->findOneBy(['id'=>$request->query->get('tag'), 'account'=>$this->account]);
            if ($tag) {
                $repositoryQuery->setTag($tag);
            }
        }

        return $repositoryQuery;
    }

    public function listICAL($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);

        $builder = new ICalBuilderForAccount($this->account, $this->container);
        $out = $builder->getStart();

        $repositoryQuery = $this->getRepositoryQuery($request);
        $events = $repositoryQuery->getEvents();

        foreach ($events as $event) {
            $out .= $builder->getEvent($event);
        }

        $out .= $builder->getEnd();

        return new Response(
            $out,
            Response::HTTP_OK,
            ['content-type' => 'text/calendar']
        );
    }


    public function listJSON($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);

        $repositoryQuery = $this->getRepositoryQuery($request);
        $events = $repositoryQuery->getEvents();

        $out = array(
            'events'=>array(),
        );

        /** @var Event $event */
        foreach ($events as $event) {
            $eventJSON = array(
                'id'=> $event->getId(),
                'title'=>$event->getTitle(),
                'description'=>$event->getDescription(),
                'url'=>$event->getUrl(),
                'url_tickets'=>$event->getUrlTickets(),
                'deleted'=>$event->getDeleted(),
                'cancelled'=>$event->getCancelled(),
                'rrule'=>$event->getRrule(),
                'timezone'=>array(
                    'code'=>$event->getTimezone()->getCode(),
                ),
                'country'=>array(
                    // TODO better name for this that says what the code actually is!
                    'code'=>$event->getCountry()->getIso3166TwoChar(),
                ),
                'privacy'=>$this->privacyLevelToAPIString($event->getPrivacy()),
                'extra_fields'=>($event->getExtraFields() ? $event->getExtraFields() : new stdClass()),
            );
            $eventJSON = array_merge($eventJSON, Library::getAPIJSONResponseForObject($event));
            $out['events'][] = $eventJSON;
        }

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    public function listOccurrencesJSON($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);

        $repositoryQuery = $this->getRepositoryQuery($request);
        $eventOccurrences = $repositoryQuery->getEventOccurrences();

        $out = array(
            'events'=>array(),
        );

        /** @var EventOccurrence $eventOccurrence */
        foreach ($eventOccurrences as $eventOccurrence) {
            /** @var Event $event */
            $event = $eventOccurrence->getEvent();
            $eventJSON = array(
                'event_id'=> $eventOccurrence->getEvent()->getId(),
                'occurrence_id'=> $eventOccurrence->getId(),
                'title'=>$event->getTitle(),
                'description'=>$event->getDescription(),
                'url'=>$event->getUrl(),
                'url_tickets'=>$event->getUrlTickets(),
                'deleted'=>$event->getDeleted(),
                'cancelled'=>$event->getCancelled(),
                'timezone'=>array(
                    'code'=>$event->getTimezone()->getCode(),
                ),
                'country'=>array(
                    // TODO better name for this that says what the code actually is!
                    'code'=>$event->getCountry()->getIso3166TwoChar(),
                ),
                'privacy'=>$this->privacyLevelToAPIString($event->getPrivacy()),
                'extra_fields'=>($event->getExtraFields() ? $event->getExtraFields() : new stdClass()),
            );
            $eventJSON = array_merge($eventJSON, Library::getAPIJSONResponseForObject($eventOccurrence));
            $out['events'][] = $eventJSON;
        }

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }
}
