<?php

namespace AppBundle\Command;

use AppBundle\Exeption\FormatFileExeption;
use AppBundle\Utility\ErrorImport;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('import:csv')
            ->setDescription('Import CSV file to database')
            ->setHelp('This command import CSV file to database')
            ->addArgument('filename', InputArgument::REQUIRED, 'The path to file.')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Run test import without insert to database.')
            ->addOption('detailed', null, InputOption::VALUE_NONE, 'Run with detailed report')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $io = new SymfonyStyle($input,$output);
        $io->note(array(
            'The imported file must meet the following conditions:'
        ));
        $io->listing(array(
            'The file extension must be "*.csv"',
            'The first column must consist of the product code.',
            'All fields must be filled',
        ));
        $io->confirm('Do you want to continue?');

        if (!($input->getArgument('filename'))) {
            $question = new Question('<question>Choose the file (write path to file):</question> ', null);
            $filename  = $helper->ask($input, $output, $question);
            $input->setArgument('filename', $filename);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        try {
            $helper = $this->getContainer()->get('helper.utility');
            $reader = $helper->getReader($filename);
            $writer = $helper->getWriter($input);
        } catch (FormatFileExeption $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return;
        } catch (FileNotFoundException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return;
        }

        $import = $this->getContainer()->get('import.csv');
        $import->import($reader, $writer);

        $reportMessage = sprintf("\n".'Total processed: <info>%s</info> product. Imported: <info>%s</info> product. Fail: <info>%s</info>',
            $import->getTotalProcessed(),
            $import->getSuccessProcessed(),
            count($import->getErrorsImport())
        );
        $output->writeln($reportMessage);

        /**
         * @var $error ErrorImport
         */
        if ($input->getOption('detailed')) {
            $output->writeln('<fg=red>Next item was not imported:</>');
            foreach ($import->getErrorsImport() as $error) {
                $report = sprintf('<info>%s</info> - %s', $error->getProductCode(), $error->getMessage());
                $output->writeln($report);
            }

        }
    }
}