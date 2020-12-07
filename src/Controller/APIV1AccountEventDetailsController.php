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

class APIV1AccountEventDetailsController extends APIV1AccountController
{


    /** @var  Event */
    protected $event;

    protected function buildEvent($account_id, $event_id, Request $request) {

        $this->buildAccount($account_id, $request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Event::class);

        $this->event = $repository->findOneBy(array('account'=>$this->account, 'id'=>$event_id));
        if (!$this->event) {
            throw new  NotFoundHttpException('Not found');
        }
        if (!$this->account_permission_read_private && $this->event->getPrivacy() > 0) {
            throw new  NotFoundHttpException('Not found');
        }

    }


    public function showJSON($account_id, $event_id, Request $request) {
        $this->buildEvent($account_id, $event_id, $request);

        $out['event'] = array(
            'id'=> $this->event->getId(),
            'title'=>$this->event->getTitle(),
            'description'=>$this->event->getDescription(),
            'url'=>$this->event->getUrl(),
            'url_tickets'=>$this->event->getUrlTickets(),
            'timezone'=>array(
                'code'=>$this->event->getTimezone()->getCode(),
            ),
            'country'=>array(
                'code'=>$this->event->getCountry()->getIso3166TwoChar(),
            ),
            'start_epoch'=>$this->event->getStartAtTimeZone()->getTimestamp(),
            'start_utc'=>Library::getAPIJSONResponseForDateTime($this->event->getStart('UTC')),
            'start_timezone'=>Library::getAPIJSONResponseForDateTime($this->event->getStartAtTimeZone()),
            'end_epoch'=>$this->event->getEndAtTimeZone()->getTimestamp(),
            'end_utc'=>Library::getAPIJSONResponseForDateTime($this->event->getEnd('UTC')),
            'end_timezone'=>Library::getAPIJSONResponseForDateTime($this->event->getEndAtTimeZone()),
            'privacy'=>($this->event->getPrivacy() == 0 ? 'public' : 'private'),
            'extra_fields'=>($this->event->getExtraFields() ? $this->event->getExtraFields() : new stdClass()),
            'editable_mode'=>$this->event->getEditableFieldsMode(),
            'editable_fields'=>$this->event->getEditableFieldsList(),
        );

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
        
    }

    public function editJSON($account_id, $event_id, Request $request, HistoryWorkerService $historyWorkerService) {
        $this->buildEvent($account_id, $event_id, $request);

        $doctrine = $this->getDoctrine();
        $tagRepository = $this->getDoctrine()->getRepository(Tag::class);
        $eventTagRepository = $doctrine->getRepository(EventHasTag::class);

        if (!$this->account_permission_write) {
            throw new AccessDeniedHttpException('This Token Can Not Write');
        }

        $editableFields = $this->event->getEditableFieldsList();
        $errorFieldsTriedToEditThatWereNotAllowed = [];

        ############# Set Changes!

        $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->accessToken->getUser());
        $changedEvent = false;

        if ($request->get('title') && $request->get('title') != $this->event->getTitle()) {
            if (in_array('title',$editableFields)) {
                $this->event->setTitle($request->get('title'));
                $changedEvent = true;
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'title';
            }
        }
        if ($request->get('description') && $request->get('description') != $this->event->getDescription()) {
            if (in_array('description',$editableFields)) {
                $this->event->setDescription($request->get('description'));
                $changedEvent = true;
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'description';
            }
        }
        if ($request->get('url') && $request->get('url') != $this->event->getUrl()) {
            if (in_array('url',$editableFields)) {
                $this->event->setUrl($request->get('url'));
                $changedEvent = true;
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'url';
            }
        }
        if ($request->get('url_tickets') && $request->get('url_tickets') != $this->event->getUrlTickets()) {
            if (in_array('url_tickets',$editableFields)) {
                $this->event->setUrlTickets($request->get('url_tickets'));
                $changedEvent = true;
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'url_tickets';
            }
        }

        if ($request->get('start_year_utc')) {
            if (in_array('start_end',$editableFields)) {
                $start = new \DateTime('', new \DateTimeZone('UTC'));
                $start->setDate(
                    $request->get('start_year_utc', $start->format('Y')),
                    $request->get('start_month_utc', $start->format('n')),
                    $request->get('start_day_utc', $start->format('j'))
                );
                $start->setTime(
                    $request->get('start_hour_utc', $start->format('G')),
                    $request->get('start_minute_utc', $start->format('i')),
                    0
                );
                if ($this->event->setStartWithObject($start)) {
                    $changedEvent = true;
                }
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'start_at';
            }
        }

        if ($request->get('end_year_utc')) {
            if (in_array('start_end',$editableFields)) {
                $end = new \DateTime('', new \DateTimeZone('UTC'));
                $end->setDate(
                    $request->get('end_year_utc', $end->format('Y')),
                    $request->get('end_month_utc', $end->format('n')),
                    $request->get('end_day_utc', $end->format('j'))
                );
                $end->setTime(
                    $request->get('end_hour_utc', $end->format('G')),
                    $request->get('end_minute_utc', $end->format('i')),
                    0
                );
                if ($this->event->setEndWithObject($end)) {
                    $changedEvent = true;
                }
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'end_at';
            }
        }

        if ($request->get('start_year_timezone')) {
            if (in_array('start_end',$editableFields)) {
                $start = new \DateTime('', $this->event->getTimezone()->getDateTimeZoneObject());
                if ($this->event->setStartWithInts(
                    $request->get('start_year_timezone', $start->format('Y')),
                    $request->get('start_month_timezone', $start->format('n')),
                    $request->get('start_day_timezone', $start->format('j')),
                    $request->get('start_hour_timezone', $start->format('G')),
                    $request->get('start_minute_timezone', $start->format('i')),
                    0
                )) {
                    $changedEvent = true;
                };
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'start_at';
            }
        }

        if ($request->get('end_year_timezone')) {
            if (in_array('start_end',$editableFields)) {
                $end = new \DateTime('', $this->event->getTimezone()->getDateTimeZoneObject());
                if ($this->event->setEndWithInts(
                    $request->get('end_year_timezone', $end->format('Y')),
                    $request->get('end_month_timezone', $end->format('n')),
                    $request->get('end_day_timezone', $end->format('j')),
                    $request->get('end_hour_timezone', $end->format('G')),
                    $request->get('end_minute_timezone', $end->format('i')),
                    0
                )) {
                    $changedEvent = true;
                }
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'end_at';
            }
        }

        if ($request->get('extra_field_0_name')) {
            if (in_array('extra_fields',$editableFields)) {
                $count = 0;
                while ($request->get('extra_field_' . $count . '_name')) {
                    if ($this->event->getExtraField($request->get('extra_field_' . $count . '_name')) != $request->get('extra_field_' . $count . '_value')) {
                        $this->event->setExtraField($request->get('extra_field_' . $count . '_name'), $request->get('extra_field_' . $count . '_value'));
                        $changedEvent = true;
                    }
                    $count++;
                }
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'extra_fields';
            }
        }

        $deleted = $this::parseBooleanString($request->get('deleted'));
        if (!is_null($deleted) && $deleted != $this->event->getDeleted()) {
            if (in_array('deleted',$editableFields)) {
                $this->event->setDeleted($deleted);
                $changedEvent = true;
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'deleted';
            }
        }

        $cancelled = $this::parseBooleanString($request->get('cancelled'));
        if (!is_null($cancelled) && $cancelled != $this->event->getCancelled()) {
            if (in_array('cancelled',$editableFields)) {
                $this->event->setCancelled($cancelled);
                $changedEvent = true;
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'cancelled';
            }
        }


        if ($request->get('add_tag_0')) {
            if (in_array('tags',$editableFields)) {
                $count = 0;
                while ($request->request->get('add_tag_' . $count)) {
                    $tag = $tagRepository->findOneBy(['id' => $request->request->get('add_tag_' . $count), 'account' => $this->account]);
                    if ($tag) {
                        $eventTag = $eventTagRepository->findOneBy(array('event' => $this->event, 'tag' => $tag));
                        if (!$eventTag) {
                            $eventTag = new EventHasTag();
                            $eventTag->setEvent($this->event);
                            $eventTag->setTag($tag);
                            $eventTag->setEnabled(True);
                            $historyWorker->addEventHasTag($eventTag);
                        } elseif (!$eventTag->getENabled()) {
                            $eventTag->setEnabled(True);
                            $historyWorker->addEventHasTag($eventTag);
                        }
                    } else {
                        // TODO should show this 404 to user somehow?
                    }
                    $count++;
                }
            } else {
                $errorFieldsTriedToEditThatWereNotAllowed[] = 'tags';
            }
        }


        ########### Result and Error/Save!
        if ($errorFieldsTriedToEditThatWereNotAllowed) {
            asort($errorFieldsTriedToEditThatWereNotAllowed);
            asort($editableFields);
            $out = [
                'error' => [
                    'id' => 'can_not_edit_field',
                    'fields_mode'=>$this->event->getEditableFieldsMode(),
                    # TODO This produces values of [0=>'description',1=>'title',2=>'url',3=>'url_tickets']
                    # there is no reason to have the keys here!
                    # Should be JSON List, not JSON object!
                    'fields_not_allowed'=>array_values($errorFieldsTriedToEditThatWereNotAllowed),
                    'fields_allowed'=>array_values($editableFields),
                ],
                'changes' => false,
            ];
            return new Response(
                json_encode($out),
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        } else {
            $out = [
                'event' => [
                    'id' => $this->event->getId(),
                ],
                'changes' => false,
            ];
            if ($changedEvent) {
                $historyWorker->addEvent($this->event);
            }
            if ($historyWorker->hasContents()) {
                $historyWorkerService->persistHistoryWorker($historyWorker);
                $out['changes'] = true;
            }

            return new Response(
                json_encode($out),
                Response::HTTP_OK,
                ['content-type' => 'application/json']
            );
        }

    }

}
