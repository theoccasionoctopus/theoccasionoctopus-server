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

        $user = $this->get('security.token_storage')->getToken() ? $this->get('security.token_storage')->getToken()->getUser() : null;
        if ($user instanceof User) {
            $doctrine = $this->getDoctrine();
            $repository = $doctrine->getRepository(Account::class);
            $more_vars['accounts_user_can_manage'] = $repository->findUserCanManage($user);
        }

        return array_merge($vars, $more_vars);
    }

    protected function isRequestForActivityPubJSON(Request $request):bool
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
}
