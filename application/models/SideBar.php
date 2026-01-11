<?php

class Model_SideBar
{
	public function getMenuPagesByUserId($id)
	{
		$db = Zend_Registry::get('dbAdapter');

		// 1. carico i moduli dal db
		$modules = $db->fetchAll('select * from menu_pages order by _index');

		$pages = array();

		foreach($modules as $page)
		{
			if($page['parent_page'] === null)
			{
				$pages[$page['id']] = $page;
			}
			else
			{
				$pages[$page['parent_page']]['pages'][] = $page;
			}
		}

		return $pages;
	}
}
