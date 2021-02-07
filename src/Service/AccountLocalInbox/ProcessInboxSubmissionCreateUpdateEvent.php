<?php

namespace App\Service\AccountLocalInbox;

use App\ActivityPub\APEvent;
use App\Entity\Account;
use App\Entity\AccountFollowsAccount;
use App\Entity\AccountLocal;
use App\Entity\AccountRemote;
use App\Entity\InboxSubmission;
use App\Entity\RemoteServer;
use App\Library;
use App\Service\RequestHTTP\RequestHTTPService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcessInboxSubmissionCreateUpdateEvent extends ProcessInboxSubmissionBase
{
    public function canHandle(InboxSubmission $inboxSubmission)
    {
        return $inboxSubmission->getData()['type'] == 'Create' &&  $inboxSubmission->getData()['object']['type'] == 'Event';
    }

    public function handle(InboxSubmission $inboxSubmission)
    {
        $actorId = $inboxSubmission->getData()['actor'];

        // Sort out remote server
        $remoteServer = $this->remoteServerService->getOrCreateByUrl($actorId);

        // Find accounts
        $accountRemote = $this->entityManager->getRepository(AccountRemote::class)->findOneBy(array('actorDataId'=>$actorId,'remoteServer'=>$remoteServer));

        if ($remoteServer->getOccasionOctopusSoftware()) {
            // We fall back to our own API to get full info
            if (array_key_exists('occasion_octopus', $inboxSubmission->getData()['object'])) {
                $this->remoteAccountContentService->downloadEventFromOccasionOctopus($accountRemote, $inboxSubmission->getData()['object']['occasion_octopus']['id']);
            } else {
                // TODO raise error?
            }
        } else {
            // We process ActivityPub data
            $apEvent = new APEvent($inboxSubmission->getData()['object']);
            $this->remoteAccountContentService->updateEventWithActivityPubServerData($accountRemote, $apEvent);
        }

        $this->markInboxSubmissionProcessed($inboxSubmission);
        $this->entityManager->flush();
    }
}
