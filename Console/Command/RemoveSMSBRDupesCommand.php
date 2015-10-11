<?php

namespace Tdn\AndroidTools\Console\Command;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Finder\SplFileInfo;
use Tdn\AndroidTools\IO\SmsBackupFileUtils;

/**
 * Class RemoveSMSBRDupesCommand.
 */
class RemoveSMSBRDupesCommand extends Command
{
    const DS = DIRECTORY_SEPARATOR;

    protected function configure()
    {
        $this->setName('remove-smsbr-dupes');
        $this->setDescription('Remove duplicates in target xml file.');
        $this->setHelp(
            'This command is used to target files from the android application "SMS Backup & Restore."' . PHP_EOL .
            'It removes duplicate entries from the target file.' . PHP_EOL .
            'It will create a new file, or optionally overwrite the existing file if <info>--overwrite</info> is used.'
        );

        $this->addArgument(
            'file',
            InputArgument::REQUIRED,
            'Target file'
        );

        $this->addOption(
            'overwrite',
            '',
            InputOption::VALUE_NONE,
            'Overwrite the file instead of creating a new one.'
        );

        $this->addOption(
            'target-file',
            '',
            InputOption::VALUE_OPTIONAL,
            'The file to output.',
            sprintf('cleaned-sms-%s.xml', (new \DateTime())->format('YmdHis'))
        );

        parent::configure();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $notice = new OutputFormatterStyle('blue', null);
        $alert = new OutputFormatterStyle('white', null);

        $output->getFormatter()->setStyle('notice', $notice);
        $output->getFormatter()->setStyle('alert', $alert);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_VERY_VERBOSE,
            LogLevel::WARNING => OutputInterface::VERBOSITY_VERY_VERBOSE,
            LogLevel::ALERT => OutputInterface::VERBOSITY_VERY_VERBOSE,
        ];

        $formatLevelMap = [
            LogLevel::WARNING => 'comment',
            LogLevel::ALERT => 'alert',
            LogLevel::NOTICE => 'notice',
        ];

        $logger = new ConsoleLogger($output, $verbosityLevelMap, $formatLevelMap);
        $file = new SplFileInfo($input->getArgument('file'), null, null);
        $filePath = $file->getRealPath();
        $error = null;

        if ($input->getOption('target-file') && $input->getOption('overwrite')) {
            $logger->error('Options target-file and overwrite should not be enabled at the same time');

            return 1;
        }

        if (!$file->isFile()) {
            $logger->error(sprintf('Could not find file: %s', $input->getArgument('file')));

            return 1;
        }

        try {
            $logger->info(sprintf('Found file %s', $filePath));
            $logger->info('Cleaning contents...');

            $newContents = $this->getSmsBackupFileUtils($logger)->getCleanedContents($file);
            $fileName = ($input->getOption('overwrite') ?
                $file->getRealPath() : $file->getPath() . self::DS . $input->getOption('target-file'));

            $logger->notice(sprintf('Writing contents to %s', $fileName));
            $this->writeContents($fileName, $newContents);
            $logger->info('SUCCESS');

            return 0;
        } catch (\Exception $e) {
            $logger->critical(sprintf('There was an error processing the file: %s', $e->getMessage()));
        }

        return 1;
    }

    /**
     * @param string $fileName
     * @param string $contents
     *
     * @throws IOException When there is an error writing to the file.
     */
    private function writeContents($fileName, $contents)
    {
        if (false === @file_put_contents($fileName, $contents)) {
            throw new IOException(sprintf(
                'Could not write file %s. Reason: %s',
                $fileName,
                implode(', ', error_get_last())
            ));
        }
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return SmsBackupFileUtils
     */
    private function getSmsBackupFileUtils(LoggerInterface $logger)
    {
        return new SmsBackupFileUtils($logger);
    }
}
