<?php

namespace Codedeploy\Uptime\Monitors;

use Closure;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Mail\TransportManager;
use Illuminate\Support\Arr;
use Throwable;
use Codedeploy\Uptime\Support;

class Mail extends Monitor
{
    use InteractsWithMailgun;
    use InteractsWithMandrill;
    use InteractsWithPostmark;
    use InteractsWithSes;
    use InteractsWithSmtp;
    use InteractsWithSparkPost;

    const ERROR_CONFIG_INVALID       = 'config_invalid';
    const ERROR_MAILGUN_CONNECTION   = 'mailgun_connection';
    const ERROR_MAILGUN_DISABLED     = 'mailgun_disabled';
    const ERROR_MAILGUN_DOMAIN       = 'mailgun_domain';
    const ERROR_MAILGUN_STATE        = 'mailgun_state';
    const ERROR_MANDRILL_CONNECTION  = 'mandrill_connection';
    const ERROR_MANDRILL_INVALID     = 'mandrill_invalid';
    const ERROR_MANDRILL_KEY         = 'mandrill_key';
    const ERROR_POSTMARK_CONNECTION  = 'postmark_connection';
    const ERROR_POSTMARK_INVALID     = 'postmark_invalid';
    const ERROR_POSTMARK_KEY         = 'postmark_key';
    const ERROR_SES_CONNECTION       = 'ses_connection';
    const ERROR_SES_INVALID          = 'ses_invalid';
    const ERROR_SES_KEY              = 'ses_key';
    const ERROR_SMTP_CONNECTION      = 'smtp_connection';
    const ERROR_SPARKPOST_CONNECTION = 'sparkpost_connection';
    const ERROR_SPARKPOST_INVALID    = 'sparkpost_invalid';
    const ERROR_SPARKPOST_KEY        = 'sparkpost_key';

    const WARNING_DRIVER_UNSUPPORTED = 'driver_unsupported';

    /**
     * Mail driver name.
     *
     * @var string
     */
    private $driverName;

    /**
     * Closure to resolve a Guzzle Http client instance.
     *
     * @var Close
     */
    private $guzzleResolver;

    /**
     * @param string       $driverName
     * @param \Closure|null $guzzleResolver
     */
    public function __construct(string $driverName, Closure $guzzleResolver = null)
    {
        $this->driverName     = $driverName;
        $this->guzzleResolver = $guzzleResolver;
    }

    /**
     * Resolves and returns the Transport Manager instance.
     *
     * @return mixed
     */
    public static function transportManager()
    {
        return Support::supportsMultipleMailers()
            ? app('mail.manager')
            : app('swift.transport');
    }

    /**
     * Get a fresh Guzzle HTTP client instance.
     *
     * @param  array  $config
     * @return \GuzzleHttp\Client
     */
    private function guzzle($config)
    {
        $config = Arr::add(
            $config['guzzle'] ?? [],
            'connect_timeout',
            5
        );

        // Check if there is a custom resolver to build the instance
        if ($this->guzzleResolver) {
            return call_user_func($this->guzzleResolver, $config);
        }

        return new HttpClient($config);
    }

    /**
     * Checks the mail driver.
     *
     * @return boolean|null
     */
    public function run()
    {
        $checker = 'run' . ucfirst($this->driverName) . 'Check';

        // Verify we have a checker for this driver
        if (!method_exists($this, $checker)) {
            $this->warning(static::WARNING_DRIVER_UNSUPPORTED, [
                'message' => 'This driver is unsupported',
            ]);
        }

        // Modern Laravel 7.0 + non-updated config
        if (Support::supportsMultipleMailers() && !config("mail.mailers")) {
            try {
                $transport = $this->transport();
            } catch (Throwable $exception) {
                return;
            }
        }

        try {
            // Try to resolve the swift mailer transport from the manager
            $transport = $transport ?? $this->transport();
        } catch (Throwable $exception) {
            return $this->error(static::ERROR_CONFIG_INVALID, [
                'message' => $exception->getMessage(),
            ]);
        }

        if (!method_exists($this, $checker)) {
            return;
        }

        $this->$checker($transport);
    }

    /**
     * Resolves the Swift Transport from the manager.
     *
     * @return mixed
     */
    private function transport()
    {
        $transport = static::transportManager()->driver($this->driverName);

        return Support::supportsMultipleMailers()
            ? $transport->getSwiftMailer()->getTransport()
            : $transport;
    }
}
