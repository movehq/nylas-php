<?php

namespace Nylas;

use Nylas\Utilities\Options;
use Nylas\Exceptions\NylasException;

/**
 * ----------------------------------------------------------------------------------
 * Nylas Client
 * ----------------------------------------------------------------------------------
 *
 * @method Accounts\Abs       Accounts()
 * @method Authentication\Abs Authentication()
 * @method Calendars\Abs      Calendars()
 * @method Deltas\Abs         Deltas()
 * @method Events\Abs         Events()
 * @method Threads\Abs        Threads()
 * @method JobStatuses\Abs    JobStatuses()
 *
 * @author lanlin
 * @change 2021/03/18
 */
class Client
{
    // ------------------------------------------------------------------------------

    /**
     * @var Options
     */
    private $options;

    // ------------------------------------------------------------------------------

    /**
     * Client constructor.
     *
     * @param array $options
     *                       [
     *                       'debug'            => bool,
     *                       'region'           => 'oregon',
     *                       'log_file'         => 'log file path',
     *                       'account_id'       => '',
     *                       'access_token'     => '',
     *                       'client_id'        => 'required',
     *                       'client_secret'    => 'required',
     *                       ]
     */
    public function __construct(array $options)
    {
        $this->options = new Options($options);
    }

    // ------------------------------------------------------------------------------

    /**
     * call nylas apis
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return object
     */
    public function __call(string $name, array $arguments): object
    {
        $apiClass = __NAMESPACE__.'\\'.\ucfirst($name).'\\Abs';

        // check class exists
        if (!\class_exists($apiClass))
        {
            throw new NylasException(null, "class {$apiClass} not found!");
        }

        return new $apiClass($this->options);
    }

    // ------------------------------------------------------------------------------

    /**
     * get options instance for setting options
     *
     * @return \Nylas\Utilities\Options
     */
    public function Options(): Options
    {
        return $this->options;
    }

    // ------------------------------------------------------------------------------
}
