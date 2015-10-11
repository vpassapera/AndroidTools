<?php

namespace Tdn\AndroidTools\IO;

use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\SplFileInfo;
use Doctrine\Common\Collections\ArrayCollection;
use Tdn\AndroidTools\Model\KeyedInterface;
use Tdn\AndroidTools\Model\Mms;
use Tdn\AndroidTools\Model\Part;
use Tdn\AndroidTools\Model\Sms;
use Tdn\PhpTypes\Type\StringType;

/**
 * Class SmsBackupFileUtils.
 */
class SmsBackupFileUtils
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param SplFileInfo $file
     *
     * @return string
     */
    public function getCleanedContents(SplFileInfo $file)
    {
        $document = $this->getXmlDocument($file->getRealPath());
        $document = $this->removeDuplicates($document);
        $document->formatOutput = true;
        $document->preserveWhiteSpace = true;

        return $document->saveXML();
    }

    /**
     * @param $path
     *
     * @return \DOMDocument
     */
    private function getXmlDocument($path)
    {
        $this->logger->debug('Translating string to dom object...');
        $prev = libxml_use_internal_errors(true);
        $document = new \DOMDocument('1.0', 'UTF-8');
        if (false === $document->load($path)) {
            $errors = libxml_get_errors();
            $errors = array_map(
                function ($error) {
                    if (is_string($error)) {
                        return $error;
                    }

                    if ($error instanceof \libXmlError) {
                        return $error->message;
                    }

                    return null;
                },
                $errors
            );

            $this->logger->critical(print_r($errors, true));

            throw new \RuntimeException(
                sprintf(
                    'Error(s) parsing file: %s.',
                    implode(', ', $errors)
                )
            );
        }

        libxml_use_internal_errors($prev);
        $this->logger->debug('Done...');

        return $document;
    }

    /**
     * @param \DOMDocument $oldDocument
     *
     * @return \DOMDocument
     */
    private function removeDuplicates(\DOMDocument $oldDocument)
    {
        $count = 0;
        $container = new ArrayCollection();
        $currentSmsCollection = $oldDocument->getElementsByTagName('sms');
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $cleanedMessagesCollection = $doc->createElement('smses');
        $this->logger->debug('Clearing duplicate messages.');
        $this->logger->alert(sprintf('Starting count: %s', $currentSmsCollection->length));

        /** @var \DOMElement $rawSms */
        foreach ($currentSmsCollection as $rawSms) {
            $sms = new Sms(
                $rawSms->getAttribute('address'),
                $rawSms->getAttribute('contact_name'),
                $rawSms->getAttribute('body'),
                $rawSms->getAttribute('date')
            );

            $this->addMessageToContainer($doc, $cleanedMessagesCollection, $rawSms, $container, $sms, $count);
        }

        /** @var \DOMElement $rawMms */
        foreach ($oldDocument->getElementsByTagName('mms') as $rawMms) {
            $mms = new Mms(
                $rawMms->getAttribute('address'),
                $rawMms->getAttribute('contact_name'),
                $rawMms->getAttribute('date'),
                $rawMms->getAttribute('sub')
            );

            /** @var \DOMElement $rawPart */
            foreach ($rawMms->getElementsByTagName('part') as $rawPart) {
                $part = new Part(
                    $rawPart->getAttribute('name'),
                    $rawPart->getAttribute('data'),
                    $rawPart->getAttribute('text')
                );

                $mms->addPart($part);
            }

            $this->addMessageToContainer($doc, $cleanedMessagesCollection, $rawMms, $container, $mms, $count);
        }

        $cleanedMessagesCollection->setAttribute('count', $count);
        $doc->appendChild($cleanedMessagesCollection);

        return $doc;
    }


    /**
     * @param \DOMDocument    $doc
     * @param \DOMElement     $cleanedMessagesCollection
     * @param \DOMElement     $element
     * @param ArrayCollection $container
     * @param KeyedInterface  $message
     * @param int             $count
     */
    private function addMessageToContainer(
        \DOMDocument $doc,
        \DOMElement $cleanedMessagesCollection,
        \DOMElement $element,
        ArrayCollection $container,
        KeyedInterface $message,
        &$count
    ) {
        $formattedKey = sprintf('%s...[%d]', substr($message->getKey(), 0, 50), mb_strlen($message->getKey()));
        $this->logger->debug(sprintf('Searching for key %s', $formattedKey));
        if ($container->containsKey($message->getKey())) {
            $this->logger->warning(sprintf('Found duplicate %s...skipping.', $this->getCleanClassName($message)));

            return;
        }

        $container->set($message->getKey(), $message);
        $el = $doc->importNode($element, true);
        $cleanedMessagesCollection->appendChild($el);
        ++$count;

        $this->logger->debug(sprintf('Added key %s', $formattedKey));
        $this->logger->alert(sprintf('New Count: %s', $count));
    }

    /**
     * @param $message
     *
     * @return string
     */
    private function getCleanClassName($message)
    {
        $fqdn = StringType::create(get_class($message));
        $type = $fqdn->substr($fqdn->indexOfLast('\\'), $fqdn->length())->trim('\\')->toUpperCase();

        return $type;
    }
}
