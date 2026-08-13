<?php declare(strict_types = 1);

namespace kristijorgji\Enum\Tests;

class EnumTest extends \PHPUnit\Framework\TestCase
{
    private TestEnum $testEnum;

    public function setUp(): void
    {
        $this->testEnum = new TestEnum(TestEnum::ANOTHER_TEST);
    }

    public function testWrongConstruct(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $t = new TestEnum('this does not exist in constants');
        echo (string) $t;
    }

    public function testGetValueAsString(): void
    {
        $this->assertEquals('another test', (string) $this->testEnum);
    }

    public function testGetSelfKey(): void
    {
        $this->assertEquals('ANOTHER_TEST', $this->testEnum->getSelfKey());
    }

    public function testGetSelfKey_on_error(): void
    {
        $optionsProperty = $this->getPrivateProperty(\kristijorgji\Enum\Enum::class, 'options');
        $initialValue = $optionsProperty->getValue($this->testEnum);
        $changedValue = $initialValue;
        $changedValue[TestEnum::class]['ANOTHER_TEST'] = uniqid('x', true);
        $optionsProperty->setValue($this->testEnum, $changedValue);

        try {
            $this->testEnum->getSelfKey();
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\Throwable::class, $e);
            $optionsProperty->setValue($initialValue);
        }
    }

    public function testGetKey(): void
    {
        $this->assertEquals('TEST_4', TestEnum::getKey(6));
    }

    public function testGetKey_from_non_existing_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TestEnum::getKey('adfasdffdfafdasf');
    }

    public function testToArray(): void
    {
        $expected = [
            'TEST' => 0,
            'TEST_2'  => 2,
            'TEST_4'  => 6,
            'ANOTHER_TEST' => 'another test'
        ];

        $this->assertEquals($expected, TestEnum::toArray());
    }

    public function testGetValues(): void
    {
        $expected = [
            0,
            2,
            6,
            'another test'
        ];

        $this->assertEquals($expected, TestEnum::getValues());
    }

    private function getPrivateProperty(string $class, string $property): \ReflectionProperty
    {
        $ref = new \ReflectionProperty($class, $property);
        $ref->setAccessible(true);
        return $ref;
    }
}
