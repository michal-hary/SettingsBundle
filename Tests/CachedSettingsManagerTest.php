<?php

namespace Mhary\SettingsBundle\Tests;

use Mhary\SettingsBundle\Manager\CachedSettingsManager;
use Mhary\SettingsBundle\Manager\SettingsManager;
use Psr\Cache\CacheItemPoolInterface;

class CachedSettingsManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $owner = $this->createOwner();
        $name = 'name';
        $value = 'foobar';
        $defaultValue = 'default';

        $settingManager = \Mockery::mock(SettingsManager::class);
        $settingManager->shouldReceive('get')->once()->with($name, $owner, $defaultValue)->andReturn($value);

        $cachedSettingsManager = $this->getMockBuilder(CachedSettingsManager::class)
            ->setMethods(array('fetchFromCache', 'storeInCache'))
            ->setConstructorArgs(array($settingManager, $this->getMock(CacheItemPoolInterface::class), 4711))
            ->getMock();
        $cachedSettingsManager->expects($this->once())
            ->method('fetchFromCache')
            ->with($this->equalTo($name), $this->equalTo($owner))
            ->willReturn(null);
        $cachedSettingsManager->expects($this->once())
            ->method('storeInCache')
            ->with($this->equalTo($name), $this->equalTo($value), $this->equalTo($owner))
            ->willReturn(null);

        $this->assertEquals($value, $cachedSettingsManager->get($name, $owner, $defaultValue));
    }

    public function testGetHit()
    {
        $owner = $this->createOwner();
        $name = 'name';
        $value = 'foobar';
        $defaultValue = 'default';

        $settingsManager = \Mockery::mock(SettingsManager::class);

        $cachedSettingsManager = $this->getMockBuilder(CachedSettingsManager::class)
            ->setMethods(array('fetchFromCache', 'storeInCache'))
            ->setConstructorArgs(array($settingsManager, $this->getMock(CacheItemPoolInterface::class), 4711))
            ->getMock();
        $cachedSettingsManager->expects($this->once())
            ->method('fetchFromCache')
            ->with($this->equalTo($name), $this->equalTo($owner))
            ->willReturn($value);

        $this->assertEquals($value, $cachedSettingsManager->get($name, $owner, $defaultValue));
    }

    public function testAll()
    {
        $owner = $this->createOwner();
        $value = array('foo' => 'bar');

        $settingsManager = \Mockery::mock(SettingsManager::class);
        $settingsManager->shouldReceive('all')->once()->with($owner)->andReturn($value);

        $cachedSettingsManager = $this->getMockBuilder(CachedSettingsManager::class)
            ->setMethods(array('fetchFromCache', 'storeInCache'))
            ->setConstructorArgs(array($settingsManager, $this->getMock(CacheItemPoolInterface::class), 4711))
            ->getMock();
        $cachedSettingsManager->expects($this->once())
            ->method('fetchFromCache')
            ->with($this->equalTo(null), $this->equalTo($owner))
            ->willReturn(null);
        $cachedSettingsManager->expects($this->once())
            ->method('storeInCache')
            ->with($this->equalTo(null), $this->equalTo($value), $this->equalTo($owner))
            ->willReturn(null);

        $this->assertEquals($value, $cachedSettingsManager->all($owner));
    }

    public function testAllHit()
    {
        $owner = $this->createOwner();
        $value = array('foo' => 'bar');

        $settingsManager = \Mockery::mock(SettingsManager::class);

        $cachedSettingsManager = $this->getMockBuilder(CachedSettingsManager::class)
            ->setMethods(array('fetchFromCache', 'storeInCache'))
            ->setConstructorArgs(array($settingsManager, $this->getMock(CacheItemPoolInterface::class), 4711))
            ->getMock();
        $cachedSettingsManager->expects($this->once())
            ->method('fetchFromCache')
            ->with($this->equalTo(null), $this->equalTo($owner))
            ->willReturn($value);

        $this->assertEquals($value, $cachedSettingsManager->all($owner));
    }

    public function testSet()
    {
        $owner = $this->createOwner();
        $name = 'name';
        $value = 'foobar';

        $settingsManager = \Mockery::mock(SettingsManager::class);
        $settingsManager->shouldReceive('set')->once()->with($name, $value, $owner);

        $cachedSettingsManager = $this->getMockBuilder(CachedSettingsManager::class)
            ->setMethods(array('invalidateCache'))
            ->setConstructorArgs(array($settingsManager, $this->getMock(CacheItemPoolInterface::class), 4711))
            ->getMock();

        // Clear the cache
        $cachedSettingsManager->expects($this->at(0))
            ->method('invalidateCache')
            ->with($this->equalTo($name), $this->equalTo($owner))
            ->willReturn(null);

        // Clear all cache for this owner
        $cachedSettingsManager->expects($this->at(1))
            ->method('invalidateCache')
            ->with($this->equalTo(null), $this->equalTo($owner))
            ->willReturn(null);

        $cachedSettingsManager->set($name, $value, $owner);
    }

    public function testSetMany()
    {
        $owner = $this->createOwner();
        $settings = array('name0' => 'value0', 'name1' => 'value1', 'name2' => 'value2');

        $settingsManager = \Mockery::mock(SettingsManager::class);
        $settingsManager->shouldReceive('setMany')->once()->with($settings, $owner);

        $cachedSettingsManager = $this->getMockBuilder(CachedSettingsManager::class)
            ->setMethods(array('invalidateCache'))
            ->setConstructorArgs(array($settingsManager, $this->getMock(CacheItemPoolInterface::class), 4711))
            ->getMock();
        $cachedSettingsManager->expects($this->exactly(4))
            ->method('invalidateCache')
            ->with($this->logicalOr('name0', 'name1', 'name2', null), $owner);

        $cachedSettingsManager->setMany($settings, $owner);
    }

    public function testClear()
    {
        $owner = $this->createOwner();
        $name = 'name';

        $settingsManager = \Mockery::mock(SettingsManager::class);
        $settingsManager->shouldReceive('clear')->once()->with($name, $owner);

        $cachedSettingsManager = $this->getMockBuilder(CachedSettingsManager::class)
            ->setMethods(array('invalidateCache'))
            ->setConstructorArgs(array($settingsManager, $this->getMock(CacheItemPoolInterface::class), 4711))
            ->getMock();
        $cachedSettingsManager->expects($this->at(0))
            ->method('invalidateCache')
            ->with($this->equalTo($name), $this->equalTo($owner))
            ->willReturn(null);
        $cachedSettingsManager->expects($this->at(1))
            ->method('invalidateCache')
            ->with($this->equalTo(null), $this->equalTo($owner))
            ->willReturn(null);

        $cachedSettingsManager->clear($name, $owner);
    }

    /**
     * Make sure we do always return a string, no matter input.
     */
    public function testGetCacheKey()
    {
        $name = 'name';
        $owner = $this->createOwner();

        $getCacheKey = new \ReflectionMethod(CachedSettingsManager::class, 'getCacheKey');
        $getCacheKey->setAccessible(true);

        $cachedSettingsManager = $this->getMockBuilder(CachedSettingsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $getCacheKey->invoke($cachedSettingsManager, $name, $owner);
        $getCacheKey->invoke($cachedSettingsManager, $name, null);
        $getCacheKey->invoke($cachedSettingsManager, null, $owner);
        $getCacheKey->invoke($cachedSettingsManager, null, null);
    }

    /**
     * @param string $ownerId
     *
     * @return \Mhary\SettingsBundle\Entity\SettingsOwnerInterface
     */
    protected function createOwner($ownerId = 'user1')
    {
        return \Mockery::mock(
            'Mhary\SettingsBundle\Entity\SettingsOwnerInterface',
            array('getSettingIdentifier' => $ownerId)
        );
    }
}
