<?php

namespace Illuminate\Tests\Container;

use stdClass;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;

class ContainerExtendTest extends TestCase
{
    public function testExtendedBindings()
    {
        $container = new Container;
        $container['foo'] = 'foo';
        $container->extend('foo', function ($old, $container) {
            return $old.'bar';
        });

        $this->assertEquals('foobar', $container->make('foo'));

        $container = new Container;

        $container->singleton('foo', function () {
            return (object) ['name' => 'taylor'];
        });
        $container->extend('foo', function ($old, $container) {
            $old->age = 26;

            return $old;
        });

        $result = $container->make('foo');

        $this->assertEquals('taylor', $result->name);
        $this->assertEquals(26, $result->age);
        $this->assertSame($result, $container->make('foo'));
    }

    public function testExtendInstancesArePreserved()
    {
        $container = new Container;
        $container->bind('foo', function () {
            $obj = new stdClass;
            $obj->foo = 'bar';

            return $obj;
        });

        $obj = new stdClass;
        $obj->foo = 'foo';
        $container->instance('foo', $obj);
        $container->extend('foo', function ($obj, $container) {
            $obj->bar = 'baz';

            return $obj;
        });
        $container->extend('foo', function ($obj, $container) {
            $obj->baz = 'foo';

            return $obj;
        });

        $this->assertEquals('foo', $container->make('foo')->foo);
        $this->assertEquals('baz', $container->make('foo')->bar);
        $this->assertEquals('foo', $container->make('foo')->baz);
    }

    public function testExtendIsLazyInitialized()
    {
        ContainerLazyExtendStub::$initialized = false;

        $container = new Container;
        $container->bind(ContainerLazyExtendStub::class);
        $container->extend(ContainerLazyExtendStub::class, function ($obj, $container) {
            $obj->init();

            return $obj;
        });
        $this->assertFalse(ContainerLazyExtendStub::$initialized);
        $container->make(ContainerLazyExtendStub::class);
        $this->assertTrue(ContainerLazyExtendStub::$initialized);
    }

    public function testExtendCanBeCalledBeforeBind()
    {
        $container = new Container;
        $container->extend('foo', function ($old, $container) {
            return $old.'bar';
        });
        $container['foo'] = 'foo';

        $this->assertEquals('foobar', $container->make('foo'));
    }

    public function testExtendInstanceRebindingCallback()
    {
        $_SERVER['_test_rebind'] = false;

        $container = new Container;
        $container->rebinding('foo', function () {
            $_SERVER['_test_rebind'] = true;
        });

        $obj = new stdClass;
        $container->instance('foo', $obj);

        $container->extend('foo', function ($obj, $container) {
            return $obj;
        });

        $this->assertTrue($_SERVER['_test_rebind']);
    }

    public function testExtendBindRebindingCallback()
    {
        $_SERVER['_test_rebind'] = false;

        $container = new Container;
        $container->rebinding('foo', function () {
            $_SERVER['_test_rebind'] = true;
        });
        $container->bind('foo', function () {
            return new stdClass;
        });

        $this->assertFalse($_SERVER['_test_rebind']);

        $container->make('foo');

        $container->extend('foo', function ($obj, $container) {
            return $obj;
        });

        $this->assertTrue($_SERVER['_test_rebind']);
    }

    public function testExtensionWorksOnAliasedBindings()
    {
        $container = new Container;
        $container->singleton('something', function () {
            return 'some value';
        });
        $container->alias('something', 'something-alias');
        $container->extend('something-alias', function ($value) {
            return $value.' extended';
        });

        $this->assertEquals('some value extended', $container->make('something'));
    }

    public function testMultipleExtends()
    {
        $container = new Container;
        $container['foo'] = 'foo';
        $container->extend('foo', function ($old, $container) {
            return $old.'bar';
        });
        $container->extend('foo', function ($old, $container) {
            return $old.'baz';
        });

        $this->assertEquals('foobarbaz', $container->make('foo'));
    }

    public function testUnsetExtend()
    {
        $container = new Container;
        $container->bind('foo', function () {
            $obj = new stdClass;
            $obj->foo = 'bar';

            return $obj;
        });

        $container->extend('foo', function ($obj, $container) {
            $obj->bar = 'baz';

            return $obj;
        });

        unset($container['foo']);
        $container->forgetExtenders('foo');

        $container->bind('foo', function () {
            return 'foo';
        });

        $this->assertEquals('foo', $container->make('foo'));
    }
}

class ContainerLazyExtendStub
{
    public static $initialized = false;

    public function init()
    {
        static::$initialized = true;
    }
}
