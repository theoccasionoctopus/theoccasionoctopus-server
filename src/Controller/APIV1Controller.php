<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\APIAccessToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class APIV1Controller extends BaseController
{

    /** @var APIAccessToken */
    protected $accessToken = null;

    protected function build(Request $request) {

        $accessTokenString = null;

        if (substr($request->headers->get('authorization',''),0,7) == 'Bearer ') {
            $accessTokenString = substr($request->headers->get('authorization',''), 7);
        } elseif ($request->query->get('access_token')) {
            $accessTokenString = $request->query->get('access_token');
        }

        if ($accessTokenString) {
            $doctrine = $this->getDoctrine();
            $repository = $doctrine->getRepository(APIAccessToken::class);
            $this->accessToken = $repository->findOneBy(['token'=>$accessTokenString]);
            if (!$this->accessToken) {
                throw new  AccessDeniedException('Bad Token!');
            }
            if (!$this->accessToken->getEnabled()) {
                throw new AccessDeniedHttpException('Token Disabled!');
            }
        }

    }


}
