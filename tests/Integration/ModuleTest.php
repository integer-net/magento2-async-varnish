<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Test\Integration;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleList;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{

    /**
     * @var ObjectManager
     */
    private $objectManager;
    const MODULE_NAME = 'IntegerNet_AsyncVarnish';
    /**
     * @return ModuleList
     */
    private function getTestModuleList()
    {
        /** @var ModuleList $moduleList */
        $moduleList = $this->objectManager->create(ModuleList::class);
        return $moduleList;
    }
    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }
    public function testTheModuleIsRegistered()
    {
        $registrar = new ComponentRegistrar();
        $paths = $registrar->getPaths(ComponentRegistrar::MODULE);
        $this->assertArrayHasKey(self::MODULE_NAME, $paths, 'Module should be registered');
    }
    public function testTheModuleIsKnownAndEnabled()
    {
        $moduleList = $this->getTestModuleList();
        $this->assertTrue($moduleList->has(self::MODULE_NAME),  'Module should be enabled');
    }

}