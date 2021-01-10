<?php

namespace App\Command;

use App\Entity\Account;
use App\Service\Purge\PurgeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\Source;
use GuzzleHttp\Client;
use App\Import\ImportRunner;
use Twig\Environment;

class PurgeAccountCommand extends Command
{
    protected static $defaultName = 'theocasionoctupus:purge-account';

    protected $container;

    /** @var  PurgeService */
    protected $purgeService;

    /**
     * LoadCountryData constructor.
     */
    public function __construct(ContainerInterface $container, PurgeService $purgeService)
    {
        parent::__construct();
        $this->container = $container;
        $this->purgeService = $purgeService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Purge Account')
            ->setHelp('Purge Account')
            ->addArgument('accountid', InputArgument::REQUIRED, 'Account Id (a GUID string)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->container->get('doctrine');

        $account = $doctrine->getRepository(Account::class)->findOneById($input->getArgument('accountid'));
        if (!$account) {
            $output->writeln('Can not find account');
            return 1;
        }

        $this->purgeService->purgeAccount($account);

        return 0;
    }
}
