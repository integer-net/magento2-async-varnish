<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Test\Integration;

use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\Framework\App\DeploymentConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @magentoAppIsolation enabled
 */
class PurgeCacheTest extends TestCase
{
    /**
     * Full path to varnish mock server
     */
    private const MOCK_SERVER_DIR  = __DIR__ . '/VarnishMock';
    /**
     * Request log, as written by VarnishMock/server.php
     */
    private const REQUEST_LOG_FILE = self::MOCK_SERVER_DIR . '/.requests.log';
    /**
     * Mock server port as used in VarnishMock/server.php
     */
    private const MOCK_SERVER_PORT = '8082';
    /**
     * @var Process
     */
    private $mockServerProcess;
    /**
     * @var PurgeCache
     */
    private $purgeCache;

    protected function setUp()
    {
        $this->startMockServer();
        $this->createRequestLog();

        $this->configureVarnishHost();
        $this->purgeCache = Bootstrap::getObjectManager()->get(PurgeCache::class);
    }

    private function configureVarnishHost()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        $deploymentConfig = new DeploymentConfig(
            $objectManager->get(DeploymentConfig\Reader::class),
            ['http_cache_hosts' => [['host' => '127.0.0.1', 'port' => self::MOCK_SERVER_PORT]]]
        );
        $objectManager->addSharedInstance(
            $deploymentConfig,
            DeploymentConfig::class
        );
    }

    protected function tearDown()
    {
        $this->stopMockServer();
    }

    public function testWebserver()
    {
        $this->assertEquals("OK\n", \file_get_contents('http://127.0.0.1:' . self::MOCK_SERVER_PORT . '/'));
    }

    public function testPurgeRequestIsSentToVarnish()
    {
        $tagsPattern = 'XXX|YYY|ZZZZ';
        $result = $this->purgeCache->sendPurgeRequest($tagsPattern);
        $this->assertTrue($result);
        $this->assertEquals(
            [
                [
                    'method'  => 'PURGE',
                    'headers' => ['Host' => ['127.0.0.1'], 'X-Magento-Tags-Pattern' => [$tagsPattern]],
                ],
            ],
            $this->getRequestsFromLog()
        );
    }

    private function startMockServer(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var PhpExecutableFinder $phpExecutableFinder */
        $phpExecutableFinder = $objectManager->get(PhpExecutableFinder::class);
        $mockServerCmd = $phpExecutableFinder->find() . ' ' . self::MOCK_SERVER_DIR . '/server.php';
        //the following needs Symfony Process >= 4.2.0
//        $this->mockServerProcess = Process::fromShellCommandline($mockServerCmd);
        //so we use the old way to instantiate Process from string:
        $this->mockServerProcess = new Process($mockServerCmd);
        $this->mockServerProcess->start();
        //the following needs Symfony Process >= 4.2.0
//        $this->mockServerProcess->waitUntil(
//            function($output) {
//                return $output === 'Started';
//            }
//        );
        // so we wait a second or two instead:
        sleep(2);
    }

    private function stopMockServer(): void
    {
        // issue: this only kills the parent shell script, not the PHP process (Symfony Process 4.1)
//        $this->mockServerProcess->stop();
        // so we implemented a kill switch in the server:
        $ch = \curl_init('http://127.0.0.1:8082/?kill=1');
        \curl_exec($ch);
    }

    private function createRequestLog(): void
    {
        \file_put_contents(self::REQUEST_LOG_FILE, '');
        \chmod(self::REQUEST_LOG_FILE, 0666);
    }

    private function getRequestsFromLog(): array
    {
        $requests = \array_map(
            function (string $line): array {
                return \json_decode($line, true);
            },
            \file(self::REQUEST_LOG_FILE)
        );
        return $requests;
    }
}
