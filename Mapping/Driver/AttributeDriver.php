<?php

declare(strict_types=1);

/*
 * This file is part of the BazingaGeocoderBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Bazinga\GeocoderBundle\Mapping\Driver;

use Bazinga\GeocoderBundle\Mapping\Annotations;
use Bazinga\GeocoderBundle\Mapping\ClassMetadata;
use Bazinga\GeocoderBundle\Mapping\Exception\MappingException;
use Doctrine\Persistence\Proxy;

/**
 * @author Pierre du Plessis <pdples@gmail.com>
 */
class AttributeDriver implements DriverInterface
{
    public function isGeocodeable($object): bool
    {
        if (PHP_VERSION_ID < 80000) {
            return false;
        }

        $reflection = new \ReflectionObject($object);

        if ($object instanceof Proxy) {
            $reflection = $reflection->getParentClass();
        }

        return [] !== $reflection->getAttributes(Annotations\Geocodeable::class);
    }

    /**
     * @throws MappingException
     */
    public function loadMetadataFromObject($object): ClassMetadata
    {
        if (PHP_VERSION_ID < 80000) {
            throw new MappingException(sprintf('The class %s is not geocodeable', get_class($object)));
        }

        $reflection = new \ReflectionObject($object);

        if ($object instanceof Proxy) {
            $reflection = $reflection->getParentClass();
        }

        $attributes = $reflection->getAttributes(Annotations\Geocodeable::class);

        if ([] === $attributes) {
            throw new MappingException(sprintf('The class %s is not geocodeable', get_class($object)));
        }

        $metadata = new ClassMetadata();

        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {

                if ($attribute->getName() === Annotations\Latitude::class) {
                    $property->setAccessible(true);
                    $metadata->latitudeProperty = $property;
                } elseif ($attribute->getName() === Annotations\Longitude::class) {
                    $property->setAccessible(true);
                    $metadata->longitudeProperty = $property;
                } elseif ($attribute->getName() === Annotations\Address::class) {
                    $property->setAccessible(true);
                    $metadata->addressProperty = $property;
                }
            }
        }

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ([] !== $method->getAttributes(Annotations\Address::class)) {
                if (0 !== $method->getNumberOfRequiredParameters()) {
                    throw new MappingException('You can not use a method requiring parameters with #[Address] attribute!');
                }

                $metadata->addressGetter = $method;
            }
        }

        return $metadata;
    }
}
