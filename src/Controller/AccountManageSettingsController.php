<?php

namespace App\Controller;

use App\Command\SendEmailUpcomingEventsCommand;
use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\Import;
use App\Entity\User;
use App\FilterParams\EventListFilterParams;
use App\Form\ImportNewType;
use App\RepositoryQuery\EventRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

class AccountManageSettingsController extends AccountManageController
{
    public function index($account_username, Request $request)
    {
        $this->build($account_username);

        $doctrine = $this->getDoctrine();
        $imports = $doctrine->getRepository(Import::class)->findBy(['account'=>$this->account]);
        $sendUpcomingEventsEmail = $doctrine->getRepository(EmailUserUpcomingEventsForAccount::class)
            ->findOneBy(['account'=>$this->account,'user'=>$this->get('security.token_storage')->getToken()->getUser()]);

        if (!$sendUpcomingEventsEmail) {
            $sendUpcomingEventsEmail = new EmailUserUpcomingEventsForAccount();
            $sendUpcomingEventsEmail->setAccount($this->account);
            $sendUpcomingEventsEmail->setUser($this->get('security.token_storage')->getToken()->getUser());
        }


        # TODO check below is POST too, and CSFR
        if ($request->get('action') == 'startEmailUserUpcomingEventsForAccount') {

            // Save
            $sendUpcomingEventsEmail->setEnabled(true);
            $this->getDoctrine()->getManager()->persist($sendUpcomingEventsEmail);
            $this->getDoctrine()->getManager()->flush($sendUpcomingEventsEmail);

            // redirect
            $this->addFlash(
                'success',
                'You will be emailed.'
            );
            return $this->redirectToRoute('account_manage_settings', ['account_username' => $this->account->getUsername() ]);
        } elseif ($request->get('action') == 'stopEmailUserUpcomingEventsForAccount') {

            // Save
            $sendUpcomingEventsEmail->setEnabled(false);
            $this->getDoctrine()->getManager()->persist($sendUpcomingEventsEmail);
            $this->getDoctrine()->getManager()->flush($sendUpcomingEventsEmail);

            // redirect
            $this->addFlash(
                'success',
                'You will not be emailed.'
            );
            return $this->redirectToRoute('account_manage_settings', ['account_username' => $this->account->getUsername() ]);
        }



        return $this->render('account/manage/settings/index.html.twig', $this->getTemplateVariables([
            'imports'=>$imports,
            'usersManage'=>$doctrine->getRepository(User::class)->findCanManageAccount($this->account),
            'sendUpcomingEventsEmail' =>$sendUpcomingEventsEmail,
        ]));
    }

    public function newImport($account_username, Request $request, LoggerInterface $logger)
    {
        $this->build($account_username);

        // build the form
        $import = new Import();
        $import->setAccount($this->account);
        $import->setId(Library::GUID());
        $import->setEnabled(true);
        // TODO let user select in form instead
        $import->setDefaultCountry($this->account->getAccountLocal()->getDefaultCountry());
        $import->setDefaultTimezone($this->account->getAccountLocal()->getDefaultTimezone());

        $form = $this->createForm(ImportNewType::class, $import, array(
            'account' => $this->account,
        ));

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Save
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($import);
            $entityManager->flush();

            // Log
            $logger->info(
                'New import created',
                [
                    'user_id'=>$this->get('security.token_storage')->getToken()->getUser()->getId(),
                    'account_id'=>$this->account->getId(),
                    'import_id'=>$import->getId()
                ]
            );

            // redirect
            return $this->redirectToRoute('account_manage_settings', ['account_username' => $this->account->getUsername() ]);
        }

        return $this->render('account/manage/settings/newImport.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'form' => $form->createView(),
        ]));
    }
}
