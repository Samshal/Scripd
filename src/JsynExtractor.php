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

    /**
     * @param $jsynFile string | PathUtil
     * @param $sqlSyntax string
     */
    public function __construct($jsynFile, $sqlSyntax)
    {
        self::setJsynFile($jsynFile);
        self::setSqlSyntax($sqlSyntax);
    }

    /**
     * @param $jsynFile string | PathUtil
     *
     * Setter function for the jsonFile global property
     *
     * @return void
     */
    public function setJsynFile($jsynFile)
    {
        $this->jsyn = json_decode(file_get_contents($jsynFile));

        if (isset($this->sqlSyntax)) {
            $sqlSyntax = $this->sqlSyntax;
<<<<<<< HEAD
            if (!isset($this->jsyn->$sqlSyntax)) {
=======
            if (!isset($this->jsyn->$sqlSyntax)){
>>>>>>> 4842a8f3c157a08b4e970aaaefdcc71fbe6b6c13
                $sqlSyntax = "default";
            }
            $this->jsyn = $this->jsyn->$sqlSyntax;
        }

        return;
    }

    /**
     * @param $sqlSyntax string
     *
     * Setter function for the sqlSyntax global property
     *
     * @return void
     */
    public function setSqlSyntax($sqlSyntax)
    {
        $this->sqlSyntax = $sqlSyntax;

        if (isset($this->jsyn->$sqlSyntax)) {
            $this->jsyn = $this->jsyn->$sqlSyntax;
        } else {
            $sqlSyntax ="default";
            $this->jsyn = $this->jsyn->$sqlSyntax;
        }
        else {
            $sqlSyntax ="default";
            $this->jsyn = $this->jsyn->$sqlSyntax;
        }

        return;
    }

    /**
     * @return array
     */
    public function getJsyn()
    {
        return $this->jsyn;
    }

    /**
     * Performs extraction of the appropriate sql syntax
     * fromthe supplied jsyn file.
     *
     * @return void
     */
    public function formatJsyn()
    {
        for ($i = 0; $i < count($this->jsyn); ++$i) {
            if (strpos($this->jsyn[$i], '[') === 0 || strpos($this->jsyn[$i], '{') === (int) 0) {
                $this->jsyn[$i] = strtolower($this->jsyn[$i]);
            } else {
                $this->jsyn[$i] = strtoupper($this->jsyn[$i]);
            }
        }

        return;
    }

    /**
     * Returns the extracted jsyn as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return implode(' ', $this->getJsyn());
    }
}
