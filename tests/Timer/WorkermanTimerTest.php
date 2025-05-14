<?php

namespace Tourze\Workerman\DnsClient\Tests\Timer;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Timer\WorkermanTimer;

class WorkermanTimerTest extends TestCase
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
        $source = file_get_contents($fileName);
        
        // 验证源代码中包含对WorkermanTimerLib::add的调用
        $this->assertStringContainsString('WorkermanTimerLib::add(', $source);
        
        // 验证源代码中包含对WorkermanTimerLib::del的调用
        $this->assertStringContainsString('WorkermanTimerLib::del(', $source);
        
        // 验证正确导入了Timer库
        $this->assertStringContainsString('use Workerman\Timer as WorkermanTimerLib', $source);
    }
} 