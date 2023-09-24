<?php

namespace App\Command;

use App\Entity\Request;
use App\Service\ClassBuilder;
use App\Service\FileReader;
use App\Service\FileWriter;
use App\Service\Validator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

#[AsCommand(
    name: 'app:ddd-maker',
    description: 'DDD code generator',
    hidden: false
)]
class DddMakerCommand extends Command
{
    /* // the command description shown when running "php bin/console list" */
    /* protected static $defaultDescription = 'Creates a new user.'; */

    public function __construct(
        private readonly FileReader $fileReader,
        private FileWriter $fileWriter,
        private ClassBuilder $classBuilder,
        private readonly Validator $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'The type of class(es) to create')
            ->addArgument('context', InputArgument::REQUIRED, 'The context the class(es) should be made in ?')
            ->addArgument('prepend', InputArgument::REQUIRED, 'Name to prepend to generated class(es)')
            ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Parent directory name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->validator->validateProjectStructure();

            $config = $this->fileReader->loadConfig();

            $this->validator->validateConfigStructure($config);

            $request = Request::fromInput($input, $config);

            $newClasses = $this->classBuilder->prepareNewClassObjects($request, $config);

            $this->fileWriter->writeFiles($output, $newClasses);

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}
