<?php
require __DIR__ . '/../vendor/autoload.php';

use src\MyProject\Cli\AbstractCommand;

try {
    unset($argv[0]);

//    spl_autoload_register(function (string $className) {
//        require_once __DIR__ . '/../src/' . $className . '.php';
//    });

    $className = '\\MyProject\\Cli\\' . array_shift($argv);
    if (!class_exists($className)) {
        throw new \src\MyProject\Exceptions\CliException('Class "' . $className . '" not found');
    }

    $classReflector = new ReflectionClass($className);
    if (!$classReflector->isSubclassOf(AbstractCommand::class)) {
        throw new \src\MyProject\Exceptions\CliException('Class "' . $className . '" not a subclass of AbstractCommand');
    }


    $params = [];

    foreach ($argv as $argument) {
        preg_match('/^-(.+)=(.+)$/', $argument, $matches);
        if (!empty($matches)) {
            $paramName = $matches[1];
            $paramValue = $matches[2];

            $params[$paramName] = $paramValue;
        }
    }



    $class = new $className($params);
    $class->execute();
}catch (\src\MyProject\Exceptions\CliException $e){
echo 'Error:'. $e->getMessage();
}
public function execute()
{
    // чтобы проверить работу скрипта, будем записывать в файлик 1.log текущую дату и время
    file_put_contents('C:\\1.log', date(DATE_ISO8601) . PHP_EOL, FILE_APPEND);
}