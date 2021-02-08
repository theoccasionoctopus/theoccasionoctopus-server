<?php

namespace App\Controller;

use App\Constants;
use App\Entity\EventHasImport;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventOccurrence;
use App\Entity\Tag;
use App\Entity\User;
use App\Library;
use App\Service\ActivityPubData\ActivityPubDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;

class AccountIdPublicEventDetailsController extends AccountIdPublicController
{
    protected $event;

    protected function setUpAccountByIdPublicEvent($account_id, $event_id, Request $request)
    {
        $this->setUpAccountByIdPublic($account_id, $request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Event::class);
        /** @var Event $event */
        $this->event = $repository->findOneBy(array('account'=>$this->account, 'id'=>$event_id));
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

    public function showEvent($account_id, $event_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        $this->setUpAccountByIdPublicEvent($account_id, $event_id, $request);

        if ($this->isRequestForActivityPubJSON($request)) {
            return new Response(
                json_encode($activityPubDataService->generateEventObject($this->event), JSON_PRETTY_PRINT),
                Response::HTTP_OK,
                ['content-type' => 'application/activity+json']
            );
        } else {
            return $this->redirectToRoute('account_public_event_show_event', ['account_username' => $this->account->getUsername(),'event_id' => $this->event->getId() ]);
        }
    }

    public function create($account_id, $event_id, Request $request, ActivityPubDataService $activityPubDataService)
    {
        throw new  NotFoundHttpException('Not found');
    }
}
