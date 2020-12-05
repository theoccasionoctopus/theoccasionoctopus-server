<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\AccountLocal;
use App\Entity\User;
use App\Library;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;


class APIIndexController extends BaseController
{

    public function occasionOctopusInfoJSON() {
        $out = [
            'api_version'=> 1,
            'instance_name'=> $this->getParameter('app.instance_name'),
            'instance_url'=> $this->getParameter('app.instance_url'),
        ];
        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }




    public function webfinger(Request $request)
    {

        list($username, $host) = Library::parseWebFingerResourceToUsernameAndHost($request->query->get('resource'));

        // TODO check host is us

        $doctrine = $this->getDoctrine();

        /** @var AccountLocal $accountLocal */
        $accountLocal = $doctrine->getRepository(AccountLocal::class)->findOneByUsernameCanonical(Library::makeAccountUsernameCanonical($username));
        if (!$accountLocal) {
            throw new  NotFoundHttpException('Not found');
        }
        if ($accountLocal->isLocked()) {
            throw new  NotFoundHttpException('Not found');
        }

        $account = $accountLocal->getAccount();
        list($ssl, $host) = Library::parseURLToSSLAndHost($this->getParameter('app.instance_url'));
        $out = [
            'subject'=>'acct:'.$accountLocal->getUsername().'@'.$host,
            'aliases'=>[
                $this->getParameter('app.instance_url').$this->generateUrl('account_public',['account_username'=>$accountLocal->getUsername()]),
            ],
            'links'=>[
                [
                    'rel'=>'http://webfinger.net/rel/profile-page',
                    'type'=>'text/html',
                    'href'=>$this->getParameter('app.instance_url').$this->generateUrl('account_public',['account_username'=>$accountLocal->getUsername()]),
                ],
                [
                    'rel'=>'self',
                    'type'=>'application/activity+json',
                    'href'=>$this->getParameter('app.instance_url').$this->generateUrl('account_activity_streams_index',['account_id'=>$account->getId()]),
                ],
                [
                    'rel'=>'self',
                    'type'=>'application/activity+json',
                    'href'=>$this->getParameter('app.instance_url').$this->generateUrl('account_public',['account_username'=>$accountLocal->getUsername()]),
                ],
            ],
            'occasion-octopus-id'=> $account->getId(),
            'occasion-octopus-title'=> $account->getTitle(),
            'occasion-octopus-username'=> $accountLocal->getUsername(),
        ];
        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );

    }

}
