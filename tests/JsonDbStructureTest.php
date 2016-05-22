<?php

/*
 * This file is part of the samshal/scripd package.
 *
 * (c) Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class JsonDbStructureTest extends PHPUnit_Framework_TestCase
{
    public function parseJsonFile($jsonFile)
    {
        $jsonDbStructure = new Samshal\Scripd\JsonDbStructure($jsonFile, 'sqlite');
        $jsonDbStructure->parseStructure();

        return $jsonDbStructure->getGeneratedSql(';');
    }

    public function parseArrayInput($jsonFile)
    {
        $jsonArray = json_decode(file_get_contents($jsonFile), JSON_FORCE_OBJECT);

        $jsonDbStructure = new Samshal\Scripd\JsonDbStructure($jsonArray, 'mysql');
        $jsonDbStructure->parseStructure();

        return $jsonDbStructure->getGeneratedSql(';');
    }

    public function parseWithoutVendor($jsonFile)
    {
        $jsonDbStructure = new Samshal\Scripd\JsonDbStructure($jsonFile);
        $jsonDbStructure->parseStructure();

        return $jsonDbStructure->getGeneratedSql(';');
    }

    /**
     * @dataProvider dataProvider
     */
    public function testStructureParserWithJsonFileInput($expected, $jsonFile)
    {
        $this->assertEquals($expected, self::parseJsonFile($jsonFile));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testStructureParserWithArrayInput($expected, $jsonFile)
    {
        $this->assertEquals($expected, self::parseArrayInput($jsonFile));
    }
    
    /**
     * @dataProvider dataProvider
     */
    public function testStructureParserWithoutVendor($expected, $jsonFile)
    {
        $this->assertEquals($expected, self::parseWithoutVendor($jsonFile));
    }

    public function dataProvider()
    {
        return array(
            'Create Database with Multiple Tables' => [
                'CREATE DATABASE another_unify_schools;'.
                 'USE another_unify_schools;'.
                 "CREATE TABLE students (id int PRIMARY KEY, first_name varchar(20) DEFAULT 'samuel', last_name varchar(20), class varchar(10));".
                 'CREATE TABLE faculty (fac_id int AUTO_INCREMENT PRIMARY KEY, first_name varchar(20), last_name varchar(20));'.
                 'CREATE TABLE subjects (subject_id int AUTO_INCREMENT PRIMARY KEY, subject_name varchar(30), subject_faculty int , FOREIGN KEY (subject_faculty) REFERENCES faculty(fac_id) ON UPDATE cascade ON DELETE set null)', __DIR__.'/json/1.json',
            ],
            'Alter Table' => [
                "ALTER TABLE facultys ADD COLUMN (full_name varchar(30) NOT NULL DEFAULT 'john doe')", __DIR__.'/json/2.json',
            ],
            'Drop Objects' => [
                'DROP TABLE IF EXISTS faculty;'.
                'DROP DATABASE another_unify_schools', __DIR__.'/json/3.json',
            ],
            'Create View' => [
                'CREATE VIEW student_vw (id, first_name, last_name, class) AS select * from students where id < 3 WITH LOCAL CHECK OPTION', __DIR__.'/json/4.json',
            ],
        );
    }
}
