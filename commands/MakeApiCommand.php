<?php
namespace Pixable\LBM;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeApiCommand extends Command
{
    protected static $defaultName = 'wp-shaper:make-api';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a new REST API endpoint and update class-api.php.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the API (use directory structure if needed, e.g., UserManagement/user_login)')
            ->addArgument('method', InputArgument::OPTIONAL, 'The HTTP method for the API (e.g., GET, POST, PUT). Use "method:<method>" format.', 'POST');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $method = $input->getArgument('method');

        if (str_starts_with(strtolower($method), 'method:')) {
            $method = strtoupper(substr($method, 7));
        } else {
            $method = strtoupper($method);
        }

        $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        if (!in_array($method, $allowedMethods)) {
            $output->writeln("<error>Invalid HTTP method '{$method}'. Allowed methods are: " . implode(', ', $allowedMethods) . ".</error>");
            return Command::FAILURE;
        }

        $parts = explode('/', $name);
        $fileName = array_pop($parts);
        $directoryPath = implode(DIRECTORY_SEPARATOR, $parts);

        $fileName = strtolower($fileName);
        $className = implode('', array_map('ucfirst', explode('_', $fileName)));
        $restPath = str_replace('_', '-', $fileName);

        $baseDirectory = realpath(__DIR__ . '/../includes/api') . DIRECTORY_SEPARATOR;
        $fullDirectoryPath = $baseDirectory . strtolower($directoryPath);
        $phpFilePath = rtrim($fullDirectoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "{$fileName}.php";

        if (!is_dir($fullDirectoryPath)) {
            mkdir($fullDirectoryPath, 0755, true);
        }

        // ✅ Use LBMCore namespace in the generated API file
        $phpContent = <<<PHP
<?php
namespace LBMCore;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class {$className} {

    public static function init() {
        \$self = new self();
        add_action('rest_api_init', array(\$self, 'register_rest_routes'));
    }

    /**
     * Registers the REST API routes.
     */
    public function register_rest_routes() {
        register_rest_route('frohub/v1', '/{$restPath}', array(
            'methods'             => '{$method}',
            'callback'            => array(\$this, 'handle_request'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Handles the API request.
     *
     * @param \\WP_REST_Request \$request
     * @return \\WP_REST_Response
     */
    public function handle_request(\\WP_REST_Request \$request) {
        return new \\WP_REST_Response(array(
            'success' => true,
            'message' => '{$restPath} API endpoint reached',
        ), 200);
    }
}
PHP;

        file_put_contents($phpFilePath, $phpContent);

        // ✅ Update class-api.php to include and initialize LBMCore class
        $apiFilePath = realpath(__DIR__ . '/../includes/api') . DIRECTORY_SEPARATOR . 'class-api.php';

        if (file_exists($apiFilePath)) {
            $apiContent = file_get_contents($apiFilePath);

            $useStatement = "use LBMCore\\{$className};";
            if (strpos($apiContent, $useStatement) === false) {
                $apiContent = str_replace("namespace LBMCore;\n", "namespace LBMCore;\n\n{$useStatement}\n", $apiContent);
            }

            $initStatement = "{$className}::init();";
            if (strpos($apiContent, $initStatement) === false) {
                $apiContent = preg_replace(
                    '/public static function init\(\) \{\n(.*?)\n\t\}/s',
                    "public static function init() {\n$1\n\t\t{$initStatement}\n\t}",
                    $apiContent
                );
            }

            file_put_contents($apiFilePath, $apiContent);
        }

        $output->writeln("✅ REST API '{$fileName}' created with method '{$method}' and 'class-api.php' updated successfully:");
        $output->writeln("- PHP file created at: {$phpFilePath}");
        $output->writeln("- Updated: {$apiFilePath}");

        return Command::SUCCESS;
    }
}
