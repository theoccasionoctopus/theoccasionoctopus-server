<?php
namespace App\Command;

use App\Entity\Account;
use App\Entity\AccountRemote;
use App\Entity\Country;
use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\Event;
use App\Entity\RemoteServer;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Service\RemoteAccountContent\RemoteAccountContentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use GuzzleHttp\Client;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

// TODO This should be called DownloadRemoteAccountContentCommand! Need to change cron entries at same time.
class DownloadRemoteUserContentCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:download-remote-user-content';

    /** @var  ContainerInterface */
    protected $container;

    protected $remoteAccountContentService;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, RemoteAccountContentService $remoteAccountContentService)
    {
        parent::__construct();
        $this->container = $container;
        $this->remoteAccountContentService = $remoteAccountContentService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Download Remote User Content')
            ->setHelp('Download Remote User Content')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');
        /** @var AccountRemote $accountRemote */
        foreach ($doctrine->getRepository(AccountRemote::class)->findAll() as $accountRemote) {
            /** @var Account $account */
            $account = $accountRemote->getAccount();
            $output->writeln('Account '. $account->getId(). ' on '. $accountRemote->getRemoteServer()->getURL());
            $this->remoteAccountContentService->downloadAccountRemote($accountRemote);
        }
        return 0;
    }
}
