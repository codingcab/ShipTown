<?php

namespace Tests;

use App\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use Throwable;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;
    use ResetsDatabase;

    protected int $shortDelay = 300;
    protected int $longDelay = 0;

    public static function prepare(): void
    {
        static::startChromeDriver();
    }

    protected function driver(): RemoteWebDriver
    {
        $arguments = collect([
            '--disable-gpu',
            env('DUSK_HEADLESS', true) ? '--headless' : null,
            '--window-size=300,600',
        ])->filter()->toArray();

        $options = (new ChromeOptions())->addArguments($arguments);

        return RemoteWebDriver::create(
            'http://127.0.0.1:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * @throws Throwable
     */
    public function basicUserAccessTest(string $uri, bool $allowed)
    {
        $this->browse(function (Browser $browser) use ($uri, $allowed) {
            /** @var User $user */
            $user = User::factory()->create();
            $user->assignRole('user');

            $browser->disableFitOnFailure();

            $browser->loginAs($user);
            $browser->visit($uri);
            $browser->pause($this->shortDelay);

            $browser->assertSourceMissing('Server Error');
            $browser->assertSourceMissing('snotify-error');

            if ($allowed) {
                $browser->assertPathIs($uri);
            } else {
                $browser->assertSee('403');
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function basicAdminAccessTest(string $uri, bool $allowed)
    {
        $this->browse(function (Browser $browser) use ($uri, $allowed) {
            /** @var User $admin */
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            $browser->disableFitOnFailure();

            $browser->loginAs($admin);
            $browser->visit($uri);
            $browser->pause($this->shortDelay);

            $browser->assertSourceMissing('Server Error');
            $browser->assertSourceMissing('snotify-error');

            if ($allowed) {
                $browser->assertPathIs($uri);
            } else {
                $browser->assertPathIs('login');
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function basicGuestAccessTest(string $uri, bool $allowed = false)
    {
        User::factory()->create();

        $this->browse(function (Browser $browser) use ($uri, $allowed) {
            $browser->disableFitOnFailure();

            $browser->logout();
            $browser->visit($uri);
            $browser->pause($this->shortDelay);

            $browser->assertSourceMissing('Server Error');
            $browser->assertSourceMissing('snotify-error');

            if ($allowed) {
                $browser->assertPathIs($uri);
            } else {
                $browser->assertPathIs('/login');
                $browser->assertSee('Login');
            }
        });
    }

    protected static function setEnvironmentValue($key, $value): void
    {
        $path = app()->environmentFilePath();

        if (! file_exists($path)) {
            return;
        }

        $str = file_get_contents($path);

        $str .= "\n"; // In case the searched variable is in the last line without \n
        $keyPosition = strpos($str, "{$key}=");
        $endOfLinePosition = strpos($str, "\n", $keyPosition);
        $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

        // If key does not exist, add it
        if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
            $str .= "{$key}={$value}\n";
        } else {
            $str = str_replace($oldLine, "{$key}={$value}", $str);
        }

        file_put_contents($path, $str);
    }

    protected function sendKeysTo(Browser $browser, string $keys): void
    {
        $browser->driver->getKeyboard()->sendKeys($keys);
    }
}
