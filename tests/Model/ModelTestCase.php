<?php

namespace Slince\Shopify\Tests\Model;


use Doctrine\Common\Inflector\Inflector;
use Slince\Shopify\Hydrator\Hydrator;
use Slince\Shopify\Tests\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

abstract class ModelTestCase extends TestCase
{
    /**
     * @var Hydrator
     */
    protected $hydrator;

    /**
     * @var PropertyInfoExtractor
     */
    protected $extractor;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    public function setUp(): void
    {
        parent::setUp();
        $this->hydrator = new Hydrator();
        $this->extractor = new ReflectionExtractor();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    abstract protected function getModelClass();

    public function createModel()
    {
        $modelClass = $this->getModelClass();
        $fixtures = basename($modelClass);
        $data = $this->readFixture("{$fixtures}/view.json");
        $obj = $this->hydrator->hydrate($modelClass, reset($data));
        return [$obj, reset($data)];
    }

    public function testProperty()
    {
        list($obj, $data) = $this->createModel();
        $modelClass = $this->getModelClass();
        foreach ($this->extractor->getProperties($modelClass) as $property) {
            $snake = Inflector::tableize($property);
            $value = $this->propertyAccessor->getValue($obj, $property);
            if (!isset($data[$snake])) {
                continue;
            }
            if (is_scalar($value) || is_null($value)) {
                $this->assertEquals($value, $data[$snake]);
            } else {
                $types = $this->extractor->getTypes($modelClass, $property);
                if (null === $types) { //generic array
                    $this->assertIsArray($value);
                    continue;
                }
//                if ($types === null) {
//                    var_dump($modelClass, $property, $value);
//                }
                if ($types[0]->getBuiltinType() === 'array') {
                    if (null === $types[0]->getCollectionValueType()) {
                        var_dump($modelClass, $property, $value, $types);exit;
                    }
                    $this->assertInstanceOf($types[0]->getCollectionValueType()->getClassName(), $value[0]);
                } else {
                    $this->assertInstanceOf($types[0]->getClassName(), $value);
                }
            }
        }
    }
}