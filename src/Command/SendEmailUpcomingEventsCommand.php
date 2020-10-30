<?php
namespace App\Command;


use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

class SendEmailUpcomingEventsCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:send-email-upcoming-events';

    /** @var  ContainerInterface */
    protected $container;

    /** @var  \Swift_Mailer */
    protected $mailer;

    private $twig;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, \Swift_Mailer $mailer, Environment $twig)
    {
        parent::__construct();
        $this->container = $container;
        $this->mailer = $mailer;
        $this->twig = $twig;
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

        foreach($accountRepository->findByEnabled(true) as $emailUpcomingEvents) {

            if ($emailUpcomingEvents->shouldSendIfData()) {

                $output->writeln("User ". $emailUpcomingEvents->getUser()->getEmail() . " for Account ". $emailUpcomingEvents->getAccount()->getUsername());

                $parameters = [
                    'account'=>$emailUpcomingEvents->getAccount(),
                    'user'=>$emailUpcomingEvents->getUser(),
                ];

                $message = new \Swift_Message(
                        $this->twig->render('email/upcoming_events/subject.text.twig', $parameters)
                    );

                $message->setFrom(
                    [$this->container->getParameter('app.mailer_from_email')=>$this->container->getParameter('app.mailer_from_name')]
                )
                    ->setTo($emailUpcomingEvents->getUser()->getEmail())
                    ->setBody(
                        $this->twig->render('email/upcoming_events/body.html.twig', $parameters),
                        'text/html'
                    )
                    ->addPart(
                        $this->twig->render('email/upcoming_events/body.text.twig', $parameters),
                        'text/plain'
                    )
                ;

                // TODO need to add unsubscribe link to each email, and a action to handle it!

                $this->mailer->send($message);

            }

        }

        return 0;

    }

}
