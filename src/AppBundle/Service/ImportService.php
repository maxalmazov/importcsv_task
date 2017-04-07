<?php

 namespace AppBundle\Service;

 use AppBundle\Utility\ErrorImport;
 use AppBundle\Utility\HelperUtility;
 use Ddeboer\DataImport\Reader;
 use Ddeboer\DataImport\Step\ValidatorStep;
 use Ddeboer\DataImport\Writer;
 use Ddeboer\DataImport\Step\MappingStep;
 use Ddeboer\DataImport\Step\ValueConverterStep;
 use Ddeboer\DataImport\Step\FilterStep;
 use Ddeboer\DataImport\Workflow\StepAggregator as Workflow;
 use Doctrine\ORM\EntityManager;
 use AppBundle\Entity\Product;
 use Symfony\Component\Validator\Constraints as Assert;
 use Symfony\Component\DependencyInjection\ContainerInterface as Container;
 use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;

 class ImportService
 {
     const MAX_PRICE = 1000;
     const MIN_PRICE = 5;
     const MIN_STOCK = 10;

     /**
      * @var $em EntityManager
      */
     private $em;

     /**
      * @var $validator Validator
      */
     private $validator;

     /**
      * @var $map HelperUtility
      */
     private $helper;

     /**
      * @var $errorImport ErrorImport
      */
     private $errorImport = [];

     private $totalProcessed;

     private $successPrcessed;

     /**
      * ImportService constructor.
      * @param EntityManager $em
      * @param Validator $validator
      * @param HelperUtility $helper
      */
     public function __construct(EntityManager $em, Validator $validator, HelperUtility $helper)
     {
         $this->em = $em;
         $this->validator = $validator;
         $this->helper = $helper;
     }

     public function import(Reader $reader, Writer $writer)
     {
         $mapping = new MappingStep($this->helper->getMapping());

         $converter = new ValueConverterStep();
         $converter->add('[dateDiscontinued]', function ($item){return $item === 'yes'? new \DateTime() : null;});

         $validate = new ValidatorStep($this->validator);
         $validate->throwExceptions(true);
         foreach ($this->helper->getConstraints() as $attribute => $constraints) {
             foreach ($constraints as $constraint) {
                 $validate->add($attribute, $constraint);
             }
         }

         $filter = new FilterStep();
         $priceAnStockfilter = function ($item) {
             if ($item['price']<self::MIN_PRICE &&  $item['stock']<self::MIN_STOCK) {
                 $message = 'Price < '.self::MIN_PRICE.' && stock < '.self::MIN_STOCK;
                 $error = new ErrorImport($item['productCode'], $message);
                 $this->setError($error);
                 return false;
             }
             return true;
         };
         $filter->add($priceAnStockfilter);

         $workflow = new Workflow($reader);
         $workflow->setSkipItemOnFailure(true);
         $result = $workflow
             ->addStep($mapping, 4)
             ->addStep($converter, 3)
             ->addStep($validate, 2)
             ->addStep($filter, 1)
             ->addWriter($writer)
             ->process();
         ;
     }

     private function setError(ErrorImport $error)
     {
         $this->errorImport[] = $error;

         return $this;
     }
 }