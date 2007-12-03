<?php
/**
 * Check for update
 *
 * @author Team USVN <contact@usvn.info>
 * @link http://www.usvn.info
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt CeCILL V2
 * @copyright Copyright 2007, Team USVN
 * @since 0.7
 * @package usvn
 *
 * This software has been written at EPITECH <http://www.epitech.net>
 * EPITECH, European Institute of Technology, Paris - FRANCE -
 * This project has been realised as part of
 * end of studies project.
 *
 * $Id$
 */
// Call USVN_TranslationTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
	define("PHPUnit_MAIN_METHOD", "USVN_UpdateTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'www/USVN/autoload.php';

/**
 * Test class for USVN_Translation.
 * Generated by PHPUnit_Util_Skeleton on 2007-03-10 at 16:05:57.
 */
class USVN_UpdateTest extends USVN_Test_Test {
	/**
	* Runs the test methods of this class.
	*
	* @access public
	* @static
	*/
	public static function main() {
		require_once "PHPUnit/TextUI/TestRunner.php";

		$suite  = new PHPUnit_Framework_TestSuite("USVN_UpdateTest");
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	protected function setUp()
	{
		parent::setUp();
		$config = Zend_Registry::get('config');
		$config->database = array("adapterName" => "mysql");
	}

	public function test_itsCheckForUpdateTime()
	{
		$config = Zend_Registry::get('config');

		$this->assertTrue(USVN_Update::itsCheckForUpdateTime());
		$config->update = array("lastcheckforupdate" => 10);
		$this->assertTrue(USVN_Update::itsCheckForUpdateTime());
		$config->update = array("lastcheckforupdate" => time() - 10);
		$this->assertFalse(USVN_Update::itsCheckForUpdateTime());
	}

	public function test_getUSVNAvailableVersion()
	{
		$config = Zend_Registry::get('config');
		$config->update = array("availableversion" => "0.8.4");
		$config->save();
		$this->assertEquals($config->version, USVN_Update::getUSVNAvailableVersion());
		$this->assertEquals("0.8.4", USVN_Update::getUSVNAvailableVersion());
	}

	public function test_getInformationsAboutSystem()
	{
		$config = Zend_Registry::get('config');
		$informations = USVN_Update::getInformationsAboutSystem();
		$xml = new SimpleXMLElement($informations);
		$this->assertEquals(phpversion(), (string)$xml->php->version);
		$this->assertEquals(PHP_OS, (string)$xml->host->os);
		$this->assertEquals(php_uname(), (string)$xml->host->uname);
		$this->assertEquals(implode(".", USVN_SVNUtils::getSvnVersion()), (string)$xml->subversion->version);
		$this->assertEquals("en_US", (string)$xml->usvn->translation);
		$this->assertEquals("mysql", (string)$xml->usvn->databaseadapter);
		$this->assertEquals("", (string)$xml->php->ini->register_globals);
		foreach ($xml->php->extension as $e) {
			if ($e == "pdo_sqlite") {
				return;
			}
		}
		$this->fail("pdo_sdqlite extension not found");
	}

	public function test_updateUSVNAvailableVersionNumber()
	{
		USVN_Update::updateUSVNAvailableVersionNumber();
		$this->assertEquals("0.7 RC1", USVN_Update::getUSVNAvailableVersion());
		$config = Zend_Registry::get('config');
		$this->assertNotEquals(0, $config->update->lastcheckforupdate);
	}
}

// Call USVN_UpdateTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "USVN_UpdateTest::main") {
	USVN_UpdateTest::main();
}
