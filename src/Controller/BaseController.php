<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\TimeZone;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends AbstractController
{
    protected $userTimeZoneCode = 'Europe/London';
    protected $userTimeZone = null;

    protected function setUp(Request $request)
    {
        $this->setUpUserTimeZone($request);
    }

    protected function setUpUserTimeZone(Request $request)
    {

        // TODO all controllers need to call this so it's set up correctly, and that needs to be checked

        // Instance Default
        $this->userTimeZoneCode = $this->getParameter('app.default_timezone_code');

        // Is it set in a cookie?
        $code = $request->cookies->get('timezone');
        if ($code) {
            $doctrine = $this->getDoctrine();
            $repository = $doctrine->getRepository(TimeZone::class);
            $this->userTimeZone = $repository->findOneByCode($code);
            if ($this->userTimeZone) {
                $this->userTimeZoneCode = $this->userTimeZone->getCode();
                return;
            } else {
                throw new \Exception($code);
            }
        }
    }


    protected function getTemplateVariables($vars = array())
    {
        $more_vars = array(
            'userTimeZone' => $this->userTimeZoneCode,
        );

        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($user instanceof User) {
            $doctrine = $this->getDoctrine();
            $repository = $doctrine->getRepository(Account::class);
            $more_vars['accounts_user_can_manage'] = $repository->findUserCanManage($user);
        }

        return array_merge($vars, $more_vars);
    }

    protected function isRequestForAccountActivityStreamsProfileJSON(Request $request):bool
    {
        // As Defined in https://www.w3.org/TR/activitypub/#retrieving-objects
        // Must separate by "," - Mastodon sends both
        $haystack = array('application/activity+json', 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"');
        foreach (explode(",", $request->headers->get('Accept')) as $needle) {
            if (in_array(trim($needle), $haystack)) {
                return true;
            }
        }
        return false;
    }

    protected function getResponseAccountActivityStreamsProfileJSON(Account $account, Request $request)
    {
        $id_and_url = $this->getParameter('app.instance_url').$this->generateUrl('account_public', ['account_username'=>$account->getAccountLocal()->getUsername()]);
        $out = [
            '@context'=>'https://www.w3.org/ns/activitystreams',
            // TODO an type of Group or Organization may be just as good - have a setting per account? https://www.w3.org/TR/activitystreams-vocabulary/#actor-types
            'type'=>'Person',
            'id'=>$id_and_url,
            'inbox'=>$this->getParameter('app.instance_url').$this->generateUrl('account_activity_streams_inbox', ['account_id'=>$account->getId()]),
            'outbox'=>$this->getParameter('app.instance_url').$this->generateUrl('account_activity_streams_outbox', ['account_id'=>$account->getId()]),
            'preferredUsername'=>$account->getAccountLocal()->getUsername(),
            'name'=>$account->getTitle(),
            // TODO description should have links turned to tags too
            'summary'=>nl2br($account->getAccountLocal()->getDescription()),
            'url'=>$id_and_url,
            'occasion-octopus-id'=>$account->getId(),
            'publicKey'=>[
                "id"=>$id_and_url.'#main-key',
                'owner'=>$id_and_url,
                'publicKeyPem'=>$account->getAccountLocal()->getKeyPublic(),
            ]
        ];
        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/activity+json']
        );
    }
}
