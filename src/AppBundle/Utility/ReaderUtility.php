<?php

namespace AppBundle\Utility;

use AppBundle\Exeption\FormatFileExeption;
use Ddeboer\DataImport\Reader;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ReaderUtility
{
    public static function getReader($filename)
    {
        $reader = null;
        $fileInfo = new \SplFileInfo($filename);

        if ($fileInfo->getExtension() === 'csv') {
            $reader = self::getCsvReader($filename);
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
}