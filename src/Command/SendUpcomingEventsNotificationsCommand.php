<?php
namespace App\Command;

use App\Entity\AccountLocal;
use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\EventOccurrence;
use App\Entity\User;
use App\Repository\AccountLocalRepository;
use App\RepositoryQuery\EventRepositoryQuery;
use App\Service\AccountRemote\AccountRemoteService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendUpcomingEventsNotificationsCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:send-upcoming-events-notifications';

    /** @var  ContainerInterface */
    protected $container;

    /** @var  \Swift_Mailer */
    protected $mailer;

    private $twig;

    /** @var LoggerInterface  */
    protected $logger;

    /** @var  AccountRemoteService */
    protected $accountRemoteService;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, \Swift_Mailer $mailer, Environment $twig, LoggerInterface $logger, AccountRemoteService $accountRemoteService, UrlGeneratorInterface $router)
    {
        parent::__construct();
        $this->container = $container;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->accountRemoteService = $accountRemoteService;
        $this->router = $router;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send Upcoming Events Notifications')
            ->setHelp('Send Upcoming Events Notifications')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->send_emails($input, $output);
        $this->send_activity_pub_notes($input, $output);
        return 0;
    }

    protected function send_emails(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');
        $emailUserUpcomingEventsForAccountRepository = $doctrine->getRepository(EmailUserUpcomingEventsForAccount::class);

        /** @var EmailUserUpcomingEventsForAccount $emailUpcomingEvents */
        foreach ($emailUserUpcomingEventsForAccountRepository->findByEnabled(true) as $emailUpcomingEvents) {
            if ($emailUpcomingEvents->shouldSendIfData()) {
                $output->writeln("Email - User ". $emailUpcomingEvents->getUser()->getEmail() . " for Account ". $emailUpcomingEvents->getAccount()->getUsername());

                $parameters = [
                    'account'=>$emailUpcomingEvents->getAccount(),
                    'accountLocal'=>$emailUpcomingEvents->getAccount()->getAccountLocal(),
                    'user'=>$emailUpcomingEvents->getUser(),
                    'upcomingEventOccurrences'=>$emailUpcomingEvents->getUpcomingEventOccurrences($doctrine),
                ];

                if ($parameters['upcomingEventOccurrences']) {
                    $output->writeln(".... Sending");

                    $message = new \Swift_Message(
                        $this->twig->render('email/upcoming_events/subject.text.twig', $parameters)
                    );

                    $message->setFrom(
                        [$this->container->getParameter('app.mailer_from_email') => $this->container->getParameter('app.instance_name')]
                    )
                        ->setTo($emailUpcomingEvents->getUser()->getEmail())
                        ->setBody(
                            $this->twig->render('email/upcoming_events/body.html.twig', $parameters),
                            'text/html'
                        )
                        ->addPart(
                            $this->twig->render('email/upcoming_events/body.text.twig', $parameters),
                            'text/plain'
                        );

                    // TODO need to add unsubscribe link to each email, and a action to handle it!

                    $this->mailer->send($message);
                    $this->logger->info(
                        'Email sent of upcoming Events',
                        [
                            'user_id'=>$emailUpcomingEvents->getUser()->getId(),
                            'email_sent_to'=>$emailUpcomingEvents->getUser()->getEmail()
                        ]
                    );
                }
            }
        }
    }

    protected function send_activity_pub_notes(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');
        $accountLocalRepo = $doctrine->getRepository(AccountLocal::class);

        /** @var AccountLocal $accountLocal */
        foreach ($accountLocalRepo->findBy(['locked'=>false]) as $accountLocal) {
            $output->writeln("ActivityPub Note - Account ". $accountLocal->getAccount()->getUsername());

            $eventRepositoryBuilder = new EventRepositoryQuery($doctrine);
            $eventRepositoryBuilder->setAccountEvents($accountLocal->getAccount());
            $start = new \DateTime('now', $accountLocal->getDefaultTimezone()->getDateTimeZoneObject());
            $start->setTime(0, 0, 0);
            $eventRepositoryBuilder->setFrom($start);
            $end = new \DateTime('now', $accountLocal->getDefaultTimezone()->getDateTimeZoneObject());
            $end->setTime(23, 59, 59);
            $eventRepositoryBuilder->setTo($end);
            $eventRepositoryBuilder->setShowCancelled(false);
            $eventRepositoryBuilder->setShowDeleted(false);
            $eventRepositoryBuilder->setPublicOnly();
            $eventOccurrences = $eventRepositoryBuilder->getEventOccurrences();

            if ($eventOccurrences) {
                $output->writeln(".... Sending");

                $msg = "Events today: <p/><p/>";
                /** @var EventOccurrence $eventOccurrence */
                foreach ($eventOccurrences as $eventOccurrence) {
                    $msg .= $eventOccurrence->getEvent()->getTitle()."<br/>";
                }
                $url = $this->container->getParameter('app.instance_url') . $this->router->generate('account_public_event', ['account_username'=>$accountLocal->getUsername()]);
                $msg .= '<p/>More at: <a href="'.$url.'">'.$url.'</a>';

                $this->accountRemoteService->sendPublicNote($accountLocal, $msg);
            }
        }
    }
}
