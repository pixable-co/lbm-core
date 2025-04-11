<?php
namespace Pixable\LBM;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteShortcodeReactCommand extends Command
{
    protected static $defaultName = 'delete:shortcode-react';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete a React-based shortcode and its associated files.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the shortcode (use directory structure if needed, e.g., BookingForm/fh_booking_calendar)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $parts = explode('/', $name);
        $fileName = array_pop($parts);
        $directoryPath = implode(DIRECTORY_SEPARATOR, $parts);

        $fileName = strtolower($fileName);
        $className = implode('', array_map('ucfirst', explode('_', $fileName)));

        $baseDirectory = realpath(__DIR__ . '/../includes/shortcodes') . DIRECTORY_SEPARATOR;
        $phpFilePath = rtrim($baseDirectory . strtolower($directoryPath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "{$fileName}.php";

        $jsxDirectory = realpath(__DIR__ . '/../src/shortcodes') . DIRECTORY_SEPARATOR . strtolower($directoryPath);
        $jsxFilePath = $jsxDirectory . DIRECTORY_SEPARATOR . "{$fileName}.jsx";

        $mainJsxPath = realpath(__DIR__ . '/../src') . DIRECTORY_SEPARATOR . 'main.jsx';
        $shortcodeFilePath = realpath(__DIR__ . '/../includes/shortcodes') . DIRECTORY_SEPARATOR . 'class-shortcode.php';

        // Remove directory helper
        $removeDirIfEmpty = function ($dir, $stopAt) use ($output) {
            while (is_dir($dir) && count(scandir($dir)) === 2 && $dir !== $stopAt) {
                rmdir($dir);
                $output->writeln("Deleted directory: {$dir}");
                $dir = dirname($dir);
            }
        };

        // Delete the PHP shortcode file
        if (file_exists($phpFilePath)) {
            unlink($phpFilePath);
            $output->writeln("Deleted PHP file: {$phpFilePath}");
        } else {
            $output->writeln("PHP file not found: {$phpFilePath}");
        }

        // Delete the JSX component file
        if (file_exists($jsxFilePath)) {
            unlink($jsxFilePath);
            $output->writeln("Deleted JSX file: {$jsxFilePath}");
        } else {
            $output->writeln("JSX file not found: {$jsxFilePath}");
        }

        // Remove empty folders
        $removeDirIfEmpty($baseDirectory . strtolower($directoryPath), $baseDirectory);
        $removeDirIfEmpty($jsxDirectory, realpath(__DIR__ . '/../src/shortcodes'));

        // ✅ Update class-shortcode.php (remove LBMCore use/init)
        if (file_exists($shortcodeFilePath)) {
            $shortcodeContent = file_get_contents($shortcodeFilePath);

            $useStatement = "use LBMCore\\{$className};";
            $shortcodeContent = str_replace("\n{$useStatement}", '', $shortcodeContent);

            $initStatement = "{$className}::init();";
            $shortcodeContent = str_replace("\t\t{$initStatement}\n", '', $shortcodeContent);

            file_put_contents($shortcodeFilePath, $shortcodeContent);
            $output->writeln("Updated class-shortcode.php: {$shortcodeFilePath}");
        } else {
            $output->writeln("class-shortcode.php not found: {$shortcodeFilePath}");
        }

        // ✅ Update main.jsx
        if (file_exists($mainJsxPath)) {
            $mainJsxContent = file_get_contents($mainJsxPath);

            $importStatement = "import {$className} from './shortcodes/{$directoryPath}/{$fileName}';";
            $mainJsxContent = str_replace("{$importStatement}\n", '', $mainJsxContent);

            $variableName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $fileName))));
            $initCode = <<<JSX

const {$variableName}Elements = document.querySelectorAll('.{$fileName}');
{$variableName}Elements.forEach(element => {
    const key = element.getAttribute('data-key');
    createRoot(element).render(
        <{$className} dataKey={key} />
    );
});
JSX;

            $mainJsxContent = str_replace($initCode, '', $mainJsxContent);

            file_put_contents($mainJsxPath, $mainJsxContent);
            $output->writeln("Updated main.jsx: {$mainJsxPath}");
        } else {
            $output->writeln("main.jsx not found: {$mainJsxPath}");
        }

        $output->writeln("✅ Shortcode '{$fileName}' and associated files deleted successfully.");

        return Command::SUCCESS;
    }
}
