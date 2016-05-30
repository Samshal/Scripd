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
 * A robust SQL Generator. Parses database structures defined in json based on the
 * jsyn file format and generates corresponding sql queries.
 *
 * @since  1.0
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 */
final class JsonDbStructure
{
    /**
     * @var array
     *
     * Names of database objects that can be manipulated
     * using major DDL keywords such as 'create', 'alter'
     * and 'drop'
     */
    private $topLevelObjects = [
        ':database',
        ':table',
        ':table-group',
        ':view',
        ':index',
        ':trigger',
        ':function',
        ':stored-procedure',
        ':storage',
        ':security',
    ];

    /**
     * @var array
     *            An array of object definers.
     *
     * Object Definers are Special keywords that accepts array values
     * in a json structure file definition
     */
    private $objectDefiners = [
        'columns',
        'add-column',
        'foreign-key',
    ];

    /**
     * @var array
     *            Special Characters used in jsyn files.
     *
     * Characters which have a special meaning such as braces and
     * square brackets are listed in this array
     */
    private $specialCharacters = [
        'left-curly-brace'     => '{',
        'right-curly-brace'    => '}',
        'left-square-bracket'  => '[',
        'right-square-bracket' => ']',
        'left-bracket'         => '(',
        'right-bracket'        => ')',
    ];

    /**
     * @var string
     */
    private $crudActionKeyword = ':crud-action';

    /**
     * @var string
     */
    private $objectGroupKeyword = '-group';

    /**
     * @var string
     */
    private $jsynExtension = '.jsyn';

    /**
     * @var string
     */
    private $jsynDirectory = __DIR__.'/bin/';

    /**
     * @var null | array
     */
    private $jsonStructure;

    /**
     * @var null | string
     */
    private $sqlVendor;

    /**
     * @var array
     */
    private $generatedSql = [];

    /**
     * @param $jsonStructureFile PathUtil | string | Array
     * @param $sqlVendor string
     */
    public function __construct($jsonStructureFile, $sqlVendor = 'default')
    {
        if (is_array($jsonStructureFile)) {
            $this->jsonStructure = $jsonStructureFile;
        } else {
            $this->jsonStructure = self::getObjectFromJsonFile($jsonStructureFile);
        }
        $this->sqlVendor = $sqlVendor;
    }

    /**
     * @param $jsynDirectory string
     *
     * @return void
     */
    public function setJsynDirectory($jsynDirectory)
    {
        $this->jsynDirectory = $jsynDirectory;
    }

    /**
     * @param $sqlVendor string
     *
     * @return void
     */
    public function setSqlVendor($sqlVendor)
    {
        $this->sqlVendor = $sqlVendor;
    }

    /**
     * @param $topLevelObject string
     * @param $crudAction string
     *
     * Based on the values provided in the $topLevelObject and $crudAction
     * variables, this method tries to derive the name of the jsyn file to use
     * for parsing.
     *
     * @return string | bool
     */
    private function guessJsynFileName($topLevelObject, $crudAction)
    {
        if (in_array($topLevelObject, $this->topLevelObjects)) {
            $this->crudAction = strtolower($crudAction);

            return $this->crudAction.'-'.self::objectIdentifierToString($topLevelObject).$this->jsynExtension;
        }

        return false;
    }

    /**
     * @param $jsonFile PathUtil | string
     *
     * Gets the content of a json file, decodes it and
     * returns an array of the decoded json.
     *
     * @return array
     */
    private function getObjectFromJsonFile($jsonFile)
    {
        $jsonStructure = file_get_contents($jsonFile);

        return json_decode($jsonStructure, JSON_FORCE_OBJECT);
    }

    /**
     * @param $jsonStructure array
     *
     * Tries to get the top level object from an array of
     * a json structure, returns false if no top level object
     * is found.
     *
     * @return string | bool
     */
    private function getProvidedTopLevelObject($jsonStructure)
    {
        foreach ($this->topLevelObjects as $topLevelObject) {
            if (isset($jsonStructure[$topLevelObject])) {
                return $topLevelObject;
            }
        }

        return false;
    }

    /**
     * @param $jsonStructure array
     *
     * Determines if a top level object is a valid one by checking
     * the $topLevelObjects array to see if its present.
     *
     * @return bool
     */
    private function isValidTopLevelObject($jsonStructure)
    {
        foreach ($this->topLevelObjects as $topLevelObject) {
            if (isset($jsonStructure[$topLevelObject])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $objectIdentifier string
     *
     * Strips a supplied $objectIdentifier string variable of
     * special characters and returns a new string with only alphanumeric
     * characters.
     *
     * @return string
     */
    private function objectIdentifierToString($objectIdentifier)
    {
        return substr($objectIdentifier, 1, strlen($objectIdentifier) - 1);
    }

    /**
     * @param $jsonStructure array
     *
     * Converts a $jsonStructure array into a string containing valid
     * sql statements.
     *
     * @return string
     */
    public function generateSqlFromStructure($jsonStructure)
    {
        $topLevelObject = self::getProvidedTopLevelObject($jsonStructure);
        $crudAction = $jsonStructure[$topLevelObject][$this->crudActionKeyword];

        $jsynFileName = self::guessJsynFileName($topLevelObject, $crudAction);

        $jsynExtractor = new JsynExtractor($this->jsynDirectory.$jsynFileName, $this->sqlVendor);
        $jsynExtractor->formatJsyn();
        $jsyn = $jsynExtractor->getJsyn();

        $count = count($jsyn);
        for ($i = 0; $i < $count; ++$i) {
            $string = $jsyn[$i];
            $toSetValue = false;
            $isConstant = false;

            if (self::enclosed($this->specialCharacters['left-square-bracket'], $this->specialCharacters['right-square-bracket'], $string)) {
                $string = str_replace($this->specialCharacters['left-square-bracket'], null, str_replace($this->specialCharacters['right-square-bracket'], null, $string));
                if (self::enclosed($this->specialCharacters['left-curly-brace'], $this->specialCharacters['right-curly-brace'], $string)) {
                    $string = str_replace($this->specialCharacters['left-curly-brace'], null, str_replace($this->specialCharacters['right-curly-brace'], null, $string));
                    $toSetValue = true;
                }
            } elseif (self::enclosed($this->specialCharacters['left-curly-brace'], $this->specialCharacters['right-curly-brace'], $string)) {
                $string = str_replace($this->specialCharacters['left-curly-brace'], null, str_replace($this->specialCharacters['right-curly-brace'], null, $string));
                $toSetValue = true;
            } else {
                $isConstant = true;
            }

            $_string = str_replace(' ', '-', $string);
            if (isset($jsonStructure[$topLevelObject][$_string])) {
                if ($toSetValue && !is_bool($jsonStructure[$topLevelObject][$_string])) {
                    if (in_array($_string, $this->objectDefiners)) {
                        $_str = [];
                        foreach ($jsonStructure[$topLevelObject][$_string] as $jsonStructures) {
                            $_str[] = self::generateSqlFromObjectDefiner([$_string => $jsonStructures], $_string);
                        }
                        $jsonStructure[$topLevelObject][$_string] = '('.implode(', ', $_str).')';
                    }
                    $jsyn[$i] = $jsonStructure[$topLevelObject][$_string];
                } else {
                    $jsyn[$i] = (isset($jsonStructure[$topLevelObject][$_string]) && $jsonStructure[$topLevelObject][$_string] == true) ? strtoupper($string) : null;
                }
            } else {
                if (!$isConstant) {
                    if (isset($jsyn[$i - 1]) && $jsyn[$i - 1] == '=') {
                        unset($jsyn[$i - 1]);
                    }
                    unset($jsyn[$i]);
                }
            }
        }

        return implode(' ', $jsyn);
    }

    /**
     * @param $jsonStructures array
     * @param $objectDefiner string
     *
     * While the {@link generateSqlFromStructure()} method above generates sql string
     * from only valid top level objects, this method generates sql statements from valid
     * object definers. Accepts an $objectDefiner and a $jsonStructure array as parameters.
     *
     * @return string
     */
    public function generateSqlFromObjectDefiner($jsonStructures, $objectDefiner)
    {
        $topLevelObject = $objectDefiner;
        $jsynFileName = $objectDefiner.'.jsyn';

        $jsynExtractor = new JsynExtractor($this->jsynDirectory.$jsynFileName, $this->sqlVendor);
        $jsynExtractor->formatJsyn();
        $jsyn = $jsynExtractor->getJsyn();

        $count = count($jsyn);
        foreach ($jsonStructures as $jsonStructure) {
            $jsonStructure = [$topLevelObject => $jsonStructure];
            for ($i = 0; $i < $count; ++$i) {
                $string = $jsyn[$i];
                $toSetValue = false;
                $isConstant = false;
                $replaceWithComma = false;

                if (self::enclosed($this->specialCharacters['left-square-bracket'], $this->specialCharacters['right-square-bracket'], $string)) {
                    $string = str_replace($this->specialCharacters['left-square-bracket'], null, str_replace($this->specialCharacters['right-square-bracket'], null, $string));
                    if (self::enclosed($this->specialCharacters['left-curly-brace'], $this->specialCharacters['right-curly-brace'], $string)) {
                        $string = str_replace($this->specialCharacters['left-curly-brace'], null, str_replace($this->specialCharacters['right-curly-brace'], null, $string));
                        $toSetValue = true;
                    } elseif (self::enclosed($this->specialCharacters['left-bracket'], $this->specialCharacters['right-bracket'], $string)) {
                        $string = str_replace($this->specialCharacters['left-bracket'], null, str_replace($this->specialCharacters['right-bracket'], null, $string));
                        $toSetValue = false;
                        $replaceWithComma = true;
                    }
                } elseif (self::enclosed($this->specialCharacters['left-curly-brace'], $this->specialCharacters['right-curly-brace'], $string)) {
                    $string = str_replace($this->specialCharacters['left-curly-brace'], null, str_replace($this->specialCharacters['right-curly-brace'], null, $string));
                    $toSetValue = true;
                } else {
                    $isConstant = true;
                }

                $_string = str_replace(' ', '-', $string);
                if (isset($jsonStructure[$topLevelObject][$_string])) {
                    if ($toSetValue && !is_bool($jsonStructure[$topLevelObject][$_string])) {
                        $jsyn[$i] = $jsonStructure[$topLevelObject][$_string];
                    } else {
                        if ($replaceWithComma) {
                            $string = ", $string";
                        }
                        $jsyn[$i] = (isset($jsonStructure[$topLevelObject][$_string]) && $jsonStructure[$topLevelObject][$_string] == true) ? strtoupper($string) : null;
                    }
                } else {
                    if (!$isConstant) {
                        if (isset($jsyn[$i - 1]) && $jsyn[$i - 1] == '=') {
                            unset($jsyn[$i - 1]);
                        }
                        unset($jsyn[$i]);
                    }
                }
            }
        }

        return implode(' ', $jsyn);
    }

    /**
     * @param $encloserPre string
     * @param $encloserPost string
     * @param $enclosee string
     *
     * Checks to see if a string ($enclosee) is enclosed by special characters
     * such as '{' and '}' and '[' and ']'.
     *
     * @return bool
     */
    private function enclosed($encloserPre, $encloserPost, $enclosee)
    {
        if (substr($enclosee, 0, 1) == $encloserPre && substr($enclosee, strlen($enclosee) - 1) == $encloserPost) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Parses a jsonStructure in global scope and assigns
     * a generated array to either of the sql string generator methods
     * depending on the top level objects or object definers.
     *
     * @return bool
     */
    public function parseStructure()
    {
        foreach ($this->jsonStructure as $object => $jsonStructure) {
            if (!strpos($object, $this->objectGroupKeyword)) {
                $jsonStructure = [$object => $jsonStructure];
                if (self::isValidTopLevelObject($jsonStructure)) {
                    $this->generatedSql[] = self::generateSqlFromStructure($jsonStructure);
                }

                $topLevelObject = self::isAnotherObjectPresent($jsonStructure[$object]);
                while ($topLevelObject) {
                    if (strtolower($object) == ':database') {
                        $dbname = ($jsonStructure[$object]['name']);
                        $this->generatedSql[] = "USE $dbname";
                    }
                    $this->jsonStructure = [$topLevelObject => $jsonStructure[$object][$topLevelObject]];
                    $topLevelObject = self::isAnotherObjectPresent($jsonStructure[$object][$topLevelObject]);
                    self::parseStructure();
                }
            } else {
                foreach ($jsonStructure as $_jsonStructure) {
                    $object = substr($object, 0, strlen($object) - strpos($object, $this->objectGroupKeyword));
                    $_jsonStructure = [$object => $_jsonStructure];
                    if (self::isValidTopLevelObject($_jsonStructure)) {
                        $this->generatedSql[] = self::generateSqlFromStructure($_jsonStructure);
                    }

                    $topLevelObject = self::isAnotherObjectPresent($_jsonStructure[$object]);
                    while ($topLevelObject) {
                        $this->jsonStructure = [$topLevelObject => $_jsonStructure[$object][$topLevelObject]];
                        $topLevelObject = self::isAnotherObjectPresent($_jsonStructure[$object][$topLevelObject]);
                        self::parseStructure();
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param $jsonStructure array
     *
     * Determines if another top level object or object definer is
     * present within the supplied json structure.
     * Returns the name of the object if found and false if not found.
     *
     * @return string
     */
    public function isAnotherObjectPresent($jsonStructure)
    {
        foreach ($this->topLevelObjects as $topLevelObject) {
            if (isset($jsonStructure[$topLevelObject])) {
                return $topLevelObject;
            }
        }
    }

    /**
     * @param $delimiter string
     *
     * Returns the parsed and generated string containing the sql
     * statement delimited by a value supplied in the $delimiter
     * parameter.
     *
     * @return string
     */
    public function getGeneratedSql($delimiter = ";\n")
    {
        return implode($delimiter, $this->generatedSql);
    }
}
