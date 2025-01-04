<?php

namespace Nylas\Events;

use Nylas\Utilities\API;
use Nylas\Utilities\Helper;
use Nylas\Utilities\Options;
use Nylas\Utilities\Validator as V;

/**
 * ----------------------------------------------------------------------------------
 * Nylas Events
 * ----------------------------------------------------------------------------------
 *
 * @see https://docs.nylas.com/reference#event-limitations
 *
 * @author lanlin
 * @change 2023/07/21
 */
class Event
{
    // ------------------------------------------------------------------------------

    /**
     * @var Options
     */
    private Options $options;

    /**
     * @var string
     */
    private string $notify = 'notify_participants';

    // ------------------------------------------------------------------------------

    /**
     * Event constructor.
     *
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    // ------------------------------------------------------------------------------

    /**
     * get events list
     *
     * @param array $params
     *
     * @return array
     * @throws GuzzleException
     */
    public function returnAllEvents(array $path = []): array
    {
        return $this->options
            ->getSync()
            ->setPath(...$path)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->get(API::LIST['returnAllEvents']);
    }

    // ------------------------------------------------------------------------------

    /**
     * get events list
     *
     * @param array $params
     *
     * @return array
     * @throws GuzzleException
     */
    public function returnAllEventsPage(array $path = []): array
    {
        return $this->options
            ->getSync()
            ->setPath(...$path)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->get(API::LIST['returnAllEventsPage']);
    }

    // ------------------------------------------------------------------------------

    /**
     * Creates an event, conference or add metadata.
     *
     * @see https://developer.nylas.com/docs/api/v2/#post-/events
     *
     * @param array $params
     * @param bool  $notifyParticipants
     *
     * @return array
     * @throws GuzzleException
     */
    public function createAnEvent(array $path, array $params): array
    {

        $data = $this->options
            ->getSync()
            ->setPath(...$path)
            ->setFormParams($params)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->post(API::LIST['createEvent']);

        return $data;
    }

    // ------------------------------------------------------------------------------

    /**
     * Returns an event by ID.
     *
     * @see https://developer.nylas.com/docs/api/v2/#get-/events/id
     *
     * @param mixed $eventId
     *
     * @return array
     */
    public function returnAnEvent(mixed $eventId): array
    {
        $eventId = Helper::fooToArray($eventId);

        V::doValidate(V::simpleArray(V::stringType()::notEmpty()), $eventId);

        $queues = [];

        foreach ($eventId as $id)
        {
            $request = $this->options
                ->getAsync()
                ->setPath($id)
                ->setHeaderParams($this->options->getAuthorizationHeader());

            $queues[] = static function () use ($request)
            {
                return $request->get(API::LIST['oneEvent']);
            };
        }

        $pools = $this->options->getAsync()->pool($queues, false);

        return Helper::concatPoolInfos($eventId, $pools);
    }

    // ------------------------------------------------------------------------------

    /**
     * Updates an event, conference, or metadata.
     *
     * @see https://developer.nylas.com/docs/api/v2/#put-/events/id
     *
     * @param string $eventId
     * @param array  $params
     * @param bool   $notifyParticipants
     *
     * @return array
     * @throws GuzzleException
     */
    public function updateAnEvent(array $path, array $params): array
    {
        return $this->options
            ->getSync()
            ->setPath(...$path)
            ->setFormParams($params)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->put(API::LIST['updateEvent']);
    }

    // ------------------------------------------------------------------------------

    /**
     * Deletes an event.
     *
     * @see https://developer.nylas.com/docs/api/v2/#delete-/events/id
     *
     * @param string $eventId
     * @param bool   $notifyParticipants
     *
     * @return array
     */
    public function deleteAnEvent(array $path = []): array
    {
        return $this->options
            ->getAsync()
            ->setPath(...$path)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->delete(API::LIST['deleteEvent']);
    }

    // ------------------------------------------------------------------------------

    /**
     * The RSVP endpoint allows you to send attendance status updates to event organizers.
     *
     * @see https://developer.nylas.com/docs/api/v2/#post-/send-rsvp
     *
     * @param array $params
     *
     * @return array
     * @throws GuzzleException
     */
    public function sendRSVP(array $params): array
    {
        V::doValidate(V::keySet(
            V::key('status', V::in(['yes', 'no', 'maybe'])),
            V::key('event_id', V::stringType()::notEmpty()),
            V::key('account_id', V::stringType()::notEmpty()),
        ), $params);

        return $this->options
            ->getSync()
            ->setFormParams($params)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->post(API::LIST['rsvpEvent']);
    }

    // ------------------------------------------------------------------------------

    /**
     * Use this endpoint to generate an ICS file for events, including virtual calendars.
     * This endpoint does not create an event.
     *
     * @see https://developer.nylas.com/docs/api/v2/#post-/events/to-ics
     *
     * @param array $params
     *
     * @return array
     * @throws GuzzleException
     */
    public function generateICSFile(array $params): array
    {
        V::doValidate(Validation::getICSRules(), $params);

        return $this->options
            ->getSync()
            ->setFormParams($params)
            ->setHeaderParams($this->options->getAuthorizationHeader())
            ->post(API::LIST['icsEvent']);
    }

    // ------------------------------------------------------------------------------
}
