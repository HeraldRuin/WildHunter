<?php

namespace BC\Installer\Controllers;

use Illuminate\Routing\Controller;
use BC\Installer\Events\LaravelInstallerFinished;
use BC\Installer\Helpers\EnvironmentManager;
use BC\Installer\Helpers\FinalInstallManager;
use BC\Installer\Helpers\InstalledFileManager;

class FinalController extends Controller
{
    /**
     * Update installed file and display finished view.
     *
     * @param  \BC\Installer\Helpers\InstalledFileManager  $fileManager
     * @param  \BC\Installer\Helpers\FinalInstallManager  $finalInstall
     * @param  \BC\Installer\Helpers\EnvironmentManager  $environment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(InstalledFileManager $fileManager, FinalInstallManager $finalInstall, EnvironmentManager $environment)
    {
        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();
        $finalEnvFile = $environment->getEnvContent();

        event(new LaravelInstallerFinished);

        return view('vendor.installer.finished', compact('finalMessages', 'finalStatusMessage', 'finalEnvFile'));
    }
}
