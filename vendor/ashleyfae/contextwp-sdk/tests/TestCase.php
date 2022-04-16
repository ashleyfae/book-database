<?php
/**
 * TestCase.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests;

use Exception;
use Mockery;
use Mockery\Expectation;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use WP_Mock;

class TestCase extends \WP_Mock\Tools\TestCase
{
    /**
     * Makes a protected method public for the given class, so it can be tested.
     *
     * @param  string|object  $class  Class name or instance of it.
     * @param  string  $methodName  Name of the method.
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected function getInaccessibleMethod($class, string $methodName): ReflectionMethod
    {
        $class = new ReflectionClass($class);

        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Invokes an inaccessible method and returns the result.
     *
     * @param  object  $class  Instance of the class.
     * @param  string  $methodName  Name of the method.
     * @param  mixed  ...$args  Arguments to pass to the method.
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function invokeInaccessibleMethod($class, string $methodName, ...$args)
    {
        return $this->getInaccessibleMethod($class, $methodName)
            ->invoke($class, ...$args);
    }

    /**
     * Makes a protected property public for the given class, so it can be tested.
     *
     * @param  string|object  $class  Class name or instance of it.
     * @param  string  $propertyName  Name of the property.
     *
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    protected function getInaccessibleProperty($class, string $propertyName): ReflectionProperty
    {
        $class = new ReflectionClass($class);

        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    /**
     * Sets the value of a protected property.
     *
     * @param  object  $classInstance  Instance of the class.
     * @param  string  $propertyName  Name of the property.
     * @param  mixed  $propertyValue  Desired property value.
     *
     * @return void
     * @throws ReflectionException
     */
    protected function setInaccessibleProperty($classInstance, string $propertyName, $propertyValue): void
    {
        $class = new ReflectionClass($classInstance);

        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($classInstance, $propertyValue);
    }

    /**
     * Mock a static method of a class.
     *
     * Copied from {@see WP_Mock\Tools\TestCase::mockStaticMethod()}.
     * This is overridden until this PR is merged: {@link https://github.com/10up/wp_mock/pull/165}
     *
     * @param string $class  The classname or class::method name
     * @param null|string $method The method name. Optional if class::method used for $class
     *
     * @return Expectation
     * @throws Exception
     */
    protected function mockStaticMethod($class, $method = null)
    {
        if (! $method) {
            list($class, $method) = (explode('::', $class) + [null, null]);
        }
        if (! $method) {
            throw new Exception(sprintf('Could not mock %s::%s', $class, $method));
        }
        if (! WP_Mock::usingPatchwork() || ! function_exists('Patchwork\redefine')) {
            throw new Exception('Patchwork is not loaded! Please load patchwork before mocking static methods!');
        }

        $safe_method = "wp_mock_safe_${method}";
        $signature = md5("${class}::${method}");

        if (! empty($this->mockedStaticMethods[$signature])) {
            $mock = $this->mockedStaticMethods[$signature];
        } else {
            $rMethod = false;
            if (class_exists($class)) {
                $rMethod = new ReflectionMethod($class, $method);
            }
            if (
                $rMethod &&
                (
                    ! $rMethod->isUserDefined() ||
                    ! $rMethod->isStatic() ||
                    $rMethod->isPrivate()
                )
            ) {
                throw new Exception(sprintf('%s::%s is not a user-defined non-private static method!', $class, $method));
            }

            /** @var \Mockery\Mock $mock */
            $mock = Mockery::mock($class);
            $mock->shouldAllowMockingProtectedMethods();
            $this->mockedStaticMethods[$signature] = $mock;

            \Patchwork\redefine("${class}::${method}", function () use ($mock, $safe_method) {
                return call_user_func_array([$mock, $safe_method], func_get_args());
            });
        }

        return $mock->shouldReceive($safe_method);
    }
}
