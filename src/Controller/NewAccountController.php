<?php

namespace App\Controller;

use App\Entity\AccountLocal;
use App\Entity\Country;
use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\TimeZone;
use App\Entity\UserManageAccount;
use App\Form\AccountRegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\UserRegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Entity\Account;
use App\Library;

class NewAccountController extends BaseController
{



    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        // Must be a user!
        $user= $this->get('security.token_storage')->getToken()->getUser();
        if (!($user instanceof User)) {
            throw new  AccessDeniedException('You must log in first!');
        }

        // TODO check user $limitNumberOfAccountsManage!

        // build the form

        $account = new Account();
        $account->setId(Library::GUID());

        $accountLocal = new AccountLocal();
        $accountLocal->setAccount($account);
        $accountLocal->setDefaultCountry(
            $this->getDoctrine()->getRepository(Country::class)
                ->findOneBy(array('iso3166_two_char'=>$this->getParameter('app.default_country_code')))
        );
        $accountLocal->setDefaultTimezone(
            $this->getDoctrine()->getRepository(TimeZone::class)
                ->findOneBy(array('code'=>$this->getParameter('app.default_timezone_code')))
        );

        $form = $this->createForm(AccountRegisterType::class, $accountLocal);

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            //  save the account!
            $entityManager = $this->getDoctrine()->getManager();

            $account->setTitle($form->get('title')->getData());
            $entityManager->persist($account);

            $entityManager->persist($accountLocal);

            $userManagesAccount = new UserManageAccount();
            $userManagesAccount->setUser($user);
            $userManagesAccount->setAccount($account);
            $entityManager->persist($userManagesAccount);

            # TODO We must ask the user permission to do this, not assume!
            if (true) {
                $emailUpcomingEvents = new EmailUserUpcomingEventsForAccount();
                $emailUpcomingEvents->setAccount($account);
                $emailUpcomingEvents->setUser($user);
                $emailUpcomingEvents->setToken(Library::randomString(10,100));
                $entityManager->persist($emailUpcomingEvents);
            }

            $entityManager->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user


            $this->addFlash(
                'success',
                'Welcome to your new account'
            );
            return $this->redirectToRoute('account_manage', ['account_username' => $accountLocal->getUsername()  ]);

        }

        return $this->render(
            'newaccount/register.html.twig',
            $this->getTemplateVariables(array('form' => $form->createView()))
        );
    }


}
