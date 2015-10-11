<?php

namespace Tdn\AndroidTools\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Mms.
 */
class Mms implements KeyedInterface
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
    private $date;

    /**
     * @var string
     */
    private $sub;

    /**
     * @var ArrayCollection|Part[]
     */
    private $parts;

    /**
     * @param string $address
     * @param string $contactName
     * @param string $date
     * @param string $sub
     */
    public function __construct($address, $contactName, $date, $sub)
    {
        $this->address = $address;
        $this->contactName = $contactName;
        $this->date = $date;
        $this->sub = $sub;
        $this->parts = new ArrayCollection();
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
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * @param Part $part
     */
    public function addPart(Part $part)
    {
        $part->setMms($this);
        $this->parts->add($part);
    }

    /**
     * @return ArrayCollection|Part[]
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        $parts = $this->getParts();
        $key = sprintf(
            '%s-%s-%s-%s-%s',
            trim($this->getAddress()),
            trim($this->getContactName()),
            trim($this->getDate()),
            trim($this->getSub()),
            implode(
                '-',
                $parts->map(function (Part $part) {
                    return $part->getKey();
                })->toArray()
            )
        );

        return hash('sha512', $key);
    }
}
