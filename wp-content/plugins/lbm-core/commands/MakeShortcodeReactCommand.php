<?php
namespace Pixable\LBM;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeShortcodeReactCommand extends Command
{
    protected static $defaultName = 'make:shortcode-react';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a new React-based shortcode and update related files.')
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
        $variableName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $fileName))));

        $baseDirectory = realpath(__DIR__ . '/../includes/shortcodes') . DIRECTORY_SEPARATOR;
        $fullDirectoryPath = $baseDirectory . strtolower($directoryPath);
        $phpFilePath = rtrim($fullDirectoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "{$fileName}.php";

        if (!is_dir($fullDirectoryPath)) {
            mkdir($fullDirectoryPath, 0755, true);
        }

        // ✅ Generated shortcode class uses LBMCore namespace
        $phpContent = <<<PHP
<?php
namespace LBMCore;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class {$className} {

    public static function init() {
        \$self = new self();
        add_shortcode( '{$fileName}', array(\$self, '{$fileName}_shortcode') );
    }

    public function {$fileName}_shortcode() {
        \$unique_key = '{$fileName}' . uniqid();
        return '<div class="{$fileName}" data-key="' . esc_attr(\$unique_key) . '"></div>';
    }
}
PHP;

        file_put_contents($phpFilePath, $phpContent);

        // ✅ Update use statement in class-shortcode.php
        $shortcodeFilePath = realpath(__DIR__ . '/../includes/shortcodes') . DIRECTORY_SEPARATOR . 'class-shortcode.php';

        if (file_exists($shortcodeFilePath)) {
            $shortcodeContent = file_get_contents($shortcodeFilePath);

            $useStatement = "use LBMCore\\{$className};";
            if (strpos($shortcodeContent, $useStatement) === false) {
                $shortcodeContent = str_replace("namespace LBMCore;\n", "namespace LBMCore;\n\n{$useStatement}\n", $shortcodeContent);
            }

            $initStatement = "{$className}::init();";
            if (strpos($shortcodeContent, $initStatement) === false) {
                $shortcodeContent = preg_replace(
                    '/public static function init\(\) \{\n(.*?)\n\t\}/s',
                    "public static function init() {\n$1\n\t\t{$initStatement}\n\t}",
                    $shortcodeContent
                );
            }

            file_put_contents($shortcodeFilePath, $shortcodeContent);
        }

        // ✅ Create JSX component
        $jsxDirectory = realpath(__DIR__ . '/../src/shortcodes') . DIRECTORY_SEPARATOR . strtolower($directoryPath);
        $jsxFilePath = $jsxDirectory . DIRECTORY_SEPARATOR . "{$fileName}.jsx";

        if (!is_dir($jsxDirectory)) {
            mkdir($jsxDirectory, 0755, true);
        }

        $jsxComponentContent = <<<JSX
import React from 'react';

const {$className} = ({ dataKey }) => {
    return (
        <div>
            <h1>Welcome to {$className} from React</h1>
        </div>
    );
};

export default {$className};
JSX;

        file_put_contents($jsxFilePath, $jsxComponentContent);

        // ✅ Update main.jsx
        $mainJsxPath = realpath(__DIR__ . '/../src') . DIRECTORY_SEPARATOR . 'main.jsx';

        if (file_exists($mainJsxPath)) {
            $mainJsxContent = file_get_contents($mainJsxPath);

            $importStatement = "import {$className} from './shortcodes/{$directoryPath}/{$fileName}';";
            if (strpos($mainJsxContent, $importStatement) === false) {
                $mainJsxContent = $importStatement . "\n" . $mainJsxContent;
            }

            $initCode = <<<JSX

const {$variableName}Elements = document.querySelectorAll('.{$fileName}');
{$variableName}Elements.forEach(element => {
    const key = element.getAttribute('data-key');
    createRoot(element).render(
        <{$className} dataKey={key} />
    );
});
JSX;

            if (strpos($mainJsxContent, $initCode) === false) {
                $mainJsxContent .= "\n" . $initCode;
            }

            file_put_contents($mainJsxPath, $mainJsxContent);
        } else {
            $output->writeln("<error>main.jsx file not found at {$mainJsxPath}</error>");
            return Command::FAILURE;
        }

        $output->writeln("✅ Shortcode '{$fileName}', React component, and main.jsx updated successfully.");
        $output->writeln("- PHP file created at: {$phpFilePath}");
        $output->writeln("- JSX file created at: {$jsxFilePath}");
        $output->writeln("- Updated: {$mainJsxPath}");

        return Command::SUCCESS;
    }
}
