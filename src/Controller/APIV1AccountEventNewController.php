<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\EventHasTag;
use App\Entity\Tag;
use App\Service\HistoryWorker\HistoryWorkerService;
use App\Library;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;

class APIV1AccountEventNewController extends APIV1AccountController
{
    public function newJSON($account_id, Request $request, HistoryWorkerService $historyWorkerService)
    {
        $this->buildAccount($account_id, $request);

        if (!$this->account_permission_write) {
            throw new AccessDeniedHttpException('This Token Can Not Write');
        }

        $doctrine = $this->getDoctrine();

        $event = new Event();
        $event->setAccount($this->account);
        $event->setNewIdAndSlug();
        $event->setPrivacy($this->account->getAccountLocal()->getDefaultPrivacy());
        $event->setCountry($this->account->getAccountLocal()->getDefaultCountry());
        $event->setTimezone($this->account->getAccountLocal()->getDefaultTimezone());

        $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->accessToken->getUser());
        $historyWorker->addEvent($event);

        if ($request->get('title')) {
            $event->setTitle($request->get('title'));
        }
        if ($request->get('description')) {
            $event->setDescription($request->get('description'));
        }
        if ($request->get('url')) {
            $event->setUrl($request->get('url'));
        }
        if ($request->get('url_tickets')) {
            $event->setUrlTickets($request->get('url_tickets'));
        }

        if ($request->get('all_day')) {
            $event->setStartWithInts(
                $request->get('start_year'),
                $request->get('start_month'),
                $request->get('start_day'),
                null,
                null,
                null
            );
            $event->setEndWithInts(
                $request->get('end_year'),
                $request->get('end_month'),
                $request->get('end_day'),
                null,
                null,
                null
            );
        } else {
            if ($request->get('start_year_timezone')) {
                $start = new \DateTime('', $event->getTimezone()->getDateTimeZoneObject());
                $start->setDate(
                    $request->get('start_year_timezone'),
                    $request->get('start_month_timezone'),
                    $request->get('start_day_timezone')
                );
                $start->setTime(
                    $request->get('start_hour_timezone'),
                    $request->get('start_minute_timezone'),
                    0
                );
                $event->setStartWithObject($start);
            } elseif ($request->get('start_year_utc')) {
                $start = new \DateTime('', new \DateTimeZone('UTC'));
                $start->setDate(
                    $request->get('start_year_utc'),
                    $request->get('start_month_utc'),
                    $request->get('start_day_utc')
                );
                $start->setTime(
                    $request->get('start_hour_utc'),
                    $request->get('start_minute_utc'),
                    0
                );
                $event->setStartWithObject($start);
            }

            if ($request->get('end_year_timezone')) {
                $end = new \DateTime('', $event->getTimezone()->getDateTimeZoneObject());
                $end->setDate(
                    $request->get('end_year_timezone'),
                    $request->get('end_month_timezone'),
                    $request->get('end_day_timezone')
                );
                $end->setTime(
                    $request->get('end_hour_timezone'),
                    $request->get('end_minute_timezone'),
                    0
                );
                $event->setEndWithObject($end);
            } elseif ($request->get('end_year_utc')) {
                $end = new \DateTime('', new \DateTimeZone('UTC'));
                $end->setDate(
                    $request->get('end_year_utc'),
                    $request->get('end_month_utc'),
                    $request->get('end_day_utc')
                );
                $end->setTime(
                    $request->get('end_hour_utc'),
                    $request->get('end_minute_utc'),
                    0
                );
                $event->setEndWithObject($end);
            }
        }

        if ($request->get('add_tag_0')) {
            $tagRepository = $this->getDoctrine()->getRepository(Tag::class);
            $count = 0;
            while ($request->request->get('add_tag_' . $count)) {
                $tag = $tagRepository->findOneBy(['id' => $request->request->get('add_tag_' . $count), 'account' => $this->account]);
                if ($tag) {
                    $eventTag = new EventHasTag();
                    $eventTag->setEvent($event);
                    $eventTag->setTag($tag);
                    $eventTag->setEnabled(true);
                    $historyWorker->addEventHasTag($eventTag);
                } else {
                    // TODO should show this 404 to user somehow?
                }
                $count++;
            }
        }

        // TODO start & end are required fields - make sure they are set
        // TODO check years are in range

        $count = 0;
        while ($request->request->get('extra_field_'.$count.'_name')) {
            $event->setExtraField($request->request->get('extra_field_'.$count.'_name'), $request->request->get('extra_field_'.$count.'_value'));
            $count++;
        }

        $historyWorkerService->persistHistoryWorker($historyWorker);

        $out = [
            'event' => [
                'id' => $event->getId(),
            ]
        ];

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }
}
