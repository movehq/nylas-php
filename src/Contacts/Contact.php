<?php namespace Nylas\Contacts;

use Nylas\Utilities\API;
use Nylas\Utilities\Helper;
use Nylas\Utilities\Options;
use Nylas\Utilities\Validate as V;
use Psr\Http\Message\StreamInterface;

/**
 * ----------------------------------------------------------------------------------
 * Nylas Contacts
 * ----------------------------------------------------------------------------------
 *
 * @author lanlin
 * @change 2018/12/17
 */
class Contact
{

    // ------------------------------------------------------------------------------

    /**
     * @var \Nylas\Utilities\Options
     */
    private $options;

    // ------------------------------------------------------------------------------

    /**
     * Contact constructor.
     *
     * @param \Nylas\Utilities\Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    // ------------------------------------------------------------------------------

    /**
     * get contacts list
     *
     * @param array $params
     * @return array
     */
    public function getContactsList(array $params = [])
    {
        $params['access_token'] =
        $params['access_token'] ?? $this->options->getAccessToken();

        V::doValidate($this->getBaseRules(), $params);

        $header = ['Authorization' => $params['access_token']];

        unset($params['access_token']);

        return $this->options
        ->getSync()
        ->setQuery($params)
        ->setHeaderParams($header)
        ->get(API::LIST['contacts']);
    }

    // ------------------------------------------------------------------------------

    /**
     * add contact
     *
     * @param array $params
     * @return array
     */
    public function addContact(array $params)
    {
        $rules = $this->addContactRules();

        $params['access_token'] =
        $params['access_token'] ?? $this->options->getAccessToken();

        V::doValidate(V::keySet(...$rules), $params);

        $header = ['Authorization' => $params['access_token']];

        unset($params['access_token']);

        return $this->options
        ->getSync()
        ->setFormParams($params)
        ->setHeaderParams($header)
        ->post(API::LIST['contacts']);
    }

    // ------------------------------------------------------------------------------

    /**
     * update contact
     *
     * @param array $params
     * @return array
     */
    public function updateContact(array $params)
    {
        $rules = $this->addContactRules();

        array_push($rules,  V::key('id', V::stringType()->notEmpty()));

        $params['access_token'] =
        $params['access_token'] ?? $this->options->getAccessToken();

        V::doValidate(V::keySet(...$rules), $params);

        $path   = $params['id'];
        $header = ['Authorization' => $params['access_token']];

        unset($params['id'], $params['access_token']);

        return $this->options
        ->getSync()
        ->setPath($path)
        ->setFormParams($params)
        ->setHeaderParams($header)
        ->put(API::LIST['oneContact']);
    }

    // ------------------------------------------------------------------------------

    /**
     * get contact
     *
     * @param string|array $contactId
     * @param string $accessToken
     * @return array
     */
    public function getContact($contactId, string $accessToken = null)
    {
        $params =
        [
            'id'           => Helper::fooToArray($contactId),
            'access_token' => $accessToken ?? $this->options->getAccessToken()
        ];

        $rule = V::keySet(
            V::key('id', V::each(V::stringType()->notEmpty(), V::intType())),
            V::key('access_token', V::stringType()->notEmpty())
        );

        V::doValidate($rule, $params);

        $queues = [];
        $target = API::LIST['oneContact'];
        $header = ['Authorization' => $params['access_token']];

        foreach ($params['id'] as $id)
        {
            $request = $this->options
            ->getAsync()
            ->setPath($id)
            ->setHeaderParams($header);

            $queues[] = function () use ($request, $target)
            {
                return $request->get($target);
            };
        }

        $pools = $this->options->getAsync()->pool($queues, false);

        return $this->concatContactInfos($params['id'], $pools);
    }

    // ------------------------------------------------------------------------------

    /**
     * delete contact
     *
     * @param string|array $contactId
     * @param string $accessToken
     * @return array
     */
    public function deleteContact($contactId, string $accessToken = null)
    {
        $params =
        [
            'id'           => Helper::fooToArray($contactId),
            'access_token' => $accessToken ?? $this->options->getAccessToken()
        ];

        $rule = V::keySet(
            V::key('id', V::each(V::stringType()->notEmpty(), V::intType())),
            V::key('access_token', V::stringType()->notEmpty())
        );

        V::doValidate($rule, $params);

        $queues = [];
        $target = API::LIST['oneContact'];
        $header = ['Authorization' => $params['access_token']];

        foreach ($params['id'] as $id)
        {
            $request = $this->options
            ->getAsync()
            ->setPath($id)
            ->setHeaderParams($header);

            $queues[] = function () use ($request, $target)
            {
                return $request->delete($target);
            };
        }

        $pools = $this->options->getAsync()->pool($queues, false);

        return $this->concatContactInfos($params['id'], $pools);
    }

    // ------------------------------------------------------------------------------

    /**
     * get contact groups
     *
     * @param string $accessToken
     * @return array
     */
    public function getContactGroups(string $accessToken = null)
    {
        $accessToken = $accessToken ?? $this->options->getAccessToken();

        V::doValidate(V::stringType()->notEmpty(), $accessToken);

        $header = ['Authorization' => $accessToken];

        return $this->options
        ->getSync()
        ->setHeaderParams($header)
        ->get(API::LIST['contactsGroups']);
    }

    // ------------------------------------------------------------------------------

    /**
     * get contact picture file (support multiple download)
     *
     * @param array $params
     * @param string $accessToken
     * @return array
     */
    public function getContactPicture(array $params, string $accessToken = null)
    {
        $downloadArr = Helper::arrayToMulti($params);
        $accessToken = $accessToken ?? $this->options->getAccessToken();

        V::doValidate($this->pictureRules(), $downloadArr);
        V::doValidate(V::stringType()->notEmpty(), $accessToken);

        $method = [];
        $target = API::LIST['contactPic'];
        $header = ['Authorization' => $accessToken];

        foreach ($downloadArr as $item)
        {
            $sink = $item['path'];

            $request = $this->options
            ->getAsync()
            ->setPath($item['id'])
            ->setHeaderParams($header);

            $method[] = function () use ($request, $target, $sink)
            {
                return $request->getSink($target, $sink);
            };
        }

        return $this->options->getAsync()->pool($method, true);
    }

    // ------------------------------------------------------------------------------

    /**
     * concat contact infos
     *
     * @param array $params
     * @param array $pools
     * @return array
     */
    private function concatContactInfos(array $params, array $pools)
    {
        $data = [];

        foreach ($params as $index => $item)
        {
            if (isset($pools[$index]['error']))
            {
                $item = array_merge($item, $pools[$index]);
            }

            $data[$item['id']] = $item;
        }

        return $data;
    }

    // ------------------------------------------------------------------------------

    /**
     * rules for download picture
     *
     * @return \Respect\Validation\Validator
     */
    private function pictureRules()
    {
        $path = V::oneOf(
            V::resourceType(),
            V::stringType()->notEmpty(),
            V::instance(StreamInterface::class)
        );

        return  V::arrayType()->each(V::keySet(
            V::key('id', V::stringType()->notEmpty()),
            V::key('path', $path)
        ));
    }

    // ------------------------------------------------------------------------------

    /**
     * get base rules
     *
     * @return \Respect\Validation\Validator
     */
    private function getBaseRules()
    {
        return V::keySet(
            V::keyOptional('limit', V::intType()->min(1)),
            V::keyOptional('offset', V::intType()->min(0)),

            V::keyOptional('email', V::email()),
            V::keyOptional('state', V::stringType()->notEmpty()),
            V::keyOptional('group', V::stringType()->notEmpty()),
            V::keyOptional('source', V::stringType()->notEmpty()),
            V::keyOptional('country', V::stringType()->notEmpty()),

            V::keyOptional('recurse', V::boolType()),
            V::keyOptional('postal_code', V::stringType()->notEmpty()),
            V::keyOptional('phone_number', V::stringType()->notEmpty()),
            V::keyOptional('street_address', V::stringType()->notEmpty()),

            V::key('access_token', V::stringType()->notEmpty())
        );
    }

    // ------------------------------------------------------------------------------

    /**
     * rules for add contact
     *
     * @return array
     */
    private function addContactRules()
    {
        return
        [
            V::keyOptional('given_name', V::stringType()->notEmpty()),
            V::keyOptional('middle_name', V::stringType()->notEmpty()),
            V::keyOptional('surname', V::stringType()->notEmpty()),
            V::keyOptional('birthday', V::date('c')),
            V::keyOptional('suffix', V::stringType()->notEmpty()),
            V::keyOptional('nickname', V::stringType()->notEmpty()),
            V::keyOptional('company_name', V::stringType()->notEmpty()),
            V::keyOptional('job_title', V::stringType()->notEmpty()),

            V::keyOptional('manager_name', V::stringType()->notEmpty()),
            V::keyOptional('office_location', V::stringType()->notEmpty()),
            V::keyOptional('notes', V::stringType()->notEmpty()),
            V::keyOptional('emails', V::arrayVal()->each(V::email())),

            V::keyOptional('im_addresses', V::arrayType()),
            V::keyOptional('physical_addresses', V::arrayType()),
            V::keyOptional('phone_numbers', V::arrayType()),
            V::keyOptional('web_pages', V::arrayType()),

            V::key('access_token', V::stringType()->notEmpty())
        ];
    }

    // ------------------------------------------------------------------------------

}
