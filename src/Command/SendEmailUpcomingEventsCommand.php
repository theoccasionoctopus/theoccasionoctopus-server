<?php
namespace App\Command;

use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\User;
use App\RepositoryQuery\EventRepositoryQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class SendEmailUpcomingEventsCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:send-email-upcoming-events';

    /** @var  ContainerInterface */
    protected $container;

    /** @var  \Swift_Mailer */
    protected $mailer;

    private $twig;

    /** @var LoggerInterface  */
    protected $logger;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, \Swift_Mailer $mailer, Environment $twig, LoggerInterface $logger)
    {
        parent::__construct();
        $this->container = $container;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send Upcoming Events Emails')
            ->setHelp('Send Upcoming Events Emails')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');
        $accountRepository = $doctrine->getRepository(EmailUserUpcomingEventsForAccount::class);

        /** @var EmailUserUpcomingEventsForAccount $emailUpcomingEvents */
        foreach ($accountRepository->findByEnabled(true) as $emailUpcomingEvents) {
            if ($emailUpcomingEvents->shouldSendIfData()) {
                $output->writeln("User ". $emailUpcomingEvents->getUser()->getEmail() . " for Account ". $emailUpcomingEvents->getAccount()->getUsername());

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

        return 0;
    }
}
