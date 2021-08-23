<?php

namespace App\Controller;

use App\Constants;
use App\Entity\EventHasImport;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventOccurrence;
use App\Entity\Tag;
use App\Entity\User;
use App\Library;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;

class AccountPublicEventDetailsController extends AccountPublicController
{
    protected $event;

    protected function setUpAccountPublicEvent($account_username, $event_slug, Request $request)
    {
        $this->setUpAccountPublic($account_username, $request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Event::class);
        /** @var Event $event */
        $this->event = $repository->findOneBy(array('account'=>$this->account, 'slug'=>$event_slug));
        if (!$this->event) {
            throw new  NotFoundHttpException('Not found');
        }
        if (
            ($this->event->getPrivacy() == Constants::PRIVACY_LEVEL_PUBLIC) ||
            ($this->event->getPrivacy() == Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS && $this->account_permission_read_only_followers)
        ) {
            // Great
        } else {
            throw new  NotFoundHttpException('Not found');
        }
    }

    public function showEvent($account_username, $event_slug, Request $request)
    {
        $this->setUpAccountPublicEvent($account_username, $event_slug, $request);

        $doctrine = $this->getDoctrine();

        # Tags
        $currentTags = (
            $this->account_permission_read_only_followers ?
            $doctrine->getRepository(Tag::class)->findFollowerOnlyByEvent($this->event) :
            $doctrine->getRepository(Tag::class)->findPublicByEvent($this->event)
        );
        $eventHasImports = $doctrine->getRepository(EventHasImport::class)->findByEvent($this->event);
        $eventHasSourceEvents = $doctrine->getRepository(EventHasSourceEvent::class)->findByEvent($this->event);

        # A specific event occurrence?
        $eventOccurrence = null;
        if ($this->event->hasReoccurence() && $request->query->get('startutc')) {
            $bits = explode('-', $request->query->get('startutc'));
            $startutc = new \DateTime('', new \DateTimeZone('UTC'));
            $startutc->setDate($bits[0], $bits[1], $bits[2]);
            $startutc->setTime($bits[3], $bits[4], $bits[5]);
            $eventOccurrence = $doctrine->getRepository(EventOccurrence::class)->findOneBy(['event'=>$this->event, 'startEpoch'=>$startutc->getTimestamp()]);
        }

        # Add to account buttons?
        $addedToAccountsUserManages = [];
        $user = $this->get('security.token_storage')->getToken() ? $this->get('security.token_storage')->getToken()->getUser() : null;
        if ($user instanceof User) {
            foreach ($doctrine->getRepository(Account::class)->findUserCanManage($user) as $addToAccount) {
                if ($addToAccount->getId() != $this->account->getId()) {
                    $eventHasSourceEvent = $doctrine->getRepository(EventHasSourceEvent::class)->findOneBySourceEventAndDestinationAccount($this->event, $addToAccount);
                    $addedToAccountsUserManages[] = [
                        'account' => $addToAccount,
                        'event' => ($eventHasSourceEvent ? $eventHasSourceEvent->getEvent() : null)
                    ];
                }
            }
        }

        return $this->render('account/public/event/details/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
            'currentTags' => $currentTags,
            'eventHasImports' => $eventHasImports,
            'eventHasSourceEvents' => $eventHasSourceEvents,
            'eventOccurrence' => $eventOccurrence,
            'addedToAccountsUserManages' => $addedToAccountsUserManages,
        ]));
    }

    public function showEventSeries($account_username, $event_slug, Request $request)
    {
        $this->setUpAccountPublicEvent($account_username, $event_slug, $request);

        if (!$this->event->hasReoccurence()) {
            return $this->redirectToRoute('account_public_event_show_event', ['account_username' => $this->account->getUsername(), 'event_slug' => $this->event->getSlug()]);
        }

        $doctrine = $this->getDoctrine();

        $eventOccurrences = $doctrine->getRepository(EventOccurrence::class)->findBy(['event'=>$this->event], ['startEpoch'=>'ASC']);

        return $this->render('account/public/event/details/series.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'event' => $this->event,
            'eventOccurrences' => $eventOccurrences,
        ]));
    }
}
