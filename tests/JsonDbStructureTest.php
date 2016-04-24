<?php

/*
 * This file is part of the samshal/scripd package.
 *
 * (c) Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Samshal\Scripd\JsonDbStructure;

class JsonDbStructureTest extends PHPUnit_Framework_TestCase {
	protected $jsonFiles = [1=>__DIR__."/1.json", 2=>__DIR__."/2.json", 3=>__DIR__."/3.json", 4=>__DIR__."/4.json"];

	protected $sqlStmts = [
		1 => "CREATE DATABASE another_unify_schools".
			 "USE another_unify_schools;".
			 "CREATE TABLE students (id int PRIMARY KEY, first_name varchar(20) DEFAULT 'samuel', last_name varchar(20), class varchar(10));".
			 "CREATE TABLE faculty (fac_id int AUTO_INCREMENT PRIMARY KEY, first_name varchar(20), last_name varchar(20));".
			 "CREATE TABLE subjects (subject_id int AUTO_INCREMENT PRIMARY KEY, subject_name varchar(30), subject_faculty int REFERENCES faculty("."fac_id) ON UPDATE cascade ON DELETE set null)",

		2 => "ALTER TABLE facultys ADD COLUMN (full_name varchar(30) NOT NULL DEFAULT 'john doe')",

		3 => "DROP TABLE IF EXISTS faculty;".
			 "DROP DATABASE another_unify_schools",

		4 => "CREATE VIEW student_vw (id, first_name, last_name, class) AS select * from students where id < 3 WITH LOCAL CHECK OPTION"
	];

	public function testParseStructure(){
		foreach ($this->jsonFiles as $index=>$jsonFile){
			$jsonDbStructure = new Samshal\Scripd\JsonDbStructure($jsonFile, "mysql");
			$jsonDbStructure->parseStructure();

			$this->assertEquals($jsonDbStructure->getGeneratedSql(), $this->sqlStmts[$index]);
		}
	}
}