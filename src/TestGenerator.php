<?php

namespace Lain\LaravelTestGenerator;

use Illuminate\Console\Command;

class TestGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:generate {test}
                                {--json=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generates api test cases for this application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $options = [
            'jsonPath' => base_path() . '/' . ($this->option('json') ?? 'test-swagger.json'),
            'test' => $this->argument('test')
        ];

        try {
            $generator = new Generator($options);
            $n = $generator->generate();
            $this->info("Successfully generated {$n} api tests");
        } catch (\Throwable $t) {
            $this->error($t->getMessage() . '(' . $t->getLine() . ')');
        }
    }
}