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

class SendTestEmailCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:send-test-email';

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
            ->setDescription('Send Test Email')
            ->setHelp('Send Test Email')
            ->addArgument('email', InputArgument::REQUIRED, 'The user email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $parameters = [];

        $message = new \Swift_Message(
                $this->twig->render('email/test/subject.text.twig', $parameters)
            );

        $message->setFrom(
            [$this->container->getParameter('app.mailer_from_email')=>$this->container->getParameter('app.mailer_from_name')]
        )
            ->setTo($input->getArgument('email'))
            ->setBody(
                $this->twig->render('email/test/body.html.twig', $parameters),
                'text/html'
            )
            ->addPart(
                $this->twig->render('email/test/body.text.twig', $parameters),
                'text/plain'
            )
        ;

        $this->mailer->send($message);

        return 0;

    }

}
