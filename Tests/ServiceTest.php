<?php

namespace Mhary\SettingsBundle\Tests;

use Mhary\SettingsBundle\DependencyInjection\MharySettingsExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ServiceTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new MharySettingsExtension(),
        );
    }

    public function testAlias()
    {
        $this->load();
        $this->assertContainerBuilderHasAlias('settings_manager', 'mhary.settings.settings_manager');
    }

    /**
     * If we provide a cache_service we should use the CachedSettingsManager as default.
     */
    public function testCacheServiceAlias()
    {
        $this->load(array('cache_service' => 'cache'));
        $this->assertContainerBuilderHasAlias('settings_manager', 'mhary.settings.cached_settings_manager');
    }
}
