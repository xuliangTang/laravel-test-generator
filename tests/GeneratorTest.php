<?php

use Lain\LaravelTestGenerator\Generator;
use Orchestra\Testbench\TestCase;

class GeneratorTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        // Code before application created.

        parent::setUp();

        // Code after application created.
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Lain\LaravelTestGenerator\TestGeneratorProvider::class
        ];
    }

    public function testGenerate()
    {
        $options = [
            'jsonPath' => __DIR__ . '/test-swagger.json',
            'test' => 'test'
        ];

        $generator = new Generator($options);
        $n = $generator->generate();
        $this->assertIsInt($n);
        $this->assertGreaterThan(0, $n);
    }
}