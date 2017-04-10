<?php

namespace Tests\AppBundle\Utility;

use AppBundle\Exeption\FormatFileExeption;
use PHPUnit\Framework\TestCase;
use AppBundle\Utility\HelperUtility;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;
use Doctrine\ORM\EntityManager;
use Ddeboer\DataImport\Reader;

class HelperUtilityTest extends TestCase
{
    private $validator;
    private $em;
    private $reader;

    public function setUp()
    {
        $this->validator = $this->createMock(Validator::class);
        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reader = new HelperUtility($this->validator, $this->em);
    }

    public function testGetReaderWithBadFormat()
    {
        $filename = __DIR__.'/../Fixture/not_csv_file.txt';

        $this->expectException(FormatFileExeption::class);
        $this->reader->getReader($filename);
    }

    public function testGetReaderWithNonexistentFile()
    {
        $filename = __DIR__.'/../Fixture/nonexisten_file.csv';

        $this->expectException(FileNotFoundException::class);
        $this->reader->getReader($filename);
    }

    public function testGetReaderWithTrueFile()
    {
        $filename = __DIR__.'/../Fixture/stock.csv';
        $result = $this->reader->getReader($filename);

        $this->assertInstanceOf(Reader::class, $result);
    }
}