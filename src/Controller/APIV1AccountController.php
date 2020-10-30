<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Entity\UserManageAccount;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;

abstract class APIV1AccountController extends APIV1Controller
{

    /** @var  Account */
    protected $account;
    protected $account_permission_read_private = False;
    protected $account_permission_write = False;

    protected function buildAccount($account_id, Request $request)
    {

        $this->build($request);

        // Load Account
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

        // See if this access token can access this account, and what permissions it has if so.
        if ($this->accessToken) {
            // Access token can be bound to one account only - if that's the case check that.
            if ($this->accessToken->getAccount() && $this->accessToken->getAccount() != $this->account) {
                throw new AccessDeniedHttpException('Wrong Account For This Token');
            }
            // Does this access token give permission to manage this account?
            $userManagesAccount = $doctrine->getRepository(UserManageAccount::class)->findOneBy(['user'=>$this->accessToken->getUser(),'account'=>$this->account]);
            if ($userManagesAccount) {
                $this->account_permission_read_private = True;
                // But still, only some access tokens can write
                if ($this->accessToken->getWrite()) {
                    $this->account_permission_write = true;
                }
            }
        }



    }

    public static function parseBooleanString($in) {
        $in = substr(strtolower(trim($in)),0,1);
        if (in_array($in, ['y','1'])) {
            return True;
        } elseif (in_array($in, ['n','0'])) {
            return False;
        } else {
            return Null;
        }
    }

}
