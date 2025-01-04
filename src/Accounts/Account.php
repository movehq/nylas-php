<?php

namespace Nylas\Accounts;

use Nylas\Utilities\API;
use Nylas\Utilities\Options;
use Nylas\Authentication\Hosted;

/**
 * ----------------------------------------------------------------------------------
 * Nylas Account
 * ----------------------------------------------------------------------------------
 *
 * @author lanlin
 * @change 2020/04/26
 */
class Account
{
    // ------------------------------------------------------------------------------

    /**
     * @var \Nylas\Utilities\Options
     */
    private $options;

    // ------------------------------------------------------------------------------

    /**
     * Account constructor.
     *
     * @param \Nylas\Utilities\Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    // ------------------------------------------------------------------------------

    /**
     * get grant info
     *
     * @return array
     */
    public function getGrantInfo(array $path = []): array
    {

        return $this->options
            ->getSync()
            ->setPath(...$path)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->get(API::LIST['getGrantInfo']);
    }

    // ------------------------------------------------------------------------------

    /**
     * get new grant
     *
     * @return array
     */
    public function getNewGrant(array $path = []): array
    {

        return $this->options
            ->getSync()
            ->setPath(...$path)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->get(API::LIST['getNewGrant']);
    }

    // ------------------------------------------------------------------------------

    /**
     * delete grant
     *
     * @return array
     */
    public function deleteGrant(array $path = []): array
    {

        return $this->options
            ->getSync()
            ->setPath(...$path)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->delete(API::LIST['deleteGrant']);
    }
}
