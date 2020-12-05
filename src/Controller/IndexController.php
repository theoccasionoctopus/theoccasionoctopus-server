<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\TimeZone;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class IndexController extends BaseController
{
    public function index(Request $request)
    {
        $this->setUp($request);

        $user= $this->get('security.token_storage')->getToken()->getUser();
        if ($user instanceof User) {

            if (true) { # TODO if email verified

                $doctrine = $this->getDoctrine();
                $repository = $doctrine->getRepository(Account::class);
                $accounts = $repository->findUserCanManage($user);

                if (count($accounts) == 0) {
                    return $this->redirectToRoute('register_account');
                }

                if (count($accounts) == 1) {
                    return $this->redirectToRoute('account_manage', ['account_username' => $accounts[0]->getAccountLocal()->getUsername()]);
                }

                return $this->render('index/index.chooseaccount.html.twig', $this->getTemplateVariables([
                    'accounts_user_can_manage' => $accounts,
                ]));

            } else {

                ## TODO show verify email page

            }

        } else {

            return $this->render('index/index.loggedout.html.twig', $this->getTemplateVariables());
        }

    }

    public function setTimeZone(Request $request)
    {
        $this->setUp($request);

        // Did user specifically request a time zone?
        if ($request->query->get('set_timezone')) {

            $doctrine = $this->getDoctrine();
            $repository = $doctrine->getRepository(TimeZone::class);
            $userTimeZone = $repository->findOneByCode($request->query->get('set_timezone'));
            if ($userTimeZone) {

                $response = $this->redirectToRoute('index');
                $cookie = Cookie::create('timezone', $userTimeZone->getCode());
                // TODO make the cookie expire later
                $response->headers->setCookie($cookie);
                return $response;

            } else {
                // TODO show an error of some sort?
            }

        }

        $this->setUp($request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(TimeZone::class);
        $timezones = $repository->findBy([],['code'=>'ASC']);

        return $this->render('index/set_timezone.html.twig', $this->getTemplateVariables([
            'timezones' => $timezones,
        ]));

    }

    public function contact(Request $request)
    {
        $this->setUp($request);

        return $this->render('index/contact.html.twig', $this->getTemplateVariables());
    }

    public function directory(Request $request)
    {
        $this->setUp($request);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Account::class);
        $accounts_in_directory = $repository->findAllInDirectory();

        return $this->render('index/directory.html.twig', $this->getTemplateVariables([
            'accounts_in_directory'=>$accounts_in_directory,
        ]));
    }


}