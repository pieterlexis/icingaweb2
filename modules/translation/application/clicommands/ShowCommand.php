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
            $chars = str_split($content);
            $charsAmount = count($chars);
            $line = 0;
            for ($i = 0; $i < $charsAmount;) {
                switch ($chars[$i]) {
                    case "\n":
                        ++$line;
                        break;
                    case '\'':
                    case '"':
                        $preg_matches = array();
                        $result = preg_match(
                            '/('
                                . '\'(?:\\\\[\'\\\\]|(?:\\\\)?[\\t\\r\\n\\x20-\\x7e](?<![\'\\\\]))*\'' // 'string'
                                . '|'
                                . '"(?:\\\\["\\\\]|(?:\\\\)?[\\t\\r\\n\\x20-\\x7e](?<!["\\\\]))*"' // "string"
                            . ')(?:\\s*\\.\\s*(?1))*/m',
                            substr($content, $i),
                            $preg_matches
                        );
                        if (false === $result) {
                            throw new IcingaException(
                                'an unknown error occurred while PCRE matching'
                            );
                        }
                        if (0 === $result) {
                            $matches[$line] = false;
                            while ($chars[++$i] !== "\n");
                        } else {
                            if (false === array_key_exists($line, $matches)) {
                                $matches[$line] = array();
                            }
                            $matches[$line][] = $i;
                            for ($j = strlen($preg_matches[0]); $j > 0; --$j) {
                                if ($chars[$i++] === "\n") {
                                    ++$line;
                                }
                            }
                        }
                        continue;
                }
                ++$i;
            }

            foreach ($matches as $linenum => $line) {
                if ($line !== false) {
                    foreach ($line as $idx => $pos) {
                        $result = preg_match(
                            '/\\b(?:translate(?:Plural)?|m?tp?)\\s*\\(\\s*(?!.)/ms',
                            substr($content, 0, $pos)
                        );
                        if (false === $result) {
                            throw new IcingaException(
                                'an unknown error occurred while PCRE matching'
                            );
                        }
                        if (1 === $result) {
                            unset($line[$idx]);
                        }
                    }
                    if (0 === count($line)) {
                        unset($matches[$linenum]);
                    }
                }
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
