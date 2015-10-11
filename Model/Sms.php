<?php

namespace Tdn\AndroidTools\Model;

/**
 * Class Sms.
 */
class Sms implements KeyedInterface
{
    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $contactName;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $date;

    /**
     * @param string $address
     * @param string $contactName
     * @param string $body
     * @param string $date
     */
    public function __construct($address, $contactName, $body, $date)
    {
        $this->address = $address;
        $this->contactName = $contactName;
        $this->body = $body;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return hash(
            'sha512',
            sprintf('%s-%s-%s', trim($this->getDate()), trim($this->getAddress()), trim($this->getBody()))
        );
    }
}
