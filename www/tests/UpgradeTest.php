<?php
/**
 * Test upgrade script
 *
 * @author Team USVN <contact@usvn.info>
 * @link http://www.usvn.info
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt CeCILL V2
 * @copyright Copyright 2007, Team USVN
 * @since 0.7
 * @package test
 *
 * This software has been written at EPITECH <http://www.epitech.net>
 * EPITECH, European Institute of Technology, Paris - FRANCE -
 * This project has been realised as part of
 * end of studies project.
 *
 * $Id$
 */

include 'www/update/0.6.5/db.php';
include 'www/update/0.7 RC3/db.php';

// Call USVN_Auth_Adapter_DbTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
	define("PHPUnit_MAIN_METHOD", "USVN_UpgradeTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'www/USVN/autoload.php';

/**
 * Test class for USVN_Auth_Adapter_Db.
 * Generated by PHPUnit_Util_Skeleton on 2007-03-25 at 09:51:30.
 */
 class USVN_Database_struct
 {
	public $list_tables = array();
	public $tables_info = array();

	public function __construct($db)
	{
		$this->list_tables = $db->listTables();
		foreach ($this->list_tables as $table) {
			$this->tables_info[$table] = $db->describeTable($table);
		}
	}
 }

class USVN_UpgradeTest extends USVN_Test_DB {
	private $struct_after_install;
	private $struct_after_upgrade;
    private $params;

	public static function main() {
		require_once "PHPUnit/TextUI/TestRunner.php";

		$suite  = new PHPUnit_Framework_TestSuite("USVN_UpgradeTest");
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	protected function setUp()
	{
		parent::setUp();
		$this->params = array ('host'     => 'localhost',
			 				'username' => 'usvn-test',
			 				'password' => 'usvn-test',
			 				'dbname'   => 'usvn-test');
		if (getenv('DB') == "PDO_SQLITE" || getenv('DB') === false) {
			$this->params['dbname'] = "tests/usvn.db";
			$this->db = Zend_Db::factory('PDO_SQLITE', $this->params);
		}
		else {
			$this->db = Zend_Db::factory(getenv('DB'), $this->params);
		}
		$this->struct_after_install = new USVN_Database_struct($this->db);
		$this->reconnect();
		if (getenv('DB') == "PDO_SQLITE" || getenv('DB') === false) {
			USVN_Db_Utils::loadFile($this->db, "www/SQL/0.6/sqlite.sql");
		}
		else {
			USVN_Db_Utils::loadFile($this->db, "www/SQL/0.6/mysql.sql");
		}
	}

    private function reconnect()
    {
		$this->_clean();
		if (getenv('DB') == "PDO_SQLITE" || getenv('DB') === false) {
			$this->db->closeConnection();
			$this->db = null;
			$this->db = Zend_Db::factory('PDO_SQLITE', $this->params);
		}
		else {
			$this->db = Zend_Db::factory(getenv('DB'), $this->params);
		}
    }

    private function run_upgrade()
    {
		if (getenv('DB') == "PDO_SQLITE" || getenv('DB') === false) {
			Sqlite_queries($this->db);
			Sqlite_queries_07RC3($this->db);
		}
		else {
			Mysql_queries($this->db);
		}
		$this->struct_after_upgrade = new USVN_Database_struct($this->db);
    }

	private function checkField($field_install, $field_upgrade, $table)
	{
		foreach (array_keys($field_install) as $info) {
			if ($info != 'COLUMN_POSITION') {
				$this->assertEquals($field_install[$info], $field_upgrade[$info],
					"Field " . $field_install['COLUMN_NAME'] . " into $table $info is different"
				);
			}
		}
	}

	public function testUpgrade()
	{
        $this->run_upgrade();
		$this->assertEquals($this->struct_after_install->list_tables, $this->struct_after_upgrade->list_tables, "Upgrade doesn't create all tables");
		foreach ($this->struct_after_install->list_tables as $table) {
			foreach (array_keys($this->struct_after_install->tables_info[$table]) as $field) {
					$this->assertTrue(isset($this->struct_after_upgrade->tables_info[$table][$field]), "Field $field doesn't exist into $table");
					$this->checkField($this->struct_after_install->tables_info[$table][$field], $this->struct_after_upgrade->tables_info[$table][$field], $table);
				}
		}
	}

    public function testDuplicateInUsvnUsersToGroups()
    {
		$this->db->query("INSERT INTO usvn_users (users_id, users_login) VALUES (1,'noplay');");
		$this->db->query("INSERT INTO usvn_users (users_id, users_login) VALUES (2,'stem');");
		$this->db->query("INSERT INTO usvn_groups (groups_id, groups_name) VALUES (1,'epitech');");
		$this->db->query("INSERT INTO usvn_groups (groups_id, groups_name) VALUES (2,'etna');");

		$this->db->query("INSERT INTO usvn_users_to_groups (users_id, groups_id, is_leader) VALUES (1,1,0);");
		try {
			$this->db->query("INSERT INTO usvn_users_to_groups (users_id, groups_id, is_leader) VALUES (1,1,0);");
		}
		catch (Exception $e) {
			return;
		}
		$this->db->query("INSERT INTO usvn_users_to_groups (users_id, groups_id, is_leader) VALUES (2,1,0);");
		$this->db->query("INSERT INTO usvn_users_to_groups (users_id, groups_id, is_leader) VALUES (1,1,1);");
		$this->db->query("INSERT INTO usvn_users_to_groups (users_id, groups_id, is_leader) VALUES (1,2,0);");
        $this->run_upgrade();
        $table = new USVN_Db_Table_UsersToGroups();
        $this->assertEquals(2, count($table->fetchAll("groups_id = 1")));
        $this->assertEquals(1, count($table->fetchAll("groups_id = 2")));
        $this->assertEquals(2, count($table->fetchAll("users_id = 1")));
    }
}

// Call USVN_Auth_Adapter_DbTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "USVN_UpgradeTest::main") {
	USVN_UpgradeTest::main();
}
?>
