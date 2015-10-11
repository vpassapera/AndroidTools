<?php

namespace Tdn\AndroidTools\Model;

/**
 * Class Part.
 */
class Part implements KeyedInterface
{
    /**
     * @var Mms
     */
    private $mms;

    /**
     * @var string
     */
    private $name;

    /**
     * @var null|string
     */
    private $data;

    /**
     * @var null|string
     */
    private $text;

    /**
     * @param string      $name
     * @param string|null $data
     * @param string|null $text
     */
    public function __construct($name, $data, $text)
    {
        $this->name = $name;
        $this->data = $data;
        $this->text = $text;
    }


    /**
     * @param Mms $mms
     */
    public function setMms(Mms $mms)
    {
        $this->mms = $mms;
    }

    /**
     * @return Mms
     */
    public function getMms()
    {
        return $this->mms;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return null|string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return sprintf('%s-%s-%s', trim($this->getName()), substr($this->getData(), 0, 100), $this->getText());
    }
}
