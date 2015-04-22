<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Translation\Clicommands;

require 'PHP-Parser/lib/bootstrap.php';

use Icinga\Module\Translation\Cli\TranslationCommand;
use Icinga\Application\Icinga;
use Icinga\File\NonEmptyFileIterator;
use Icinga\Exception\IcingaException;
use Icinga\Util\NamedArrayObject;

use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\Lexer;
use PhpParser\Error;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Stmt\Throw_;

class ShowCommand extends TranslationCommand
{
    public function untranslatedAction()
    {
        $parser = new Parser(new Lexer());

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

            try {
                $stmts = $parser->parse($content);
            } catch (Error $e) {
                continue;
            }

            $matches = array();

            $this->scanNodes($stmts, $matches);

            if (false === empty($matches)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                $fileTree = new NamedArrayObject($file);
                foreach ($matches as $match) {
                    $startLine = $match->getAttribute('startLine', 1);
                    $fileTree->append(sprintf('%d:%s', $startLine, $lines[$startLine - 1]));
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

    protected function scanNodes($nodes, array &$result)
    {
        if (is_array($nodes)) {
            foreach ($nodes as $node) {
                $this->scanNodes($node, $result);
            }
        } else if ($nodes instanceof Node) {
            if ($nodes instanceof Throw_) {
                return;
            }

            if ((
                (
                    $nodes instanceof MethodCall || $nodes instanceof StaticCall
                ) && $nodes->name === 'translate'
            ) || (
                $nodes instanceof FuncCall && $nodes->name instanceof Name
                &&
                1 === count($nodes->name->parts) && $nodes->name->parts[0] === 't'
            )) {
                return;
            }

            if ($nodes instanceof String_ || $nodes instanceof Encapsed) {
                $result[] = $nodes;
                return;
            }

            foreach ($nodes->getSubNodeNames() as $subNodeName) {
                $this->scanNodes($nodes->{$subNodeName}, $result);
            }
        }
    }
}
