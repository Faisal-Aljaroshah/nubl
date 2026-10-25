<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        using: function () {
            // Laravel 11 ignores `health:` when `using:` is provided, so register /up manually.
            Route::get('/up', function () {
                $exception = null;
                try {
                    Event::dispatch(new DiagnosingHealth);
                } catch (\Throwable $e) {
                    if (app()->hasDebugModeEnabled()) {
                        throw $e;
                    }
                    report($e);
                    $exception = $e->getMessage();
                }

                return response(
                    '<h1>Application up</h1>'.($exception ? "<!-- {$exception} -->" : ''),
                    $exception ? 500 : 200
                );
            });

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            if (! app()->environment('production')) {
                Route::middleware('api')
                    ->group(base_path('routes/testing.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\ApplyMockedTime::class);

        // Plain XSRF-TOKEN in the browser; required for JS CSRF + axios/fetch headers.
        $middleware->encryptCookies(except: [
            'XSRF-TOKEN',
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\DisableHttpCacheForAuthForms::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\EnsurePermission::class,
            'redirect.by.role' => \App\Http\Middleware\RedirectByRole::class,
            'email.verified' => \App\Http\Middleware\EnsureEmailVerified::class,
            'phone.verified' => \App\Http\Middleware\EnsurePhoneVerified::class,
            'account.approved' => \App\Http\Middleware\EnsureAccountApproved::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            $message = __('Your session has expired. Please try again.');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'code' => 'session_expired',
                ], 419);
            }

            return redirect()
                ->back()
                ->withInput($request->except(['password', 'password_confirmation', '_token']))
                ->with('error', $message);
        });
    })->create();
