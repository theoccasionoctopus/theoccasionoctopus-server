<?php

namespace App\Service\ActivityPubData;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\AccountRemote;
use App\Entity\Event;
use App\Entity\Note;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityPubDataService
{

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(
        ParameterBagInterface $params,
        UrlGeneratorInterface $router
    ) {
        $this->params = $params;
        $this->router = $router;
    }

    public function generateActorForAccount(Account $account): array
    {
        $id = $this->params->get('app.instance_url') . $this->router->generate('account_id_public', ['account_id'=>$account->getId()]);
        $out = [
            '@context'=>[
                'https://www.w3.org/ns/activitystreams',
                # For publicKey
                'https://w3id.org/security/v1'
            ],
            // TODO an type of Group or Organization may be just as good - have a setting per account? https://www.w3.org/TR/activitystreams-vocabulary/#actor-types
            'type'=>'Person',
            'id'=>$id,
            'inbox'=>$this->params->get('app.instance_url').$this->router->generate('account_id_public_activitypub_inbox', ['account_id'=>$account->getId()]),
            'outbox'=>$this->params->get('app.instance_url').$this->router->generate('account_id_public_activitypub_outbox', ['account_id'=>$account->getId()]),
            'preferredUsername'=>$account->getAccountLocal()->getUsername(),
            'name'=>$account->getTitle(),
            // TODO description should have links turned to tags too
            'summary'=>nl2br($account->getAccountLocal()->getDescription()),
            'url'=>$this->params->get('app.instance_url') . $this->router->generate('account_public', ['account_username'=>$account->getUsername()]),
            'occasion-octopus-id'=>$account->getId(),
            'publicKey'=>[
                "id"=>$id.'#main-key',
                'owner'=>$id,
                'publicKeyPem'=>$account->getAccountLocal()->getKeyPublic(),
            ]
        ];
        return $out;
    }

    public function generateCreateActivityForEvent(Event $event): array
    {
        $out = [
            'type'=> 'Create',
            "id"=> $this->params->get('app.instance_url').$this->router->generate('account_id_public_event_create', ['account_id'=>$event->getAccount()->getId(),'event_id'=>$event->getId()]),
            'actor'=> $this->params->get('app.instance_url') . $this->router->generate('account_id_public', ['account_id'=>$event->getAccount()->getId()]),
            "to"=> 	"https://www.w3.org/ns/activitystreams#Public",
            'object'=>$this->generateEventObject($event),
        ];
        return $out;
    }

    public function generateEventObject(Event $event): array
    {
        $out = [
            // These fields are ActivityPub standard
            'type'=>'Event',
            'id'=>$this->params->get('app.instance_url').$this->router->generate('account_id_public_event_show_event', ['account_id'=>$event->getAccount()->getId(),'event_id'=>$event->getId()]),
            'name'=>$event->getTitle(),
            'summary'=>str_replace("\n", '<p>', htmlspecialchars($event->getDescription())),
            'startTime'=>$event->getStart('UTC')->format('Y-m-d\TH:i:s'),
            'endTime'=>$event->getEnd('UTC')->format('Y-m-d\TH:i:s'),
            'url'=>$this->params->get('app.instance_url').$this->router->generate('account_public_event_show_event', ['account_username'=>$event->getAccount()->getUsername(),'event_id'=>$event->getId()]),
            // These fields are us
            'occasion_octopus' =>
            [
                'id'=>$event->getId(),
                'description'=>$event->getDescription(),
                'url'=>$event->getUrl(),
                'url_tickets'=>$event->getUrlTickets(),
                'timezone'=>['code'=>$event->getTimezone()->getCode()],
                'country'=>['code'=>$event->getCountry()->getIso3166TwoChar()],
                // TODO Add all the various start/end options we send in normal API
            ]
        ];
        return $out;
    }


    public function generateCreateActivityForNote(Note $note): array
    {
        return [
            "@context"=> "https://www.w3.org/ns/activitystreams",
            "type"=> "Create",
            "id"=> $this->params->get('app.instance_url').$this->router->generate('account_id_public_note_create', ['account_id'=>$note->getAccount()->getId(),'note_id'=>$note->getId()]),
            "to"=> "https://www.w3.org/ns/activitystreams#Public",
            "actor"=> $this->params->get('app.instance_url') . $this->router->generate('account_id_public', ['account_id'=>$note->getAccount()->getId()]),
            "object"=>$this->generateNoteObject($note),
        ];
    }

    public function generateNoteObject(Note $note): array
    {
        $published = new \DateTime('', new \DateTimeZone('UTC'));
        $published->setTimestamp($note->getCreated());
        return [
            "id"=> $this->params->get('app.instance_url') . $this->router->generate('account_id_public_note_show_note', ['account_id'=>$note->getAccount()->getId(), 'note_id'=>$note->getId()]),
            "type"=> "Note",
            "attributedTo"=> $this->params->get('app.instance_url') . $this->router->generate('account_id_public', ['account_id'=>$note->getAccount()->getId()]),
            "content"=> $note->getContent(),
            "@context"=> "https://www.w3.org/ns/activitystreams",
            "published"=>$published->format('Y-m-d\TH:i:s\Z')
        ];
    }

    public function generateFollowRequest(AccountLocal $account, AccountRemote $wantsToFollowAccount)
    {
        return [
            "id"=> $this->params->get('app.instance_url') . $this->router->generate(
                'account_id_public_activitypub_follow',
                ['account_id'=>$account->getAccount()->getId(), 'remote_account_id'=>urlencode($wantsToFollowAccount->getActorDataId())]
            ),
            "type"=> "Follow",
            "actor"=> $this->params->get('app.instance_url') . $this->router->generate('account_id_public', ['account_id'=>$account->getAccount()->getId()]),
            "object"=> $wantsToFollowAccount->getActorDataId(),
            "@context"=> "https://www.w3.org/ns/activitystreams",
        ];
    }

    public function generateUndoFollow(AccountLocal $account, AccountRemote $wantsToUnfollowAccount)
    {
        return [
            "id"=> $this->params->get('app.instance_url') . $this->router->generate(
                'account_id_public_activitypub_unfollow',
                ['account_id'=>$account->getAccount()->getId(), 'remote_account_id'=>urlencode($wantsToUnfollowAccount->getActorDataId())]
            ),
            "type"=> "Undo",
            "actor"=> $this->params->get('app.instance_url') . $this->router->generate('account_id_public', ['account_id'=>$account->getAccount()->getId()]),
            "object"=>[
                "id"=> $this->params->get('app.instance_url') . $this->router->generate(
                    'account_id_public_activitypub_follow',
                    ['account_id'=>$account->getAccount()->getId(), 'remote_account_id'=>urlencode($wantsToUnfollowAccount->getActorDataId())]
                ),
                "type"=> "Follow",
                "actor"=> $this->params->get('app.instance_url') . $this->router->generate('account_id_public', ['account_id'=>$account->getAccount()->getId()]),
                "object"=> $wantsToUnfollowAccount->getActorDataId(),
                "@context"=> "https://www.w3.org/ns/activitystreams",
            ],
            "@context"=> "https://www.w3.org/ns/activitystreams",
        ];
    }

    public function generateFollowAccept(AccountRemote $account, AccountLocal $wantsToFollowAccount, array $objectData)
    {
        return [
            "id"=> $this->params->get('app.instance_url') . $this->router->generate(
                'account_id_public_activitypub_accept_follow_request',
                ['account_id'=>$wantsToFollowAccount->getAccount()->getId(), 'remote_account_id'=>urlencode($account->getActorDataId())]
            ),
            "type"=> "Accept",
            "actor"=> $this->params->get('app.instance_url') . $this->router->generate('account_id_public', ['account_id'=>$wantsToFollowAccount->getAccount()->getId()]),
            "object"=> $objectData,
            "@context"=> "https://www.w3.org/ns/activitystreams",
        ];
    }

    public function generateFollowReject(AccountRemote $account, AccountLocal $wantsToFollowAccount, array $objectData)
    {
        return [
            "id"=> $this->params->get('app.instance_url') . $this->router->generate(
                'account_id_public_activitypub_reject_follow_request',
                ['account_id'=>$wantsToFollowAccount->getAccount()->getId(), 'remote_account_id'=>urlencode($account->getActorDataId())]
            ),
            "type"=> "Reject",
            "actor"=> $this->params->get('app.instance_url') . $this->router->generate('account_id_public', ['account_id'=>$wantsToFollowAccount->getAccount()->getId()]),
            "object"=> $objectData,
            "@context"=> "https://www.w3.org/ns/activitystreams",
        ];
    }
}
