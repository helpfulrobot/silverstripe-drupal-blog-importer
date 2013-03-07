<?php
class DrupalBlogUserBulkLoader extends CsvBulkLoader {

	public $columnMap = array(
		'uid' => 'DrupalUid', // requires DrupalMemberExtension
		'title' => 'Nickname', // requires DrupalMemberExtension
		'mail' => 'Email',
		'name' => '->importName',
		'created' => 'Created',
		'changed' => 'LastEdited',
	);

	public $duplicateChecks = array(
		'DrupalUid' => array(
			'callback' => 'findDuplicateByUid'
		),
		'Nickname' => array(
			'callback' => 'findDuplicateByTitle'
		)
	);
	
	public function __construct($objectClass = 'Member') {
		parent::__construct($objectClass);

		$canMapUid = (
			isset($this->columnMap['uid']) 
			&& singleton($objectClass)->hasDatabaseField($this->columnMap['uid'])
		);
		$canMapTitle = (
			isset($this->columnMap['title']) 
			&& singleton($objectClass)->hasDatabaseField($this->columnMap['title'])
		);
		if(!$canMapUid && !$canMapTitle) {
			throw new LogicException(sprintf(
				'The user importer requires a unique identifier field for "uid" or "title" ' .
				'(expected "%s" or "%s")',
				$this->columnMap['uid'],
				$this->columnMap['title']
			));
		}
	}

	protected function importName($obj, $val, $record) {
		$parts = preg_split('/\s/', $val, 2);
		$obj->FirstName = $parts[0];
		if(isset($parts[1])) $obj->Surname = $parts[1];
	}

	protected function findDuplicateByUid($uid, $record) {
		// Lookup is optional, fall back to title
		if(!singleton('Member')->hasDatabaseField($this->columnMap['uid'])) return;

		return Member::get()->filter($this->columnMap['uid'], $uid)->First();
	}

	protected function findDuplicateByTitle($title, $record) {
		// Lookup is optional, fall back to uid
		if(!singleton('Member')->hasDatabaseField($this->columnMap['title'])) return;

		return Member::get()->filter($this->columnMap['title'], $title)->First();
	}
	
}