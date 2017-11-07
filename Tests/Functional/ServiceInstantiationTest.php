<?php

namespace Mhary\SettingsBundle\Tests\Functional;

use Mhary\SettingsBundle\Serializer\PhpSerializer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceInstantiationTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        static::bootKernel();
    }

    public function testHttpClient()
    {
        $container = static::$kernel->getContainer();

        $this->assertTrue($container->has('mhary.settings.serializer'));
        $serializer = $container->get('mhary.settings.serializer');
        $this->assertInstanceOf(PhpSerializer::class, $serializer);
    }
}
