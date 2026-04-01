<?php

namespace App\Foundation;

use App\Contracts\Foundation\ConsoleContract;
use App\Models\CronLog;
use Illuminate\Support\Facades\App;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Throwable;

class Command extends \Illuminate\Console\Command
{
    public function handle()
    {
        $hasConsoleContract = $this instanceof ConsoleContract;

        $startedAt = now();

        $log = new CronLog([
            'command' => $this->getName(),
            'title' => $hasConsoleContract ? $this->title() : null,
            'started_at' => $startedAt,
        ]);

        $log->save();

        try {

            if (! method_exists($this, 'process')) {
                throw new \RuntimeException('The command must implement a process() method.');
            }

            $method = new ReflectionMethod($this, 'process');

            $parameters = collect($method->getParameters())->map(
                fn (ReflectionParameter $param) => App::make($param->getType()?->getName())
            )->all();

            $result = $method->invokeArgs($this, $parameters);

            $log->status = $result;

            return $result;

        } catch (Throwable $e) {

            $log->status = SymfonyCommand::FAILURE;

            $log->error_message = $e->getMessage();

        } finally {

            $finishedAt = now();

            $log->finished_at = $finishedAt;

            $log->duration_ms = $startedAt->diffInMilliseconds($finishedAt);

            $log->save();
        }

    }

    protected function askForRequired(string $question): string
    {
        do {
            $input = $this->ask($question);
            if (empty($input)) {
                $this->error('This field is required.');
            }
        } while (empty($input));

        return $input;
    }
}
