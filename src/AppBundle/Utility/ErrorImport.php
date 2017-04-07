<?php

namespace AppBundle\Utility;

class ErrorImport
{
    private $productCode;
    private $message;

    public function __construct($productCode, $message)
    {
        $this->productCode = $productCode;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getProductCode()
    {
        return $this->productCode;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
}