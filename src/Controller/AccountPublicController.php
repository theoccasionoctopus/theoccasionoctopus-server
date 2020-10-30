<?php

namespace App\Controller;

use App\Entity\AccountLocal;
use App\Entity\Tag;
use App\Library;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;

class AccountPublicController extends BaseController
{

    /** @var  Account */
    protected $account;


    protected function setUpAccountPublic($account_username, Request $request) {
        $this->setUp($request);
        $doctrine = $this->getDoctrine();
        $accountLocal = $doctrine->getRepository(AccountLocal::class)->findOneByUsernameCanonical(Library::makeAccountUsernameCanonical($account_username));
        if (!$accountLocal) {
            throw new  NotFoundHttpException('Not found');
        }
        $this->account = $accountLocal->getAccount();
    }


    public function indexAccount($account_username, Request $request)
    {

        $this->setUpAccountPublic($account_username, $request);

        if ($this->isRequestForAccountActivityStreamsProfileJSON($request)) {
            return $this->getResponseAccountActivityStreamsProfileJSON($this->account);
        }

        return $this->render('account/public/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
        ]));
    }



}
