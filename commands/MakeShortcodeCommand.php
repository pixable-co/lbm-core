<?php
namespace Pixable\LBM;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeShortcodeCommand extends Command
{
    protected static $defaultName = 'wp-shaper:make-shortcode';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a new shortcode and update class-shortcode.php.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the shortcode (use directory structure if needed, e.g., BookingForm/fh_submit_form)');
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
        $fullDirectoryPath = $baseDirectory . strtolower($directoryPath);
        $phpFilePath = rtrim($fullDirectoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "{$fileName}.php";

        if (!is_dir($fullDirectoryPath)) {
            mkdir($fullDirectoryPath, 0755, true);
        }

        // ✅ Generated shortcode class will use the LBMCore namespace
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

        // Update class-shortcode.php
        $shortcodeFilePath = realpath(__DIR__ . '/../includes/shortcodes') . DIRECTORY_SEPARATOR . 'class-shortcode.php';

        if (file_exists($shortcodeFilePath)) {
            $shortcodeContent = file_get_contents($shortcodeFilePath);

            // ✅ Update this line to use the LBMCore namespace in use statement
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

        $output->writeln("✅ Shortcode '{$fileName}' created and 'class-shortcode.php' updated successfully.");
        $output->writeln("- PHP file created at: {$phpFilePath}");
        $output->writeln("- Updated: {$shortcodeFilePath}");

        return Command::SUCCESS;
    }
}
