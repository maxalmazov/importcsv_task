<?php

 namespace AppBundle\Service;

 use AppBundle\Utility\ErrorImport;
 use AppBundle\Utility\HelperUtility;
 use Ddeboer\DataImport\Reader;
 use Ddeboer\DataImport\Writer;
 use Ddeboer\DataImport\Step\MappingStep;
 use Ddeboer\DataImport\Step\ValueConverterStep;
 use Ddeboer\DataImport\Step\ValidatorStep;
 use Ddeboer\DataImport\Step\FilterStep;
 use Ddeboer\DataImport\Workflow\StepAggregator as Workflow;
 use Doctrine\ORM\EntityManager;
 use Symfony\Component\Console\Output\OutputInterface;
 use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;

 class ImportService
 {
     const MAX_PRICE = 1000;
     const MIN_PRICE = 5;
     const MIN_STOCK = 10;

     private $em;

     private $validator;

     private $helper;

     private $errorsImport = [];

     private $totalProcessed;

     private $successProcessed;

     public function __construct(EntityManager $em, Validator $validator, HelperUtility $helper)
     {
         $this->em = $em;
         $this->validator = $validator;
         $this->helper = $helper;
     }

     public function import(Reader $reader, Writer $writer, OutputInterface $output)
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
             ->addStep($filter, 1)
             ->addStep($validate, 2)
             ->addStep($converter, 3)
             ->addStep($mapping, 4)
             ->addWriter($writer)
             ->process($output);
         ;

         $this->totalProcessed = $result->getTotalProcessedCount() + count($reader->getErrors());
         $this->successProcessed = $result->getSuccessCount();

         //Validation and filter errors
         if ($result->hasErrors()) {
             foreach ($result->getExceptions() as $exception) {
                 foreach ($exception->getViolations() as $violation) {
                     $error = new ErrorImport($violation->getRoot()['productCode'], $violation->getMessage());
                     $this->setError($error);
                 }
             }
         }

         //Errors of incorrect formatting in CSV
         foreach ($reader->getErrors() as $invalidItem) {
             $error = new ErrorImport($invalidItem[0],'Invalid item in CSV file');
             $this->setError($error);
         }
     }

     /**
      * Add error to array $errorsImport or add message if error with this productCode already exist
      *
      * @param ErrorImport $error
      * @return $this
      */
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

         foreach ($this->errorsImport as $errorImport) {
             if ($errorImport->getProductCode() === $error->getProductCode()) {
                 return $errorImport;
             }
         }
         return false;
     }

     /**
      * @return int
      */
     public function getTotalProcessed()
     {
         return $this->totalProcessed;
     }

     /**
      * @return int
      */
     public function getSuccessProcessed()
     {
         return $this->successProcessed;
     }

     /**
      * @return array
      */
     public function getErrorsImport()
     {
         return $this->errorsImport;
     }
 }