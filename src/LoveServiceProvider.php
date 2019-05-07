<?php

/*
 * This file is part of Laravel Love.
 *
 * (c) Anton Komarev <a.komarev@cybercog.su>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Cog\Laravel\Love;

use Cog\Laravel\Love\Console\Commands\ReactionTypeAdd;
use Cog\Laravel\Love\Console\Commands\Recount;
use Cog\Laravel\Love\Console\Commands\UpgradeV5ToV6;
use Cog\Laravel\Love\Reactant\Listeners\DecrementAggregates;
use Cog\Laravel\Love\Reactant\Listeners\IncrementAggregates;
use Cog\Laravel\Love\Reaction\Events\ReactionHasBeenAdded;
use Cog\Laravel\Love\Reaction\Events\ReactionHasBeenRemoved;
use Cog\Laravel\Love\Reaction\Models\Reaction;
use Cog\Laravel\Love\Reaction\Observers\ReactionObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class LoveServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerConsoleCommands();
        $this->registerObservers();
        $this->registerPublishes();
        $this->registerMigrations();
        $this->registerListeners();
    }

    /**
     * Register Love's models observers.
     *
     * @return void
     */
    private function registerObservers(): void
    {
        Reaction::observe(ReactionObserver::class);
    }

    /**
     * Register Love's console commands.
     *
     * @return void
     */
    private function registerConsoleCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReactionTypeAdd::class,
                Recount::class,
                UpgradeV5ToV6::class,
            ]);
        }
    }

    /**
     * Setup the resource publishing groups for Love.
     *
     * @return void
     */
    private function registerPublishes(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'love-migrations');
        }
    }

    /**
     * Register the Love migrations.
     *
     * @return void
     */
    private function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Register the Love event listeners.
     *
     * @return void
     */
    private function registerListeners(): void
    {
        Event::listen(ReactionHasBeenAdded::class, IncrementAggregates::class);
        Event::listen(ReactionHasBeenRemoved::class, DecrementAggregates::class);
    }
}