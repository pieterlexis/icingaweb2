<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Translation\Clicommands;

use Icinga\Module\Translation\Cli\TranslationCommand;
use Icinga\Application\Icinga;
use Icinga\File\NonEmptyFileIterator;
use Icinga\Exception\IcingaException;
use Icinga\Util\NamedArrayObject;

class ShowCommand extends TranslationCommand
{
    public function untranslatedAction()
    {
        $filesTree = array();

        foreach (new NonEmptyFileIterator(
            new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        Icinga::app()->getLibraryDir('Icinga')
                    ),
                    \RecursiveIteratorIterator::SELF_FIRST
                ),
                '/.+\\.ph(?:p|tml)(?!.)/ms'
            )
        ) as $file) {
            $file = (string) $file;
            $content = file_get_contents($file);
            if (false === $content) {
                throw new IcingaException('could not get "%s"\'s content', $file);
            }

            $matches = array();
            $cnt = $line = 0;
            foreach (str_split($content) as $chr) {
                switch ($chr) {
                    case "\n":
                        ++$line;
                        break;
                    case '\'':
                    case '"':
                        $result = preg_match(
                            '/\\b(?:translate(?:Plural)?|m?tp?)\\s*\\(\\s*(?!.)/ms',
                            substr($content, 0, $cnt)
                        );
                        if (false === $result) {
                            throw new IcingaException(
                                'an unknown error occurred while PCRE matching'
                            );
                        }
                        if (0 === $result) {
                            if (array_key_exists($line, $matches)) {
                                ++$matches[$line];
                            } else {
                                $matches[$line] = 1;
                            }
                        }
                }
                ++$cnt;
            }

            if (0 !== count($matches)) {
                $fileTree = new NamedArrayObject($file);
                $lines = explode("\n", $content);
                foreach (array_keys($matches) as $line) {
                    $fileTree->append(sprintf('%d:%s', $line + 1, $lines[$line]));
                }
                $filesTree[] = $fileTree;
            }
        }

        foreach (new \IteratorIterator(
            new \RecursiveTreeIterator(
                new \RecursiveArrayIterator($filesTree),
                \RecursiveTreeIterator::SELF_FIRST
            )
        ) as $val) {
            echo $val . PHP_EOL;
        }
    }
}
