<?php

declare(strict_types=1);

/*
 * This file is part of the BazingaGeocoderBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Bazinga\GeocoderBundle\Tests\Mapping\Driver;

use Bazinga\GeocoderBundle\Mapping\Annotations\Address;
use Bazinga\GeocoderBundle\Mapping\Annotations\Geocodeable;
use Bazinga\GeocoderBundle\Mapping\Annotations\Latitude;
use Bazinga\GeocoderBundle\Mapping\Annotations\Longitude;
use Bazinga\GeocoderBundle\Mapping\Driver\AttributeDriver;
use Bazinga\GeocoderBundle\Mapping\Exception\MappingException;
use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\SetUpTearDownTrait;

/**
 * @author Pierre du Plessis <pdples@gmail.com>
 */
class AttributeDriverTest extends TestCase
{
    use SetUpTearDownTrait;

    /**
     * @var AttributeDriver
     */
    private $driver;

    /**
     * @var Reader
     */
    private $reader;

    protected function doSetUp(): void
    {
        $this->driver = new AttributeDriver();
    }

    /**
     * @requires PHP 8
     */
    public function testLoadMetadata()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped(sprintf('"%s" is only supported on PHP 8', AttributeDriver::class));
        }

        $obj = new Dummy3();
        $metadata = $this->driver->loadMetadataFromObject($obj);

        $this->assertInstanceOf('ReflectionProperty', $metadata->addressProperty);
        $this->assertInstanceOf('ReflectionProperty', $metadata->latitudeProperty);
        $this->assertInstanceOf('ReflectionProperty', $metadata->longitudeProperty);
    }

    /**
     * @requires PHP 8
     */
    public function testLoadMetadataFromWrongObject()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped(sprintf('"%s" is only supported on PHP 8', AttributeDriver::class));
        }

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage('The class '.Dummy4::class.' is not geocodeable');

        $this->driver->loadMetadataFromObject(new Dummy4());
    }

    /**
     * @requires PHP 8
     */
    public function testIsGeocodable()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped(sprintf('"%s" is only supported on PHP 8', AttributeDriver::class));
        }

        $this->assertTrue($this->driver->isGeocodeable(new Dummy3()));
    }
}

#[Geocodeable()]
class Dummy3
{
    #[Latitude()]
    public $latitude;

    #[Longitude()]
    public $longitude;

    #[Address()]
    public $address;
}

class Dummy4
{
}
