<?php

namespace Nylas\Utilities;

use function fopen;
use function is_string;
use function is_resource;

use Nylas\Request\Sync;
use Nylas\Request\Async;
use Nylas\Utilities\Validator as V;

/**
 * ----------------------------------------------------------------------------------
 * Nylas Utils Options
 * ----------------------------------------------------------------------------------
 *
 * @author lanlin
 * @change 2021/03/18
 */
class Options
{
    // ------------------------------------------------------------------------------

    /**
     * @var mixed
     */
    private $logFile;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var string
     */
    private $server;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $grantId;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $accountId;

    /**
     * @var array
     */
    private $accountInfo;

    // ------------------------------------------------------------------------------

    /**
     * Options constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $rules = V::keySet(
            V::key('debug', V::boolType(), false),
            V::key('region', V::in(['oregon', 'canada', 'ireland']), false),
            V::key('log_file', $this->getLogFileRule(), false),
            V::key('account_id', V::stringType()->notEmpty(), false),
            V::key('access_token', V::stringType()->notEmpty(), false),
            V::key('client_id', V::stringType()->notEmpty()),
            V::key('api_key', V::stringType()->notEmpty())
        );

        V::doValidate($rules, $options);

        // required
        $this->region = $options['region'] ?? 'oregon';
        $this->setClientApps($options['api_key']);
        $this->setClientId($options['client_id']);
        // optional
        $this->setDebug($options['debug'] ?? false);
        $this->setServer($options['region'] ?? 'oregon');
        $this->setLogFile($options['log_file'] ?? null);
        $this->setAccountId($options['account_id'] ?? '');
        $this->setGrantId($options['grant_id'] ?? '');
        $this->setAccessToken($options['access_token'] ?? '');
    }

    // ------------------------------------------------------------------------------

    /**
     * set access token
     *
     * @param string $token
     */
    public function setGrantId(string $grantId): void
    {
        $this->grantId = $grantId;
    }

    /**
     * get grantId
     *
     */
    public function getGrantId(): string
    {
        return $this->grantId;
    }

    /**
     * set access token
     *
     * @param string $token
     */
    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;

        if (!$token)
        {
            return;
        }

        $this->accountInfo = [];
    }

    // ------------------------------------------------------------------------------

    /**
     * get access token
     *
     * @return string
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken ?? null;
    }

    // ------------------------------------------------------------------------------

    /**
     * set account id
     *
     * @param string $id
     */
    public function setAccountId(string $id): void
    {
        $this->accountId = $id;
    }

    // ------------------------------------------------------------------------------

    /**
     * get account id
     *
     * @return string
     */
    public function getAccountId(): ?string
    {
        return $this->accountId ?? null;
    }

    // ------------------------------------------------------------------------------

    /**
     * @param null|string $region
     */
    public function setServer(string $region = null): void
    {
        $region = $region ?? 'oregon';

        $this->server = API::SERVER[$region] ?? API::SERVER['oregon'];
    }

    // ------------------------------------------------------------------------------

    /**
     * get server
     *
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }

    // ------------------------------------------------------------------------------

    /**
     * enable/disable debug
     *
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    // ------------------------------------------------------------------------------

    /**
     * set log file
     *
     * @param mixed $logFile
     */
    public function setLogFile($logFile): void
    {
        if (null !== $logFile)
        {
            V::doValidate($this->getLogFileRule(), $logFile);
        }

        $this->logFile = $logFile;
    }

    // ------------------------------------------------------------------------------

    /**
     * set clientId
     *
     * @param mixed $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    // ------------------------------------------------------------------------------

    /**
     * set client id & secret
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function setClientApps(string $clientId,  string $apiKey): void
    {
        $this->clientId   = $clientId;
        $this->apiKey     = $apiKey;
    }

    // ------------------------------------------------------------------------------

    /**
     * get client id & secret
     *
     * @return array
     */
    public function getClientApps(): array
    {
        return
        [
            'client_id'   => $this->clientId,
            'api_key'     => $this->apiKey
        ];
    }

    // ------------------------------------------------------------------------------

    /**
     * get all configure options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        return
        [
            'debug'            => $this->debug,
            'log_file'         => $this->logFile,
            'server'           => $this->server,
            'api_key'          => $this->apiKey,
            'client_id'        => $this->clientId,
            'account_id'       => $this->accountId,
            'grant_id'         => $this->grantId,
            'access_token'     => $this->accessToken,
        ];
    }

    // ------------------------------------------------------------------------------

    /**
     * get sync request instance
     *
     * @return \Nylas\Request\Sync
     */
    public function getSync(): Sync
    {
        $debug = $this->getLoggerHandler();
        $server = $this->getServer();
        $handler = $this->getHandler();

        return new Sync($server, $handler, $debug);
    }

    // ------------------------------------------------------------------------------

    /**
     * get async request instance
     *
     * @return \Nylas\Request\Async
     */
    public function getAsync(): Async
    {
        $debug = $this->getLoggerHandler();
        $server = $this->getServer();
        $handler = $this->getHandler();

        return new Async($server, $handler, $debug);
    }

    // ------------------------------------------------------------------------------

    /**
     * get account infos
     *
     * @return array
     */
    public function getAccount(): array
    {
        $temp =
        [
            'id'                => '',
            'account_id'        => '',
            'email_address'     => '',
            'name'              => '',
            'object'            => '',
            'provider'          => '',
            'linked_at'         => null,
            'sync_state'        => '',
            'organization_unit' => '',
        ];

        if (empty($this->accountInfo) && !empty($this->accessToken))
        {
            $this->accountInfo = (new Account($this))->getAccount();
        }

        return \array_merge($temp, $this->accountInfo);
    }

    // ------------------------------------------------------------------------------

    /**
     * get log file rules
     *
     * @return Validator
     */
    private function getLogFileRule(): V
    {
        return V::oneOf(
            V::resourceType(),
            V::stringType()::notEmpty()
        );
    }

    // ------------------------------------------------------------------------------

    /**
     * get logger handler
     *
     * @return mixed
     */
    private function getLoggerHandler(): mixed
    {
        return match (true)
        {
            is_string($this->logFile)   => fopen($this->logFile, 'ab'),
            is_resource($this->logFile) => $this->logFile,

            default => $this->debug,
        };
    }

    // ------------------------------------------------------------------------------
}
