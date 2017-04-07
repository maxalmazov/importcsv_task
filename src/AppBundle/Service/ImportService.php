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
 use function PHPSTORM_META\elementType;
 use Symfony\Component\Console\Output\OutputInterface;
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
      * @var $errorsImport ErrorImport
      */
     private $errorsImport = [];

     private $totalProcessed;

     private $successProcessed;

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

     public function import(Reader $reader, Writer $writer, $output)
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
             if ($item['price']<self::MIN_PRICE && $item['stock']<self::MIN_STOCK) {
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
             ->process($output);
         ;

         $this->totalProcessed = $result->getTotalProcessedCount() + count($reader->getErrors());
         $this->successProcessed = $result->getSuccessCount();

         if ($result->hasErrors()) {
             foreach ($result->getExceptions() as $exception) {
                 foreach ($exception->getViolations() as $violation) {
                     $error = new ErrorImport($violation->getRoot()['productCode'], $violation->getMessage());
                     $this->setError($error);
                 }
             }
         }

         foreach ($reader->getErrors() as $invalidItem) {
             $error = new ErrorImport($invalidItem[0],'Invalid item in CSV file');
             $this->setError($error);
         }
     }

     private function setError(ErrorImport $error)
     {
         if ($this->isErrorExist($error) instanceof ErrorImport) {
             $errorImport = $this->isErrorExist($error);
             $newMessage = $errorImport->getMessage().' && '.$error->getMessage();
             $errorImport->setMessage($newMessage);
         } else {
             $this->errorsImport[] = $error;
         }

         return $this;
     }

     private function isErrorExist($error)
     {
         /**
          * @var $error ErrorImport
          * @var $errorImport ErrorImport
          */
         foreach ($this->errorsImport as $errorImport) {
             if ($errorImport->getProductCode() === $error->getProductCode()) {
                 return $errorImport;
             }
         }
         return false;
     }

     /**
      * @return mixed
      */
     public function getTotalProcessed()
     {
         return $this->totalProcessed;
     }

     /**
      * @return mixed
      */
     public function getSuccessProcessed()
     {
         return $this->successProcessed;
     }

     /**
      * @return ErrorImport
      */
     public function getErrorsImport()
     {
         return $this->errorsImport;
     }
 }