<?php

namespace Lain\LaravelTestGenerator;

class Formatter
{
    private string $jsonPath;

    public function __construct(string $jsonPath)
    {
        $this->jsonPath = $jsonPath;
    }

    /**
     * format and pack test unit.
     *
     * @return array
     */
    public function packTests() : array
    {
        $pack = [];
        $jsonArr = json_decode(file_get_contents($this->jsonPath), true);

        foreach ($jsonArr['paths'] as $url => $paths) {
            foreach ($paths as $method => $path) {
                $item = [
                    'url' => $url,
                    'method' => $method,
                    'summary' => $path['summary'],
                    'description' => $path['description'] ?: $this->getTestName($method, $url),
                    'routers' => [],
                    'parameters' => [],
                ];

                foreach ($path['parameters'] as $parameter) {
                    switch ($parameter['in']) {
                        case 'header':
                            continue 2;
                        case 'path':
                            $item['routers'][] = [
                                'name' => $parameter['name'],
                                'desc' => $parameter['description']
                            ];
                            break;
                        default:
                            $item['parameters'][] = [
                                'name' => $parameter['name'],
                                'required' => $parameter['required'] ?? false,
                                'example' => $parameter['example'] ?? '',
                                'type' => $parameter['schema']['type']
                            ];
                    }
                }

                $requestBody = $path['requestBody']['content']['application/json']['schema']['properties'] ?? [];
                foreach ($requestBody as $name => $parameter) {
                    $item['parameters'][] = $this->packRequestBody($name, $parameter);
                }

                $item['response_code'] = key($path['responses']);
                $response = current($path['responses']);
                $properties = $response['content']['application/json']['schema']['properties'] ?? [];
                $assertJson = [];
                foreach ($properties as $name => $property) {
                    $this->packResponse($name, $property, $assertJson);
                }
                $item['response'] = $assertJson;

                $pack[] = $item;
            }
        }

        return $pack;
    }

    /**
     * @param $name
     * @param $parameter
     * @return array
     */
    private function packRequestBody($name, $parameter): array
    {
        $bodyFields = ['model', 'enum', 'default', 'minimum', 'maximum'];

        $p = [
            'name' => $name,
            'required' => true,
            'example' => '',
            'type' => $parameter['type']
        ];

        foreach ($bodyFields as $field) {
            if (isset($parameter[$field]) && $parameter[$field]) {
                $p[$field] = $parameter[$field];
            }
        }

        if ($p['type'] === 'array') $p['item_type'] = $parameter['items']['type'];

        if (isset($p['item_type']) && $p['item_type'] === 'object') {
            foreach ($parameter['items']['properties'] as $key => $property) {
                $p['sub'][$key] = $this->packRequestBody($key, $property);
            }
        }

        if ($p['type'] === 'object') {
            foreach ($parameter['properties'] as $key => $property) {
                $p['sub'][$key] = $this->packRequestBody($key, $property);
            }
        }

        return $p;
    }

    /**
     * pack assert json
     *
     * @param string $name
     * @param array $property
     * @param $assertJson
     * @return void
     */
    private function packResponse(string $name, array $property, &$assertJson)
    {
        switch ($property['type']) {
            case 'array':
                $assertJson[$name] = $property['items']['required'];
                break;
            case 'object':
                if (isset($property['properties']['items'])) {
                    $assertJson[$name] = [
                        'items' => [
                            '*' => $property['properties']['items']['items']['required'] ?? []
                        ]
                    ];
                } else {
                    $assertJson[$name] = $property['required'];
                }
                break;
            default:
                $assertJson[] = $name;
        }
    }

    /**
     * generate test name.
     *
     * @param string $method
     * @param string $url
     * @return string
     */
    private function getTestName(string $method, string $url): string
    {
        $name = $method;
        $url = explode('/', $url);
        foreach ($url as $str) {
            if (!$str || preg_match('/^{\w+}$/', $str)) continue;
            $name .= ucfirst($str);
        }

        return $name;
    }
}
