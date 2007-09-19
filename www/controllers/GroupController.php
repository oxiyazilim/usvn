<?php
/**
 * Display group homepage.
 *
 * @author Team USVN <contact@usvn.info>
 * @link http://www.usvn.info
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt CeCILL V2
 * @copyright Copyright 2007, Team USVN
 * @since 0.7
 * @package usvn
 * @subpackage group
 *
 * This software has been written at EPITECH <http://www.epitech.net>
 * EPITECH, European Institute of Technology, Paris - FRANCE -
 * This project has been realised as part of
 * end of studies project.
 *
 * $Id$
 */

class GroupController extends USVN_Controller
{
	/**
     * Pre-dispatch routines
     *
     * Called before action method. If using class with
     * {@link Zend_Controller_Front}, it may modify the
     * {@link $_request Request object} and reset its dispatched flag in order
     * to skip processing the current action.
     *
     * @return void
     */
	public function preDispatch()
	{
		parent::preDispatch();

		$group = $this->getRequest()->getParam('group');
		$table = new USVN_Db_Table_Groups();
		$group = $table->fetchRow(array("groups_name = ?" => $group));
	//	$this->view->user = $this->getRequest()->getParam('user');
		/*$identity = Zend_Auth::getInstance()->getIdentity();
		$table = new USVN_Db_Table_Users();
		$this->view->user = $table->fetchRow(array("users_login = ?" => $identity['username']));*/
		$this->_group = $group;
	}

	/**
	 * Group row object
	 *
	 * @var USVN_Db_Table_Row_Group
	 */
	protected $_group;

	public function indexAction()
	{
		$this->view->group = $this->_group;
	}

	public function managegroupAction()
	{
		$request = $this->getRequest();
		/* @var $request USVN_Controller_Request_Http */

		$table = new USVN_Db_Table_Groups();
		$group = $table->fetchRow(array("groups_name = ?" => $request->getParam('group')));
		/* @var $group USVN_Db_Table_Row_Group */

		try {
			$table = new USVN_Db_Table_Users();
			if ($request->getParam('addlogin', "") != "")
			{

				$user = $table->fetchRow(array("users_login = ?" => $request->getParam('addlogin')));
				if ($user === null) {
					throw new USVN_Exception(sprintf(T_("Unknown user %s"), $request->getParam('addlogin')));
				}
				if (!$group->hasUser($user))
					$group->addUser($user);
				else
					$group->updateLeaderUser($user, 0);
			}
			if ($request->getParam('deleteid', 0) != 0) {
				$user = $table->fetchRow(array("users_id = ?" => $request->getParam('deleteid')));
				if ($user === null) {
					throw new USVN_Exception(sprintf(T_("Unknown user %s"), $request->getParam('deleteid')));
				}
				if ($group->hasUser($user))
					$group->deleteUser($user);
			}
			if (isset($user)) {
				$this->_redirect("/group/{$group->name}/");
			}
		}
		catch (Exception $e) {
			$this->view->message = $e->getMessage();
		}

	//	$this->view->project = $this->_project;
		$this->view->group = $group;
	}
	
	public function addleadergroupAction()
	{
		$request = $this->getRequest();
		/* @var $request USVN_Controller_Request_Http */

		$table = new USVN_Db_Table_Groups();
		$group = $table->fetchRow(array("groups_name = ?" => $request->getParam('group')));
		/* @var $group USVN_Db_Table_Row_Group */

		try {
			$table = new USVN_Db_Table_Users();
			if ($request->getParam('ap', "") != "") 
			{		
				$user = $table->fetchRow(array("users_login = ?" => $request->getParam('ap')));
				if ($user === null) {
					throw new USVN_Exception(sprintf(T_("Unknown user %s"), $request->getParam('ap')));
				}
				if (!$group->hasUser($user))
				{
					$group->addLeaderUser($user);
				}
				else
				{
					$group->updateLeaderUser($user, 1);
				}
			}
			if ($request->getParam('deleteid', 0) != 0) {
				$user = $table->fetchRow(array("users_id = ?" => $request->getParam('deleteid')));
				if ($user === null) {
					throw new USVN_Exception(sprintf(T_("Unknown user %s"), $request->getParam('deleteid')));
				}
				if ($group->hasUser($user))
					$group->deleteUser($user);
			}		
			/*if (isset($user)) {
				$this->_redirect("/group/index/group/{$group->name}/");
			}*/
		}
		catch (Exception $e) {
			$this->view->message = $e->getMessage();
		}

	//	$this->view->project = $this->_project;
		$this->view->group = $group;
	}
}