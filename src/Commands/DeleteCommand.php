<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;

class DeleteCommand extends FileManipulationCommand
{
    protected $signature = 'livewire:delete {name} {--force}';

    protected $description = 'Delete a Livewire component.';

    public function handle()
    {
        $this->parser = new ComponentParser(
            config('livewire.class_namespace', 'App\\Http\\Livewire'),
            config('livewire.view_path', resource_path('views/livewire')),
            $this->argument('name')
        );

        if (! $force = $this->option('force')) {
            $shouldContinue = $this->confirm(
                "<fg=yellow>Are you sure you want to delete the following files?</>\n\n{$this->parser->relativeClassPath()}\n{$this->parser->relativeViewPath()}\n"
            );

            if (! $shouldContinue) {
                return;
            }
        }

        $class = $this->removeClass($force);
        $view = $this->removeView($force);

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->line("<options=bold,reverse;fg=yellow> COMPONENT DESTROYED </> 🦖💫\n");
        $class && $this->line("<options=bold;fg=yellow>CLASS:</> {$this->parser->relativeClassPath()}");
        $view && $this->line("<options=bold;fg=yellow>VIEW:</>  {$this->parser->relativeViewPath()}");
    }

    protected function removeClass($force = false)
    {
        $classPath = $this->parser->classPath();

        if (! File::exists($classPath) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class doesn't exist:</> {$this->parser->relativeClassPath()}");

            return false;
        }

        File::delete($classPath);

        return $classPath;
    }

    protected function removeView($force = false)
    {
        $viewPath = $this->parser->viewPath();

        if (! File::exists($viewPath) && ! $force) {
            $this->line("<fg=red;options=bold>View doesn't exist:</> {$this->parser->relativeViewPath()}");

            return false;
        }

        File::delete($viewPath);

        return $viewPath;
    }
}
