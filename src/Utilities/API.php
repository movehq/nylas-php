<?php

namespace Nylas\Utilities;

/**
 * ----------------------------------------------------------------------------------
 * Nylas RESTFul API List
 * ----------------------------------------------------------------------------------
 *
 * @see https://changelog.nylas.com/
 * @see https://docs.nylas.com/reference#api-changelog
 *
 * @version 2.1 (2020/09/30)
 *
 * @author lanlin
 */
class API
{
    // ------------------------------------------------------------------------------

    /**
     * nylas server list array
     */
    public const SERVER = [
        'oregon'  => 'https://api.us.nylas.com',
        'canada'  => 'https://api.us.nylas.com',
        'ireland' => 'https://api.eu.nylas.com',
    ];

    // ------------------------------------------------------------------------------


    // ------------------------------------------------------------------------------

    /**
     * nylas providers (for Native Authentication only)
     *
     * @see https://developer.nylas.com/docs/api/supported-providers/
     * @see https://developer.nylas.com/docs/api/v2/#post-/connect/authorize
     */
    public const PROVIDERS = [
        'gmail',
        'yahoo',
        'exchange',
        'outlook',
        'imap',
        'icloud',
        'hotmail',
        'aol',
        'graph',
        'office365',
        'nylas',
    ];

    // ------------------------------------------------------------------------------

    /**
     * nylas account status
     *
     * @see https://developer.nylas.com/docs/developer-guide/manage-accounts/account-sync-status/#account-management
     */
    public const STATUS = [
        'valid',               // All emails for folders, contacts, and calendars are syncing reliably.
        'invalid',             // The account has an authorization issue and needs to be re-authenticated. Learn more about Account re-authentication.
        'stopped',             // An account stops syncing if it repeatedly encounters the same error or is unable to access the email server. In cases where an account has stopped, you can try to restart it using the downgrade and upgrade endpoints. Learn more about Account re-authentication. If the account continues to fall into a stopped sync state, please contact us.
        'running',             // All emails for folders, contacts, and calendars are syncing reliably.
        'partial',             // See Partial https://developer.nylas.com/docs/developer-guide/manage-accounts/account-sync-status/#partial.
        'exception',           // This can occur if an upstream provider returns an error that Nylas's sync engine doesn't yet understand. Please contact us for accounts in this state.
        'sync-error',          // An unexpected error was raised while syncing an account. Please contact us for accounts in this state.
        'downloading',         // All folders are connected and the account is in the process of syncing all historical messages on the account. Depending on the size of the account and the speed of the connection between Nylas and the email server, this can take up to 24 hours or more to complete. During this time, the account is usable for sending messages and receiving new email messages.
        'initializing',        // The account has been authenticated on the Nylas platform and is in the process of connecting to all the account's folders. Accounts that use email.send as the only scope will always be in an initializing state. Nylas uses folders to determine sync status. email.send doesn't fetch folders.
        'invalid-credentials', // You can only continue to use an account with our API as long as the <ACCESS_TOKEN> is valid. Sometimes, this token is invalidated by the provider when connection settings are changed or by the end-user when their password is changed. When this happens, reauthenticate the account and generate a new <ACCESS_TOKEN> for the account. Learn more about Account re-authentication.
    ];

    // ------------------------------------------------------------------------------

    /**
     * nylas scopes
     *
     * @see https://developer.nylas.com/docs/the-basics/authentication/authentication-scopes/#nylas-scopes
     */
    public const SCOPES = [
        'calendar',                 // Read and modify calendars and events.
        'calendar.free_busy',       // Exchange WebSync (EWS) accounts should add this scope to access the /free-busy endpoint.
        'calendar.read_only',       // Read calendars and events.
    ];

    // ------------------------------------------------------------------------------


    /**
     * nylas api list array
     */
    public const LIST =
    [
        // Authentication
        'oAuthToken'        => '/v3/connect/token',
        'oAuthRevoke'       => '/v3/connect/revoke',
        'oAuthAuthorize'    => '/v3/connect/auth',
        'connectToken'      => '/v3/connect/token',
        'connectAuthorize'  => '/v3/connect/auth',
        'connectTokenInfo'  => '/v3/connect/tokeninfo',

        // Accounts
        'account'            => '/account',
        'manageApp'          => '/a/%s',
        'tokenInfo'          => '/a/%s/accounts/%s/token-info',
        'ipAddresses'        => '/a/%s/accounts/%s/ip_addresses',
        'listAnAccount'      => '/a/%s/accounts/%s',
        'listAllAccounts'    => '/a/%s/accounts',
        'cancelAnAccount'    => '/a/%s/accounts/%s/downgrade',
        'revokeAllTokens'    => '/a/%s/accounts/%s/revoke-all',
        'reactiveAnAccount'  => '/a/%s/accounts/%s/upgrade',

        // Calendars
        'calendars'     => '/v3/grants/%s/calendars',
        'oneCalendar'   => '/v3/grants/%s/calendars/%s',

        // Events
        'returnAllEvents'       => '/v3/grants/%s/events?calendar_id=%s&start=%s&end=%s&limit=%d&show_cancelled=%s',
        'returnAllEventsPage'   => '/v3/grants/%s/events?calendar_id=%s&start=%s&end=%s&limit=%d&show_cancelled=%s&page_token=%s',
        'createEvent'           => '/v3/grants/%s/events?calendar_id=%s',
        'updateEvent'           => '/v3/grants/%s/events/%s?calendar_id=%s',
        'deleteEvent'           => '/v3/grants/%s/events/%s',

        // Grants
        'getGrantInfo'     => '/v3/grants/%s',
        'getNewGrant'      => '/v3/grants/me'

    ];

    // ------------------------------------------------------------------------------
}
