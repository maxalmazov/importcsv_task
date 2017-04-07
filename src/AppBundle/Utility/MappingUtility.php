<?php

namespace AppBundle\Utility;

use AppBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
class MappingUtility
{

    private $validator;

    private $container;

    /**
     * MappingUtility constructor.
     *
     * @param $validator
     * @param $container
     */
    public function __construct(Validator $validator, Container $container)
    {
        $this->validator = $validator;
        $this->container = $container;
    }

    public function getMapping()
    {
        return [
            '[Product Code]' => '[productCode]',
            '[Product Name]' => '[productName]',
            '[Product Description]' => '[productDesc]',
            '[Stock]' => '[stock]',
            '[Cost in GBP]' => '[price]',
            '[Discontinued]' => '[dateDiscontinued]',
        ];
    }

    public function getConstraints()
    {
        $constraints = [];

        /**
         * @var $metadata \Symfony\Component\Validator\Mapping\ClassMetadata
         * */
        $metadata = $this->validator->getMetadataFor(new Product());
        foreach ($metadata->properties as $attribute => $propertyMetadata) {
            foreach ($propertyMetadata->getConstraints() as $constraint) {
                $constraints[$attribute][] = $constraint;
            }
        }

        return $constraints;
    }
}