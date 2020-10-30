<?php
namespace App\Command;


use App\Entity\Account;
use App\Entity\AccountRemote;
use App\Entity\Country;
use App\Entity\EmailUserUpcomingEventsForAccount;
use App\Entity\Event;
use App\Entity\Import;
use App\Entity\RemoteServer;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Service\Import\ImportService;
use App\Service\RemoteUserContent\RemoteUserContentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Source;
use GuzzleHttp\Client;
use App\Import\ImportRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

class DownloadImportContentCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:download-import-content';

    /** @var  ContainerInterface */
    protected $container;

    protected $importService;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, ImportService $importService)
    {
        parent::__construct();
        $this->container = $container;
        $this->importService = $importService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Download Import Content')
            ->setHelp('Download Import Content')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');
        /** @var AccountRemote $accountRemote */
        foreach($doctrine->getRepository(Import::class)->findByEnabled(true) as $import) {
            $output->writeln('Import '. $import->getId(). ' for account '. $import->getAccount()->getId());
            $this->importService->import($import);
        }
        return 0;

    }

}
