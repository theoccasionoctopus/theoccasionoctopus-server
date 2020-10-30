<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
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

class APIV1AccountEventListController extends APIV1AccountController {


    public function listICAL($account_id, Request $request)
    {

        $this->buildAccount($account_id, $request);

        $builder = new ICalBuilderForAccount($this->account, $this->container);
        $out = $builder->getStart();

        # Set up search with security filters
        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        if (!$this->account_permission_read_private) {
            $repositoryQuery->setPublicOnly();
        }

        # set up custom filters
        if ($request->query->get('tag')) {
            $tagRepository = $this->getDoctrine()->getRepository(Tag::class);
            $tag = $tagRepository->findOneBy(['id'=>$request->query->get('tag'), 'account'=>$this->account]);
            if ($tag) {
                $repositoryQuery->setTag($tag);
            }
        }

        # Get events, output
        $events = $repositoryQuery->getEvents();

        foreach($events as $event) {
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

        $out = array(
            'events'=>array(),
        );

        # Set up search with security filters
        $repositoryQuery = new EventRepositoryQuery($this->getDoctrine());
        $repositoryQuery->setAccountEvents($this->account);
        if (!$this->account_permission_read_private) {
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

        # Get events, output
        $events = $repositoryQuery->getEvents();

        /** @var Event $event */
        foreach($events as $event) {
            $out['events'][] = array(
                'id'=> $event->getId(),
                'title'=>$event->getTitle(),
                'description'=>$event->getDescription(),
                'url'=>$event->getUrl(),
                'url_tickets'=>$event->getUrlTickets(),
                'timezone'=>array(
                    'code'=>$event->getTimezone()->getCode(),
                ),
                'country'=>array(
                    // TODO better name for this that says what the code actually is!
                    'code'=>$event->getCountry()->getIso3166TwoChar(),
                ),
                'start_epoch'=>$event->getStartAtTimeZone()->getTimestamp(),
                'start_utc'=>Library::getAPIJSONResponseForDateTime($event->getStart('UTC')),
                'start_timezone'=>Library::getAPIJSONResponseForDateTime($event->getStartAtTimeZone()),
                'end_epoch'=>$event->getEndAtTimeZone()->getTimestamp(),
                'end_utc'=>Library::getAPIJSONResponseForDateTime($event->getEnd('UTC')),
                'end_timezone'=>Library::getAPIJSONResponseForDateTime($event->getEndAtTimeZone()),
                'privacy'=>($event->getPrivacy() == 0 ? 'public' : 'private'),
                'extra_fields'=>($event->getExtraFields() ? $event->getExtraFields() : new stdClass()),
            );
        }

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );


    }


}
