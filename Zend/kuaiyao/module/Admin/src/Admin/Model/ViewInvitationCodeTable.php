<?php

namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class ViewInvitationCodeTable extends PublicTable
{
	public function __construct(Adapter $adapter)
	{
		$this->table = "view_invitation_code";
		$this->adapter = $adapter;
		$this->resultSetPrototype = new ResultSet();
		$this->initialize();
	}
}