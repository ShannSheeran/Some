<?php

namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class InvitationCodeTable extends PublicTable
{
	public function __construct(Adapter $adapter)
	{
		$this->table = DB_PREFIX . "invitation_code";
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->initialize();
	}
}