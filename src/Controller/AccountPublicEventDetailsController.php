<?php

namespace App\Controller;

use App\Entity\EventHasImport;
use App\Entity\EventOccurrence;
use App\Entity\Tag;
use App\Library;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;

class AccountPublicEventDetailsController extends AccountPublicController
{

    protected $event;

    protected function setUpAccountPublicEvent($account_username, $event_id, Request $request) {

        $this->setUpAccountPublic($account_username, $request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Event::class);
        /** @var Event $event */
        $this->event = $repository->findOneBy(array('account'=>$this->account, 'id'=>$event_id));
        if (!$this->event) {
            throw new  NotFoundHttpException('Not found');
        }
        if ($this->event->getPrivacy() > 0) {
            throw new  NotFoundHttpException('Not found');
        }
    }

    public function showEvent($account_username, $event_id, Request $request)
    {

        $this->setUpAccountPublicEvent($account_username, $event_id, $request);

        $doctrine = $this->getDoctrine();

        $currentTags = $doctrine->getRepository(Tag::class)->findPublicByEvent($this->event);
        $eventHasImports = $doctrine->getRepository(EventHasImport::class)->findByEvent($this->event);

        $eventOccurrence = Null;
        if ($this->event->hasReoccurence() && $request->query->get('startutc')) {
            $bits = explode('-',$request->query->get('startutc'));
            $startutc = new \DateTime('',new \DateTimeZone('UTC'));
            $startutc->setDate($bits[0], $bits[1], $bits[2]);
            $startutc->setTime($bits[3], $bits[4], $bits[5]);
            $eventOccurrence = $doctrine->getRepository(EventOccurrence::class)->findOneBy(['event'=>$this->event, 'startEpoch'=>$startutc->getTimestamp()]);
        }

        return $this->render('account/public/event/details/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
            'currentTags' => $currentTags,
            'eventHasImports' => $eventHasImports,
            'eventOccurrence' => $eventOccurrence,
        ]));

    }

    public function showEventSeries($account_username, $event_id, Request $request)
    {

        $this->setUpAccountPublicEvent($account_username, $event_id, $request);

        if (!$this->event->hasReoccurence()) {
            return $this->redirectToRoute('account_public_event_show_event', ['account_username' => $this->account->getUsername(), 'event_id' => $this->event->getId()]);
        }

        $doctrine = $this->getDoctrine();

        $eventOccurrences = $doctrine->getRepository(EventOccurrence::class)->findBy(['event'=>$this->event],['startEpoch'=>'ASC']);

        return $this->render('account/public/event/details/series.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
            'eventOccurrences' => $eventOccurrences,
        ]));

    }



}
