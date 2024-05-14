<?php

namespace App\Providers;

use TusPhp\Tus\Server as TusServer;
use Illuminate\Support\ServiceProvider;
use App\Models\Journey;

class TusServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton("tus-server", function ($app) {
            \TusPhp\Config::set(base_path("config/tus.php"));
            $server = new TusServer("redis");

            $server
                ->setApiPath("/tus") // tus server endpoint.
                ->setUploadDir(storage_path("app/public/uploads")); // uploads dir.

            $server
                ->event()
                ->addListener("tus-server.upload.complete", function (
                    \TusPhp\Events\TusEvent $event
                ) {
                    $fileMeta = $event->getFile()->details();

                    // Delete file from uploads dir if the metadata isn't set.
                    if (
                        !isset($fileMeta["metadata"]) ||
                        !isset($fileMeta["metadata"]["journey"])
                    ) {
                        // Delete file from uploads dir if the user doesn't have permission to upload to the journey.
                        unlink($fileMeta["file_path"]);
                        return;
                    }

                    // Check if the journey exists.
                    $journey = $fileMeta["metadata"]["journey"];
                    $journey = Journey::findOrFail($journey);

                    // Check if the user has permission to upload to the journey.
                    if (
                        !auth()
                            ->user()
                            ->can(
                                "journeyMember",
                                Journey::find($fileMeta["metadata"]["journey"])
                            )
                    ) {
                        unlink($fileMeta["file_path"]);
                        return;
                    }

                    // Create journey folder if not exists.
                    $journeyFolder = storage_path(
                        "app/public/journeys/" .
                            $fileMeta["metadata"]["journey"]
                    );
                    if (!file_exists($journeyFolder)) {
                        mkdir($journeyFolder, 0777, true);
                    }

                    // Move file to journey folder.
                    rename(
                        $fileMeta["file_path"],
                        $journeyFolder .
                            "/" .
                            hrtime(true) .
                            "_" .
                            $fileMeta["name"]
                    );
                });

            return $server;
        });
    }
}
