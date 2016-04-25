<?php

/*
 * This file is part of the samshal/scripd package.
 *
 * (c) Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Samshal\Scripd;

/**
 * Contains utility methods for parsing a jsyn file.
 *
 * @since  1.0
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 */
final class JsynExtractor
{
    /**
     * @var string
     *
     * Content of the JSYN File
     */
    private $jsyn;

    /**
     * @var string
     *
     * SQL Syntax to use for script generation
     */
    private $sqlSyntax;

    public function __construct($jsynFile, $sqlSyntax)
    {
        self::setJsynFile($jsynFile);
        self::setSqlSyntax($sqlSyntax);
    }

    public function setJsynFile($jsynFile)
    {
        $this->jsyn = json_decode(file_get_contents($jsynFile));

        if (isset($this->sqlSyntax)) {
            $sqlSyntax = $this->sqlSyntax;
            $this->jsyn = $this->jsyn->$sqlSyntax;
        }

        return;
    }

    public function setSqlSyntax($sqlSyntax)
    {
        $this->sqlSyntax = $sqlSyntax;

        if (isset($this->jsyn->$sqlSyntax)) {
            $this->jsyn = $this->jsyn->$sqlSyntax;
        }

        return;
    }

    public function getJsyn()
    {
        return $this->jsyn;

        return;
    }

    public function formatJsyn()
    {
        for ($i = 0; $i < count($this->jsyn); ++$i) {
            if (strpos($this->jsyn[$i], '[') === 0 || strpos($this->jsyn[$i], '{') === (int) 0) {
                $this->jsyn[$i] = strtolower($this->jsyn[$i]);
            } else {
                $this->jsyn[$i] = strtoupper($this->jsyn[$i]);
            }
        }
    }

    public function toString()
    {
        return implode(' ', $this->getJsyn());
    }
}
