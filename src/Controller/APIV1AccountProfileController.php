<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\EventHasTag;
use App\Entity\Tag;
use App\Service\HistoryWorker\HistoryWorkerService;
use App\Library;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;

class APIV1AccountProfileController extends APIV1AccountController
{
    public function profileJSON($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);

        $out = array(
            'id'=> $this->account->getId(),
            'username'=>$this->account->getAccountLocal()->getUsername(),
            'title'=>$this->account->getTitle(),
        );

        return new Response(
            json_encode($out),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }
}
