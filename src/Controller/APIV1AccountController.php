<?php

namespace App\Controller;

use App\APIV1\ICalBuilderForAccount;
use App\Constants;
use App\Entity\AccountFollowsAccount;
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
    protected $account_permission_read_private = false;
    protected $account_permission_read_only_followers = false;
    protected $account_permission_write = false;

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
        if ($this->account->getAccountLocal()->isLocked()) {
            throw new  NotFoundHttpException('Not found');
        }

        // See if this access token can access this account, and what permissions it has if so.
        if ($this->accessToken) {
            // Access token can be bound to one account only - if that's the case check that.
            if ($this->accessToken->getAccount() && $this->accessToken->getAccount() != $this->account) {
                // TODO Change what binding a token to an account does
                // Currently: Can only read and write to that account, can't even look at other accounts
                // Should be: Can only write to that account, can only read:
                // * that account (if still manager; manager may have been removed since token created)
                // * anything followers only in accounts that accounts follows
                // * anything public
                throw new AccessDeniedHttpException('Wrong Account For This Token');
            }
            // Does this access token give permission to manage this account?
            $userManagesAccount = $doctrine->getRepository(UserManageAccount::class)->findOneBy(['user'=>$this->accessToken->getUser(),'account'=>$this->account]);
            if ($userManagesAccount) {
                $this->account_permission_read_private = true;
                $this->account_permission_read_only_followers = true;
                // But still, only some access tokens can write
                if ($this->accessToken->getWrite()) {
                    $this->account_permission_write = true;
                }
            } else {
                // Does this access token user manage an account that follows this account?
                if ($doctrine->getRepository(Account::class)->findAccountsManagedByUserThatFollowsThisAccount($this->accessToken->getUser(), $this->account)) {
                    $this->account_permission_read_only_followers = true;
                }
            }
        }
    }

    public static function parseBooleanString($in)
    {
        $in = substr(strtolower(trim($in)), 0, 1);
        if (in_array($in, ['y','1'])) {
            return true;
        } elseif (in_array($in, ['n','0'])) {
            return false;
        } else {
            return null;
        }
    }

    public function privacyLevelToAPIString($in)
    {
        if ($in == Constants::PRIVACY_LEVEL_PRIVATE) {
            return 'private';
        }
        if ($in == Constants::PRIVACY_LEVEL_ONLY_FOLLOWERS) {
            return 'only-followers';
        }
        if ($in == Constants::PRIVACY_LEVEL_PUBLIC) {
            return 'public';
        }
    }
}
