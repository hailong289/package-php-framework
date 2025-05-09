<?php

namespace Hola\Scripts\Commands;

class GenerateKey extends \Hola\Core\Command
{
    protected $command = 'generate:key';
    protected $command_description = 'Generate a project key';
    protected $arguments = [];
    protected $options = ['?file'];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $new_project_key = generateKey(32);
        $key = 'PROJECT_KEY';
        $envFile = __DIR__ROOT . '/.env';

        if ($this->updateOrAddEnvKey($key, $new_project_key)) {
            $this->output()->text("Key generate successfully. KEY:$key=$new_project_key" . PHP_EOL);
        } else {
            $this->output()->error('Error when process key ' . $key);
        }
    }

    /**
     * @param string $key 
     * @param string|null $value 
     * @return bool 
     */
    protected function updateOrAddEnvKey(string $key, ?string $value = null): bool
    {
        try {
            $envFile = $this->getOption('file') ?? '.env';
            $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';
            $lines = explode("\n", $envContent);
            $keyExists = false;
            $newValue = $value;

            // Kiểm tra và cập nhật key nếu tồn tại
            foreach ($lines as &$line) {
                if (strpos($line, "$key=") === 0) {
                    $line = "$key=$newValue";
                    $keyExists = true;
                    break;
                }
            }
            
            if (!$keyExists) {
                $lines[] = "$key=$newValue";
            }
            
            $newContent = implode("\n", array_filter($lines, fn($line) => trim($line) !== ''));
            file_put_contents($envFile, $newContent);
            $_ENV[$key] = $newValue;
            putenv("$key=$newValue");
            return true;
        } catch (\Exception $e) {
            return false; 
        }
    }
}