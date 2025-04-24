<?php
namespace Pixable\LBM;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteApiCommand extends Command
{
    protected static $defaultName = 'wp-shaper:delete-api';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete a REST API endpoint and update class-api.php.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the API (use directory structure if needed, e.g., UserManagement/user_login)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $parts = explode('/', $name);
        $fileName = array_pop($parts);
        $directoryPath = implode(DIRECTORY_SEPARATOR, $parts);

        $fileName = strtolower($fileName);
        $className = implode('', array_map('ucfirst', explode('_', $fileName)));

        $baseDirectory = realpath(__DIR__ . '/../includes/api') . DIRECTORY_SEPARATOR;
        $phpFilePath = rtrim($baseDirectory . strtolower($directoryPath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "{$fileName}.php";
        $apiFilePath = $baseDirectory . 'class-api.php';

        // Helper to remove empty directories
        $removeDirIfEmpty = function ($dir, $stopAt) use ($output) {
            while (is_dir($dir) && count(scandir($dir)) === 2 && $dir !== $stopAt) {
                rmdir($dir);
                $output->writeln("Deleted directory: {$dir}");
                $dir = dirname($dir);
            }
        };

        // Delete the API PHP file
        if (file_exists($phpFilePath)) {
            unlink($phpFilePath);
            $output->writeln("Deleted API PHP file: {$phpFilePath}");
        } else {
            $output->writeln("API PHP file not found: {$phpFilePath}");
        }

        // Attempt to clean up empty directories
        $removeDirIfEmpty($baseDirectory . strtolower($directoryPath), $baseDirectory);

        // ✅ Update class-api.php to remove use and init of LBMCore\{ClassName}
        if (file_exists($apiFilePath)) {
            $apiContent = file_get_contents($apiFilePath);

            $useStatement = "use LBMCore\\{$className};";
            $apiContent = str_replace("\n{$useStatement}", '', $apiContent);

            $initStatement = "{$className}::init();";
            $apiContent = str_replace("\t\t{$initStatement}\n", '', $apiContent);

            file_put_contents($apiFilePath, $apiContent);
            $output->writeln("Updated class-api.php: {$apiFilePath}");
        } else {
            $output->writeln("class-api.php not found: {$apiFilePath}");
        }

        $output->writeln("✅ API '{$fileName}' and associated files deleted successfully.");

        return Command::SUCCESS;
    }
}
