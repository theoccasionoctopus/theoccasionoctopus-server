<?php

namespace App\Controller;

use App\Entity\AccountLocal;
use App\Entity\User;
use App\Entity\UserManageAccount;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class AccountManageController extends BaseController
{

    /** @var  Account */
    protected $account;


    protected function build($account_username) {
        // If no user don't even bother
        $user= $this->get('security.token_storage')->getToken()->getUser();
        if (!($user instanceof User)) {
            throw new  AccessDeniedException('You must log in first!');
        }

        // Load account
        $doctrine = $this->getDoctrine();
        $accountLocal = $doctrine->getRepository(AccountLocal::class)->findOneByUsernameCanonical(Library::makeAccountUsernameCanonical($account_username));
        if (!$accountLocal) {
            throw new  NotFoundHttpException('Not found');
        }
        $this->account = $accountLocal->getAccount();

        // Check user has access
        $userManagesAccount = $doctrine->getRepository(UserManageAccount::class)->findOneBy(['user'=>$user,'account'=>$this->account]);
        if (!$userManagesAccount) {
            throw new  AccessDeniedException('You can not manage this account!');
        }

        // Now other stuff
        // TODO only when we have a request - $this->setUp($request);
    }


    protected function getTemplateVariables($vars = array()) {
        $vars['account'] = $this->account;
        return parent::getTemplateVariables($vars);
    }

    public function index($account_username, Request $request)
    {

        $this->build($account_username);

        return $this->render('account/manage/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
        ]));

    }

}
