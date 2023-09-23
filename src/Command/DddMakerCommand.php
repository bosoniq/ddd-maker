<?php

namespace App\Command;

use App\Entity\Request;
use App\Entity\ToGenerate;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

#[AsCommand(
    name: 'app:ddd-maker',
    description: 'DDD code generator',
    hidden: false
)]
class DddMakerCommand extends Command
{
    /* // the command description shown when running "php bin/console list" */
    /* protected static $defaultDescription = 'Creates a new user.'; */

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
        // TODO: validate project dir structure

        $config = $this->loadConfig(getcwd());

        // TODO Validate config structure

        $request = Request::fromInput($input, $config);

        $newClasses = $this->prepareNewClassObjects($request, $config);

        // Write files according to make file Dto's
        //      create missing dirs from Dto
        //      do final script write operation
        $this->writeFiles($newClasses);


        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }

    /** @param array<ToGenerate> $files */
    private function writeFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->pathPutContents($file->path(), $file->content());
        }

        echo "Files written successfully";
    }

    private function pathPutContents(string $filePath, string $contents): void
    {
        if (!is_dir($dir = implode('/', explode('/', $filePath, -1)))) {
            mkdir($dir, 0777, true);
        }

        // TODO: Handle errors here
        file_put_contents($filePath, $contents, 0);
    }

    /**
     * @param array{
     *   project_namespace: string,
     *   templates: array<string, array{template: string, additional: array<string>}>
     * } $config
     * @return array<ToGenerate>
     */
    private function prepareNewClassObjects(Request $request, array $config): array
    {
        $toGenerate = [];
        $templateKeys = $this->getTemplateKeys($request, $config);

        foreach ($templateKeys as $key) {
            $this->ensureConfigKeyExists($config, $key);

            $content = $this->readTemplateContent($config['templates'][$key]['template']);

            $toGenerate[] = ToGenerate::fromRequest($request, $content, $key);
        }

        return $toGenerate;
    }

    private function readTemplateContent(string $templateName): string
    {
        return $this->readFile(sprintf('%s/../../%s', __DIR__, $templateName));
    }

    private function ensureConfigKeyExists(array $config, string $type): void
    {
        if (!array_key_exists($type, $config['templates'])) {
            throw new Exception('Requested type does not exist!');
        }
    }

    private function getTemplateKeys(Request $request, array $config): array
    {
        $this->ensureConfigKeyExists($config, $request->type());

        $additional = $config['templates'][$request->type()]['additional'] ?? [];

        return [$request->type(), ...$additional];
    }

    private function loadConfig(string $projectPath): array
    {
        // load defaults
        $default = $this->readFile(__DIR__.'/../../config/config.json');
        $default = json_decode($default, true);

        try {
            // load custom config
            $custom = $this->readFile($projectPath.'.maker.json');
            $custom = json_decode($custom, true);

            return array_merge($default, $custom);
        } catch (FileNotFoundException) {
            return $default;
        }
    }

    /** @throws FileNotFoundException */
    private function readFile(string $path): string
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException();
        }

        $content = file_get_contents($path);

        if (is_bool($content)) {
            throw new FileNotFoundException(); // TODO: FileCannotBeReadException
        }

        return $content;
    }
}
