<?php

namespace AppBundle\Utility;

/**
 * Class ErrorImport
 * @package AppBundle\Utility
 */
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
     * @return string
     */
    public function getProductCode()
    {
        return $this->productCode;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}