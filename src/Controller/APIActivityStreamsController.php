<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\InboxSubmission;
use App\Entity\UserManageAccount;
use App\Library;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;

class APIActivityStreamsController extends BaseController
{

    /** @var  Account */
    protected $account;

    protected function buildAccount($account_id, Request $request)
    {
        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);
        $this->account = $repository->findOneById($account_id);
        if (!$this->account) {
            throw new  NotFoundHttpException('Not found');
        }
        if (!$this->account->getAccountLocal()) {
            // API should only be used on local accounts
            throw new  NotFoundHttpException('Not found');
        }
        if ($this->account->getAccountLocal()->isLocked()) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function index($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);
        return $this->getResponseAccountActivityStreamsProfileJSON($this->account, $request);
    }

    public function inbox($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new Response(
                json_encode(['error'=>'no-data']),
                500,
                ['content-type' => 'application/json']
            );
        }

        $s = new InboxSubmission();
        $s->setId(Library::GUID());
        $s->setAccount($this->account);
        $s->setData($data);
        $s->setIp($request->getClientIp());
        $s->setUseragent($request->headers->get('User-Agent'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($s);
        $entityManager->flush();

        return new Response(
            json_encode([]),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    public function outbox($account_id, Request $request)
    {
        $this->buildAccount($account_id, $request);

        // TODO
    }
}
