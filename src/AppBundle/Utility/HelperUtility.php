<?php

namespace AppBundle\Utility;

use AppBundle\Exeption\FormatFileExeption;
use AppBundle\Entity\Product;
use Ddeboer\DataImport\Reader;
use Ddeboer\DataImport\Writer\ArrayWriter;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;

class HelperUtility
{

    private $validator;
    private $em;

    /**
     * HelperUtility constructor.
     *
     * @param $validator Validator
     * @param $em EntityManager
     */
    public function __construct(Validator $validator, EntityManager $em)
    {
        $this->validator = $validator;
        $this->em = $em;
    }

    public function getReader($filename)
    {
        $reader = null;
        $fileInfo = new \SplFileInfo($filename);

        if ($fileInfo->getExtension() === 'csv') {
            $reader = $this->getCsvReader($filename);
        } else {
            throw new FormatFileExeption('It is not CSV file');
        }

        return $reader;
    }

    private function getCsvReader($filename)
    {
        try {
            $file = new \SplFileObject($filename);
            $reader = new Reader\CsvReader($file);
            $reader->setHeaderRowNumber(0);

            return $reader;
        } catch (\Exception $e) {
            throw new FileNotFoundException($filename);
        }
    }

    public function getWriter(InputInterface $input)
    {
        if ($input->getOption('test')) {
            $testWriter = [];
            $writer = new ArrayWriter($testWriter);

            return $writer;
        } else {
            $writer = new DoctrineWriter($this->em, 'AppBundle:Product', 'productCode');
            $writer->setTruncate(false);

            return $writer;
        }
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