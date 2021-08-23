<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventHasImport;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventHasTag;
use App\Entity\EventOccurrence;
use App\Entity\Tag;
use App\Entity\TimeZone;
use App\Form\EditTagsType;
use App\Form\EventEditDetailsType;
use App\Service\HistoryWorker\HistoryWorkerService;
use App\Service\UpdateSourcedEvent\UpdateSourcedEventService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AccountManageEventDetailsController extends AccountManageController
{

    /** @var  Event */
    protected $event;

    protected function buildEvent($account_username, $event_slug, Request $request)
    {
        $this->setUpAccountManage($account_username, $request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Event::class);

        $this->event = $repository->findOneBy(array('account'=>$this->account, 'slug'=>$event_slug));
        if (!$this->event) {
            throw new  NotFoundHttpException('Not found');
        }
    }

    public function indexShow($account_username, $event_slug, Request $request)
    {
        $this->buildEvent($account_username, $event_slug, $request);

        $doctrine = $this->getDoctrine();
        $currentTags = $doctrine->getRepository(Tag::class)->findByEvent($this->event);

        $eventHasImports = $doctrine->getRepository(EventHasImport::class)->findByEvent($this->event);

        $eventHasSourceEvents = $doctrine->getRepository(EventHasSourceEvent::class)->findByEvent($this->event);

        $eventOccurrence = null;
        if ($this->event->hasReoccurence() && $request->query->get('startutc')) {
            $bits = explode('-', $request->query->get('startutc'));
            $startutc = new \DateTime('', new \DateTimeZone('UTC'));
            $startutc->setDate($bits[0], $bits[1], $bits[2]);
            $startutc->setTime($bits[3], $bits[4], $bits[5]);
            $eventOccurrence = $doctrine->getRepository(EventOccurrence::class)->findOneBy(['event'=>$this->event, 'startEpoch'=>$startutc->getTimestamp()]);
        }

        return $this->render('account/manage/event/details/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
            'currentTags' => $currentTags,
            'eventHasImports' => $eventHasImports,
            'eventHasSourceEvents' => $eventHasSourceEvents,
            'eventOccurrence' => $eventOccurrence,
            'canCancelOrDelete' => in_array('cancelled', $this->event->getEditableFieldsList()),
        ]));
    }

    public function indexShowSeries($account_username, $event_slug, Request $request)
    {
        $this->buildEvent($account_username, $event_slug, $request);

        $doctrine = $this->getDoctrine();

        if (!$this->event->hasReoccurence()) {
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(), 'event_slug' => $this->event->getSlug()]);
        }

        $eventOccurrences = $doctrine->getRepository(EventOccurrence::class)->findBy(['event'=>$this->event], ['startEpoch'=>'ASC']);


        return $this->render('account/manage/event/details/series.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
            'eventOccurrences' => $eventOccurrences,
        ]));
    }


    public function indexEditDetails($account_username, $event_slug, Request $request, HistoryWorkerService $historyWorkerService)
    {
        $this->buildEvent($account_username, $event_slug, $request);

        // build the form
        $editableFields = $this->event->getEditableFieldsList();
        $editableMode = $this->event->getEditableFieldsMode();
        $form = $this->createForm(
            EventEditDetailsType::class,
            $this->event,
            array(
                'edit_extra_fields' => $this->event->getExtraFieldsKeys(),
                'editableFields' => $editableFields,
                'editableMode' => $editableMode,
            )
        );
        if (in_array('start_end', $editableFields)) {
            $form->get('all_day')->setData($this->event->isAllDay());
            $form->get('start_date')->setData([
                'year'=>$this->event->getStartYear(),
                'month'=>$this->event->getStartMonth(),
                'day'=>$this->event->getStartDay(),
            ]);
            $form->get('start_time')->setData([
                'hour'=>$this->event->getStartHour(),
                'minute'=>$this->event->getStartMinute(),
                'second'=>$this->event->getStartSecond(),
            ]);
            $form->get('end_date')->setData([
                'year'=>$this->event->getEndYear(),
                'month'=>$this->event->getEndMonth(),
                'day'=>$this->event->getEndDay(),
            ]);
            $form->get('end_time')->setData([
                'hour'=>$this->event->getEndHour(),
                'minute'=>$this->event->getEndMinute(),
                'second'=>$this->event->getEndSecond(),
            ]);
        }

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Non Mapped Fields
            if (in_array('start_end', $editableFields)) {
                $startDate = $form->get('start_date')->getData();
                $endDate = $form->get('end_date')->getData();
                if ($form->get('all_day')->getData()) {
                    $this->event->setStartWithInts(
                        $startDate['year'],
                        $startDate['month'],
                        $startDate['day'],
                        null,
                        null,
                        null
                    );
                    $this->event->setEndWithInts(
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
                    $this->event->setStartWithInts(
                        $startDate['year'],
                        $startDate['month'],
                        $startDate['day'],
                        $startTime['hour'],
                        $startTime['minute'],
                        $startTime['second']
                    );
                    $this->event->setEndWithInts(
                        $endDate['year'],
                        $endDate['month'],
                        $endDate['day'],
                        $endTime['hour'],
                        $endTime['minute'],
                        $endTime['second']
                    );
                }
            }

            // Save
            foreach ($this->event->getExtraFieldsKeys() as $key) {
                $this->event->setExtraField($key, $form->get('extra_field_'.md5($key))->getData());
            }

            if ($form->get('new_extra_field_key')->getData() || $form->get('new_extra_field_value')->getData()) {
                $this->event->setExtraField($form->get('new_extra_field_key')->getData(), $form->get('new_extra_field_value')->getData());
            }

            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addEvent($this->event);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            $this->addFlash(
                'success',
                'Event edited!'
            );
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_slug' => $this->event->getSlug() ]);
        }

        $editExtraFieldKeys = [];
        foreach ($this->event->getExtraFieldsKeys() as $key) {
            $editExtraFieldKeys[] = 'extra_field_'. md5($key);
        }

        return $this->render('account/manage/event/details/editDetails.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
            'form' => $form->createView(),
            'edit_extra_fields' => $editExtraFieldKeys,
            'editableFields' => $editableFields,
            'editableMode' => $editableMode,
        ]));
    }


    public function indexEditTags($account_username, $event_slug, Request $request, HistoryWorkerService $historyWorkerService)
    {
        $this->buildEvent($account_username, $event_slug, $request);

        // build the form
        $doctrine = $this->getDoctrine();
        $eventTagRepository = $doctrine->getRepository(EventHasTag::class);
        $tagRepository = $doctrine->getRepository(Tag::class);
        $currentTags = $tagRepository->findByEvent($this->event);

        $form = $this->createForm(EditTagsType::class, null, array('account'=>$this->account,'currentTags'=>$currentTags));

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            // Save
            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());

            foreach ($tagRepository->findBy(array('account'=>$this->account)) as $tag) {
                if (in_array($tag, $form->get('tags')->getData())) {
                    $eventTag = $eventTagRepository->findOneBy(array('event'=>$this->event, 'tag'=>$tag));
                    if (!$eventTag) {
                        $eventTag = new EventHasTag();
                        $eventTag->setEvent($this->event);
                        $eventTag->setTag($tag);
                    }
                    $eventTag->setEnabled(true);
                    $historyWorker->addEventHasTag($eventTag);
                } else {
                    $eventTag = $eventTagRepository->findOneBy(array('event'=>$this->event, 'tag'=>$tag));
                    if ($eventTag) {
                        $eventTag->setEnabled(false);
                        $historyWorker->addEventHasTag($eventTag);
                    }
                }
            }

            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            $this->addFlash(
                'success',
                'Tags edited!'
            );
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_slug' => $this->event->getSlug() ]);
        }

        return $this->render('account/manage/event/details/editTags.html.twig', $this->getTemplateVariables([
            'account' => $this->account,
            'event' => $this->event,
            'form' => $form->createView(),
        ]));
    }
    

    public function indexEditCancel($account_username, $event_slug, Request $request, HistoryWorkerService $historyWorkerService)
    {
        $this->buildEvent($account_username, $event_slug, $request);

        if (!in_array('cancelled', $this->event->getEditableFieldsList())) {
            return $this->render('account/manage/event/details/editCancel.notAllowed.html.twig', $this->getTemplateVariables([
                'account'=> $this->account,
                'event' => $this->event,
            ]));
        }

        # TODO check below is POST too, and CSFR
        if ($request->get('action') == 'cancel') {
            $this->event->setCancelled(true);

            // Save
            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addEvent($this->event);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            $this->addFlash(
                'success',
                'Event cancelled!'
            );
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_slug' => $this->event->getSlug() ]);
        }

        return $this->render('account/manage/event/details/editCancel.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
        ]));
    }

    public function indexEditDelete($account_username, $event_slug, Request $request, HistoryWorkerService $historyWorkerService)
    {
        $this->buildEvent($account_username, $event_slug, $request);

        if (!in_array('deleted', $this->event->getEditableFieldsList())) {
            return $this->render('account/manage/event/details/editDelete.notAllowed.html.twig', $this->getTemplateVariables([
                'account'=> $this->account,
                'event' => $this->event,
            ]));
        }

        # @TODO check below is POST too,
        if ($request->get('action') == 'delete') {
            $this->event->setDeleted(true);

            // Save
            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addEvent($this->event);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            $this->addFlash(
                'success',
                'Event deleted!'
            );
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_slug' => $this->event->getSlug() ]);
        }

        return $this->render('account/manage/event/details/editDelete.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
        ]));
    }

    public function indexEditSource($account_username, $event_slug, Request $request, HistoryWorkerService $historyWorkerService, UpdateSourcedEventService $updateSourcedEventService)
    {
        $this->buildEvent($account_username, $event_slug, $request);

        $doctrine = $this->getDoctrine();
        $eventHasSourceEvents = $doctrine->getRepository(EventHasSourceEvent::class)->findAll();
        if (!$eventHasSourceEvents) {
            throw new  NotFoundHttpException('Not found');
        }
        $eventHasSourceEvent = $eventHasSourceEvents[0];

        # @TODO check below is POST too,
        if ($request->get('action') == 'stopUpdates') {
            $eventHasSourceEvent->setUpdateAll(false);

            // Save
            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addEventHasSourceEvent($eventHasSourceEvent);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            $this->addFlash(
                'success',
                'This event will no longer be updated for you'
            );
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_slug' => $this->event->getSlug() ]);
        }
        # @TODO check below is POST too,
        if ($request->get('action') == 'startUpdates') {
            $eventHasSourceEvent->setUpdateAll(true);

            // Save
            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addEventHasSourceEvent($eventHasSourceEvent);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // Update now (not via message), so that when user goes to index page it's already up to date.
            $updateSourcedEventService->update($eventHasSourceEvent);

            // redirect
            $this->addFlash(
                'success',
                'This event will now be updated for you'
            );
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_slug' => $this->event->getSlug() ]);
        }

        return $this->render('account/manage/event/details/editSource.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
            'eventHasSourceEvent' => $eventHasSourceEvent,
        ]));
    }
}
