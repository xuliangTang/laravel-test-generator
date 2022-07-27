<?php

namespace Lain\LaravelTestGenerator;

use Faker\Factory;
use Illuminate\Support\Facades\Config;

class Generator
{
    private \Faker\Generator $faker;
    private Formatter $formatter;
    private string $jsonPath;
    private string $testPath;
    private string $testName;
    private $testFile;

    public function __construct($options)
    {
        if (!file_exists($options['jsonPath'])) {
            throw new \Exception('error: swagger json file not exist');
        }
        $this->faker = Factory::create('zh_CN');
        $this->jsonPath = $options['jsonPath'];
        $this->formatter = new Formatter($this->jsonPath);
        $this->testName = ucfirst($options['test']);
        $this->testPath = base_path() . '/tests/Feature/' . $this->testName . 'Test.php';
    }

    /**
     * Generate the route methods and write to the file
     *
     * @return int
     */
    public function generate(): int
    {
        $this->createFile();
        $pack = $this->formatter->packTests();
        $this->writeTests($pack);

        return count($pack);
    }

    /**
     * Create a new test file if not exist
     *
     * @return void
     */
    private function createFile()
    {
        if(!is_dir(dirname($this->testPath))) {
            mkdir(dirname($this->testPath), 0755, true);
        }

        if (!file_exists($this->testPath)) {
            $this->testFile = fopen($this->testPath, 'aw+');

            $tpClass = file_get_contents(__DIR__ . '/tpl/TestClass.tpl');

            $init = str_replace('{name}', $this->testName, $tpClass);
            fwrite($this->testFile, $init);
        }
    }

    /**
     * 追加写入测试方法
     *
     * @param array $pack
     * @return void
     */
    private function writeTests(array $pack)
    {
        $tpMethod = file_get_contents(__DIR__ . '/tpl/TestMethod.tpl');

        $lines = file($this->testPath, FILE_IGNORE_NEW_LINES);

        foreach ($pack as $item) {
            $declare = '';
            foreach ($item['routers'] as $router) {
                $declare .= str_repeat("\t", 2) . '$' . $router['name'] . ' = \App\Models\\'. ucfirst($router['desc']) .'::query()->value("'. $router['name'] .'");' . PHP_EOL;
                $item['url'] = str_replace('{'. $router['name'] .'}', '{$'. $router['name'] .'}', $item['url']);
            }

            $parameters = $this->generateData($item['parameters']);

            if ($item['response']) {
                $assert = '$response->assertStatus('. $item['response_code'] .')' . PHP_EOL;
                $assert .= str_repeat("\t", 3) . '->assertJsonStructure(' . $this->arrayToStr($item['response']) . ');';
            } else {
                $assert = '$response->assertStatus('. $item['response_code'] .');';
            }

            $code = str_replace('{summary}', $item['summary'], $tpMethod);
            $code = str_replace('{name}', ucfirst($item['description']), $code);
            $code = str_replace('{method}', $item['method'], $code);
            $code = str_replace('{get_token}', Config::get('test-generator.get_token'), $code);
            $code = str_replace("        {declare}\n", $declare, $code);
            $code = str_replace('{url}', ucfirst($item['url']), $code);
            $code = str_replace('{parameters}', $parameters, $code);
            $code = str_replace('{assert}', $assert, $code);

            $lines = $this->insertToArray($lines, count($lines) - 1, [$code]);
        }

        file_put_contents($this->testPath, implode(PHP_EOL, $lines));
    }

    /**
     * 在数组指定位置插入值
     *
     * @param $arr
     * @param $position
     * @param $element
     * @return array
     */
    private function insertToArray($arr, $position, $element): array
    {
        $first_array = array_splice($arr, 0, $position);
        return array_merge($first_array, $element, $arr);
    }

    /**
     * 生成参数
     *
     * @param array $parameters
     * @return string
     */
    private function generateData(array $parameters) : string
    {
        $value = '[';

        $count = 0;
        foreach ($parameters as $parameter) {
            if (!$parameter['required']) continue;

            $count ++;
            $value .= PHP_EOL . str_repeat("\t", 3);
            $value .= "'{$parameter['name']}' => " . $this->getValue($parameter) . ',';
        }

        if ($count > 0) $value .= PHP_EOL . str_repeat("\t", 2);
        $value .= ']';

        return $value;
    }

    /**
     * @param array $parameter
     * @param int $floor
     * @return false|mixed|string
     */
    private function getValue(array $parameter, int $floor = 0): mixed
    {
        $value = [];
        $factor = 1;
        if ($parameter['type'] === 'array' && (!isset($parameter['model']) || !$parameter['model'])) {
            $factor = 2;
            $switchField = $parameter['item_type'];
        } else {
            $switchField = $parameter['type'];
        }

        for ($i = 0; $i < $factor; $i ++) {
            if (isset($parameter['model']) && $parameter['model']) {
                $model = explode(':', $parameter['model']);
                if ($parameter['type'] !== 'array') {
                    $value[] = '\App\Models\\' . ucfirst($model[0]).'::query()->value("'. $model[1] .'")';
                } else {
                    $value[] = '\App\Models\\' . ucfirst($model[0]).'::query()->limit(2)->pluck("'. $model[1] .'")';
                }
                continue;
            }

            if (isset($parameter['example']) && $parameter['example']) {
                $value[] = $parameter['example'];
                continue;
            }

            if (isset($parameter['default']) && $parameter['default']) {
                $value[] = $parameter['default'];
                continue;
            }

            if (isset($parameter['enum']) && $parameter['enum']) {
                $value[] = current($parameter['enum']);
                continue;
            }

            switch ($switchField) {
                case 'string':
                    $str = '\'';
                    if (str_contains($parameter['name'], 'phone') || str_contains($parameter['name'], 'mobile')) {
                        $str .= $this->faker->phoneNumber;
                    } elseif (str_contains($parameter['name'], 'email')) {
                        $str .= $this->faker->email;
                    } elseif (str_contains($parameter['name'], 'address')) {
                        $str .= $this->faker->address;
                    } elseif (str_contains($parameter['name'], 'idcard')) {
                        $str .= $this->faker->randomNumber(18, true);
                    } elseif (str_contains($parameter['name'], 'city')) {
                        $str .= $this->faker->city;
                    } elseif (str_contains($parameter['name'], 'date') || str_contains($parameter['name'], 'time')) {
                        $str .= $this->faker->date('Y-m-d H:i:s');
                    } else {
                        $str .= $this->faker->name;
                    }
                    $value[] = $str . '\'';
                    break;
                case 'integer':
                case 'number':
                    if (isset($parameter['minimum']) || isset($parameter['maximum'])) {
                        $value[] = $parameter['minimum'] ?? $parameter['maximum'];
                    } else {
                        $value[] = rand(1, 100);
                    }
                    break;
                case 'boolean':
                    $value[] = rand(0, 1);
                    break;
                case 'object':
                    $v = [];
                    foreach ($parameter['sub'] as $sub) {
                        $v[$sub['name']] = $this->getValue($sub, $floor + 1);
                    }
                    $value[] = $v;
                    break;
            }
        }

        if ($floor > 0) {
            if ($parameter['type'] === 'array') {
                return $value;
            } else {
                return current($value);
            }
        } else {
            if ($parameter['type'] === 'array') {
                return $this->arrayToStr($value);
            } elseif ($parameter['type'] === 'object') {
                return $this->arrayToStr(current($value));
            } else {
                return current($value);
            }
        }
    }

    /**
     * @param array $array
     * @param int $floor
     * @return string
     */
    private function arrayToStr(array $array, int $floor = 0): string
    {
        $str = '[' . PHP_EOL . str_repeat("\t", 4 + $floor);

        $i = 1;
        foreach ($array as $key => $item) {
            if ($i++ % 4 === 0) {
                $str .= PHP_EOL . str_repeat("\t", 4 + $floor);
            }

            if (is_string($key)) $str .= "'{$key}' => ";
            if (is_array($item)) {
                $str .= $this->arrayToStr($item, $floor + 1) . ', ';
            } else {
                if (str_starts_with($item, '\'') || str_starts_with($item, '\"') || !is_string($item) || str_starts_with($item, '\\')) {
                    $str .= "{$item}, ";
                } else {
                    $str .= "'{$item}', ";
                }
            }
        }

        $str .= PHP_EOL . str_repeat("\t", 3 + $floor) . ']';

        return $str;
    }
}
