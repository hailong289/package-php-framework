<?php
if (strpos($name_request, 'Request') === false) $name_request = $name_request . 'Request';
$concurrentDirectory = __DIR__ROOT . "/Request/$name_request.php";
if (!file_exists($concurrentDirectory)) {
    if (!is_dir(__DIR__ROOT . "/Request")) {
        mkdir(__DIR__ROOT . "/Request");
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
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
    echo "Request $name_request create successfully";
} else {
    echo "Request $name_request already exist";
}