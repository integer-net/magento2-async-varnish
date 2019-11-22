<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Console\Command;

use IntegerNet\AsyncVarnish\Model\PurgeAsyncCache as Purger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeAsyncTagsCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var Purger
     */
    private $purger;

    public function __construct(
        \Magento\Framework\App\State\Proxy $appState,
        Purger $purger,
        ?string $name = null
    ) {
        $this->appState = $appState;
        $this->purger = $purger;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() //phpcs:ignore
    {
        $this->setName('integernet:asyncvarnish:purge')
            ->setDescription('Purges Varnish Tags ');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) //phpcs:ignore
    {
        try {
            $this->appState->setAreaCode('adminhtml');
            $successQty = $this->purger->run();

            $output->setDecorated(true);
            $output->writeln('<info>' . sprintf('%s records purged from cache', $successQty) . '</info>');
        } catch (\Exception $e) {
            $output->setDecorated(true);
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
