<?php
namespace Awz\Pull\Access\Permission;

use Awz\Pull\Access\Tables;
use Awz\Pull\Access\Custom;

class RoleUtil extends \Bitrix\Main\Access\Role\RoleUtil
{
	protected static function getRoleTableClass(): string
	{
		return Tables\RoleTable::class;
	}

	protected static function getRoleRelationTableClass(): string
	{
		return Tables\RoleRelationTable::class;
	}

	protected static function getPermissionTableClass(): string
	{
		return Tables\PermissionTable::class;
	}

	protected static function getRoleDictionaryClass(): ?string
	{
		return Custom\RoleDictionary::class;
	}

}