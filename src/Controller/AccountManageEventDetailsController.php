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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class AccountManageEventDetailsController extends  AccountManageController {

    /** @var  Event */
    protected $event;

    protected function buildEvent($account_username, $event_id) {

        $this->build($account_username);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Event::class);

        $this->event = $repository->findOneBy(array('account'=>$this->account, 'id'=>$event_id));
        if (!$this->event) {
            throw new  NotFoundHttpException('Not found');
        }

    }

    public function indexShow($account_username, $event_id, Request $request)
    {

        $this->buildEvent($account_username, $event_id);

        $doctrine = $this->getDoctrine();
        $currentTags = $doctrine->getRepository(Tag::class)->findByEvent($this->event);

        $eventHasImports = $doctrine->getRepository(EventHasImport::class)->findByEvent($this->event);

        $eventHasSourceEvents = $doctrine->getRepository(EventHasSourceEvent::class)->findByEvent($this->event);

        $eventOccurrence = Null;
        if ($this->event->hasReoccurence() && $request->query->get('startutc')) {
            $bits = explode('-',$request->query->get('startutc'));
            $startutc = new \DateTime('',new \DateTimeZone('UTC'));
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
        ]));

    }

    public function indexShowSeries($account_username, $event_id, Request $request)
    {

        $this->buildEvent($account_username, $event_id);

        $doctrine = $this->getDoctrine();

        if (!$this->event->hasReoccurence()) {
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(), 'event_id' => $this->event->getId()]);
        }

        $eventOccurrences = $doctrine->getRepository(EventOccurrence::class)->findBy(['event'=>$this->event],['startEpoch'=>'ASC']);


        return $this->render('account/manage/event/details/series.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
            'eventOccurrences' => $eventOccurrences,
        ]));

    }


    public function indexEditDetails($account_username, $event_id,  Request $request,  HistoryWorkerService $historyWorkerService)
    {

        $this->buildEvent($account_username, $event_id);

        // build the form
        $timeZone = $this->event->getTimezone()->getCode();
        if (isset($_POST['event_edit_details']) && isset($_POST['event_edit_details']['timezone'])) {
            $timeZoneObject  = $this->getDoctrine()->getRepository(TimeZone::class)->findOneBy(['id'=>$_POST['event_edit_details']['timezone']]);
            if ($timeZoneObject) {
                $timeZone = $timeZoneObject->getCode();
            }
        }
        $editableFields = $this->event->getEditableFieldsList();
        $editableMode = $this->event->getEditableFieldsMode();
        $form = $this->createForm(
            EventEditDetailsType::class,
            $this->event,
            array(
                'timeZoneName' => $timeZone,
                'edit_extra_fields' => $this->event->getExtraFieldsKeys(),
                'editableFields' => $editableFields,
                'editableMode' => $editableMode,
            )
        );
        if (in_array('start_end', $editableFields)) {
            $form->get('start_at')->setData($this->event->getStart());
            $form->get('end_at')->setData($this->event->getEnd());
        }

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Non Mapped Fields
            if (in_array('start_end', $editableFields)) {
                $this->event->setStartWithObject($form->get('start_at')->getData());
                $this->event->setEndWithObject($form->get('end_at')->getData());
            }

            // Save
            foreach($this->event->getExtraFieldsKeys() as $key) {
                $this->event->setExtraField($key, $form->get('extra_field_'.md5($key))->getData());
            }

            if ($form->get('new_extra_field_key')->getData() || $form->get('new_extra_field_value')->getData()) {
                $this->event->setExtraField($form->get('new_extra_field_key')->getData() ,$form->get('new_extra_field_value')->getData());
            }

            $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
            $historyWorker->addEvent($this->event);
            $historyWorkerService->persistHistoryWorker($historyWorker);

            // redirect
            $this->addFlash(
                'success',
                'Event edited!'
            );
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_id' => $this->event->getId() ]);
        }

        $editExtraFieldKeys = [];
        foreach($this->event->getExtraFieldsKeys() as $key) {
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


    public function indexEditTags($account_username, $event_id, Request $request  ,  HistoryWorkerService $historyWorkerService )
    {

        $this->buildEvent($account_username, $event_id);

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

            foreach($tagRepository->findBy(array('account'=>$this->account)) as $tag) {

                if (in_array($tag, $form->get('tags')->getData())) {

                    $eventTag = $eventTagRepository->findOneBy(array('event'=>$this->event, 'tag'=>$tag));
                    if (!$eventTag) {
                        $eventTag = new EventHasTag();
                        $eventTag->setEvent($this->event);
                        $eventTag->setTag($tag);
                    }
                    $eventTag->setEnabled(True);
                    $historyWorker->addEventHasTag($eventTag);

                } else {

                    $eventTag = $eventTagRepository->findOneBy(array('event'=>$this->event, 'tag'=>$tag));
                    if ($eventTag) {
                        $eventTag->setEnabled(False);
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
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_id' => $this->event->getId() ]);


        }

        return $this->render('account/manage/event/details/editTags.html.twig', $this->getTemplateVariables([
            'account' => $this->account,
            'event' => $this->event,
            'form' => $form->createView(),
        ]));

    }
    

    public function indexEditCancel($account_username, $event_id,  Request $request,  HistoryWorkerService $historyWorkerService)
    {

        $this->buildEvent($account_username, $event_id);


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
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_id' => $this->event->getId() ]);
        }

        return $this->render('account/manage/event/details/editCancel.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
        ]));

    }

    public function indexEditDelete($account_username, $event_id,  Request $request,  HistoryWorkerService $historyWorkerService)
    {

        $this->buildEvent($account_username, $event_id);


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
            return $this->redirectToRoute('account_manage_event_show_event', ['account_username' => $this->account->getUsername(),'event_id' => $this->event->getId() ]);
        }

        return $this->render('account/manage/event/details/editDelete.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
        ]));

    }

}


