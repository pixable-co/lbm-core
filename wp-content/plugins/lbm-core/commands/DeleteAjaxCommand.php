<?php
namespace Pixable\LBM;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteAjaxCommand extends Command
{
    protected static $defaultName = 'wp-shaper:delete-ajax';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete an AJAX handler and its references in class-ajax.php.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the AJAX handler to delete (e.g., NamespaceFolder/my_ajax_call)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $parts = explode('/', $name);
        $ajaxName = array_pop($parts);
        $namespacePath = implode(DIRECTORY_SEPARATOR, $parts);

        $className = implode('', array_map('ucfirst', explode('_', $ajaxName)));

        $baseDirectory = realpath(__DIR__ . '/../includes/ajax') . DIRECTORY_SEPARATOR;
        $namespaceDirectory = $baseDirectory . strtolower($namespacePath);
        $phpFilePath = rtrim($namespaceDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "{$ajaxName}.php";
        $ajaxClassFilePath = $baseDirectory . 'class-ajax.php';

        // Delete AJAX file
        if (file_exists($phpFilePath)) {
            unlink($phpFilePath);
            $output->writeln("<info>Deleted PHP file: {$phpFilePath}</info>");
        } else {
            $output->writeln("<error>PHP file not found: {$phpFilePath}</error>");
        }

        // Clean up empty directories
        $this->removeEmptyFolders($namespaceDirectory, $baseDirectory, $output);

        // ✅ Update class-ajax.php to remove use/init of LBMCore class
        if (file_exists($ajaxClassFilePath)) {
            $ajaxContent = file_get_contents($ajaxClassFilePath);

            $useStatement = "use LBMCore\\{$className};";
            $ajaxContent = str_replace("\n{$useStatement}", '', $ajaxContent);

            $initStatement = "{$className}::init();";
            $ajaxContent = str_replace("\t\t{$initStatement}\n", '', $ajaxContent);

            file_put_contents($ajaxClassFilePath, $ajaxContent);
            $output->writeln("<info>Updated class-ajax.php to remove references to '{$ajaxName}'.</info>");
        } else {
            $output->writeln("<error>class-ajax.php not found: {$ajaxClassFilePath}</error>");
        }

        $output->writeln("<info>✅ AJAX handler '{$ajaxName}' deleted successfully.</info>");
        return Command::SUCCESS;
    }

    /**
     * Recursively removes empty folders up to the base directory.
     *
     * @param string $dir
     * @param string $baseDirectory
     * @param OutputInterface $output
     */
    private function removeEmptyFolders(string $dir, string $baseDirectory, OutputInterface $output)
    {
        while (is_dir($dir) && count(scandir($dir)) === 2 && $dir !== $baseDirectory) {
            rmdir($dir);
            $output->writeln("<info>Deleted empty directory: {$dir}</info>");
            $dir = dirname($dir);
        }
    }
}
