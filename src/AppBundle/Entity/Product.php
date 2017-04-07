<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="tblProductData")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @var int
     * @ORM\Column(name="productId", type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Product code should not be blank")
     * @Assert\Type(type="string")
     *
     * @ORM\Column(name="productCode", type="string", length=10, unique=true)
     */
    private $productCode;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Product name should not be blank");
     * @Assert\Type(type="string");
     *
     * @ORM\Column(name="productName", type="string", length=50)
     */
    private $productName;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Product desc should not be blank");
     * @Assert\Type(type="string")
     *
     * @ORM\Column(name="productDesc", type="string", length=255)
     */
    private $productDesc;

    /**
     * @var \DateTime
     * @ORM\Column(name="dtmAdded", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @Assert\DateTime(message="This property should be a DateTime")
     *
     * @ORM\Column(name="dtmDiscontinued", type="datetime", nullable=true)
     */
    private $dateDiscontinued;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="Stock should not be blank")
     * @Assert\Type(type="numeric", message="Stock must be of type integer")
     *
     * @ORM\Column(name="stock", type="integer")
     */
    private $stock;

    /**
     * @var float
     *
     * @Assert\NotBlank(message="Price should not be blank")
     * @Assert\Type(type="numeric", message="Price must be of type float")
     * @Assert\LessThan(value=1000, message="Price should be less then 1000")
     *
     * @ORM\Column(name="price", type="float", options={"unsigned"=true})
     */
    private $price;

    /**
     * @var \DateTime
     * @ORM\Column(name="stmTimestamp", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getProductCode()
    {
        return $this->productCode;
    }

    /**
     * @param string $productCode
     */
    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

    /**
     * @return string
     */
    public function getProductDesc()
    {
        return $this->productDesc;
    }

    /**
     * @param string $productDesc
     */
    public function setProductDesc($productDesc)
    {
        $this->productDesc = $productDesc;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateDiscontinued()
    {
        return $this->dateDiscontinued;
    }

    /**
     * @param \DateTime $dateDiscontinued
     */
    public function setDateDiscontinued(\DateTime $dateDiscontinued)
    {
        $this->dateDiscontinued = $dateDiscontinued;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }
}