<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Translation\Clicommands;

use Icinga\Module\Translation\Cli\TranslationCommand;
use Icinga\Application\Icinga;
use Icinga\File\NonEmptyFileIterator;
use Icinga\Exception\IcingaException;

class ShowCommand extends TranslationCommand
{
    public function untranslatedAction()
    {
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
            $cnt = 0;
            $line = 1;
            foreach (str_split($content) as $chr) {
                switch ($chr) {
                    case "\n":
                        ++$line;
                        break;
                    case '\'':
                    case '"':
                        /* TODO (AK):
                         *
                         * check for valid string w/ PCRE
                         * @see http://php.net/manual/de/language.types.string.php#language.types.string.syntax.single
                         *
                         * increase $cnt w/ string's size (and skip ++$cnt)
                         */
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
            
            var_dump($matches);
        }
    }
}
