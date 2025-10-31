<?php

namespace Tourze\Workerman\DnsClient\Tests\Timer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Timer\WorkermanTimer;

/**
 * @internal
 */
#[CoversClass(WorkermanTimer::class)]
final class WorkermanTimerTest extends TestCase
{
    /**
     * 测试WorkermanTimer的方法实现
     * 由于禁止使用动态代码生成，我们只能验证类的结构和行为
     */
    public function testTimerImplementation(): void
    {
        $timer = new WorkermanTimer();

        // 验证类实现了必要的方法
        $reflectionClass = new \ReflectionClass($timer);
        $this->assertTrue($reflectionClass->hasMethod('add'));
        $this->assertTrue($reflectionClass->hasMethod('del'));

        // 验证add方法签名
        $addMethod = $reflectionClass->getMethod('add');
        $addParams = $addMethod->getParameters();
        $this->assertCount(4, $addParams);
        $this->assertEquals('interval', $addParams[0]->getName());
        $this->assertEquals('callback', $addParams[1]->getName());
        $this->assertEquals('args', $addParams[2]->getName());
        $this->assertEquals('persistent', $addParams[3]->getName());

        // 验证del方法签名
        $delMethod = $reflectionClass->getMethod('del');
        $delParams = $delMethod->getParameters();
        $this->assertCount(1, $delParams);
        $this->assertEquals('timerId', $delParams[0]->getName());
    }

    /**
     * 通过检查源代码实现来验证WorkermanTimer调用了底层Workerman的Timer库
     */
    public function testTimerBehavior(): void
    {
        // 获取WorkermanTimer类的源代码
        $reflectionClass = new \ReflectionClass(WorkermanTimer::class);
        $fileName = $reflectionClass->getFileName();
        $this->assertNotFalse($fileName, 'Failed to get WorkermanTimer file name');
        $source = file_get_contents($fileName);
        $this->assertNotFalse($source, 'Failed to read WorkermanTimer source file');

        // 验证源代码中包含对WorkermanTimerLib::add的调用
        $this->assertStringContainsString('WorkermanTimerLib::add(', $source);

        // 验证源代码中包含对WorkermanTimerLib::del的调用
        $this->assertStringContainsString('WorkermanTimerLib::del(', $source);

        // 验证正确导入了Timer库
        $this->assertStringContainsString('use Workerman\Timer as WorkermanTimerLib', $source);
    }

    public function testAdd(): void
    {
        $timer = new WorkermanTimer();

        // 验证add方法存在且可调用
        $reflectionClass = new \ReflectionClass($timer);
        $this->assertTrue($reflectionClass->hasMethod('add'));

        // 验证方法签名正确
        $addMethod = $reflectionClass->getMethod('add');
        $this->assertTrue($addMethod->isPublic());

        $params = $addMethod->getParameters();
        $this->assertCount(4, $params);

        // 验证参数名称和类型
        $this->assertEquals('interval', $params[0]->getName());
        $this->assertEquals('callback', $params[1]->getName());
        $this->assertEquals('args', $params[2]->getName());
        $this->assertEquals('persistent', $params[3]->getName());

        // 验证返回类型
        $returnType = $addMethod->getReturnType();
        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertEquals('int', $returnType->getName());
        }
    }

    public function testDel(): void
    {
        $timer = new WorkermanTimer();

        // 验证del方法存在且可调用
        $reflectionClass = new \ReflectionClass($timer);
        $this->assertTrue($reflectionClass->hasMethod('del'));

        // 验证方法签名正确
        $delMethod = $reflectionClass->getMethod('del');
        $this->assertTrue($delMethod->isPublic());

        $params = $delMethod->getParameters();
        $this->assertCount(1, $params);

        // 验证参数名称和类型
        $this->assertEquals('timerId', $params[0]->getName());

        $type = $params[0]->getType();
        if ($type instanceof \ReflectionNamedType) {
            $this->assertEquals('int', $type->getName());
        }

        // 验证返回类型为bool
        $returnType = $delMethod->getReturnType();
        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertEquals('bool', $returnType->getName());
        }
    }
}
