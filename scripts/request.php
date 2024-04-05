<?php
namespace Scripts;
class RequestScript extends \System\Core\Command
{
    protected $command = 'create:request';
    protected $command_description = 'Create a new form request';
    protected $arguments = [
        'name_request'
    ];
    protected $options = [];

    public function handle()
    {
        $name_request = $this->getArgument('name_request');
        if (strpos($name_request, 'Request') === false) $name_request = $name_request . 'Request';
        $concurrentDirectory = __DIR__ROOT . "/request/$name_request.php";
        if (!file_exists($concurrentDirectory)) {
            if (!is_dir(__DIR__ROOT . "/request")) {
                mkdir(__DIR__ROOT . "/request");
            }
            file_put_contents($concurrentDirectory, '<?php
namespace Request;
use System\Core\FormRequest;

class '.$name_request.' extends FormRequest
{
    public function __construct() {
        parent::__construct();
    }

    public function auth() {
        return true;
    }

    public function rules()
    {
        return [];
    }
}', FILE_APPEND);
            if (!file_exists($concurrentDirectory)) {
                $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            $this->output()->text("Request $name_request create successfully");
        } else {
            $this->output()->text("Request $name_request already exist");
        }
    }
}
