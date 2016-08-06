<?php
/* ******************************************************** _ *** _ ******** */
/*                                                   ______//_____\\______   */
/*   WhiteCanvas 2016                               |                     |  */
/*                                                  |                     |  */
/*   Created by Gabriel Marais                      |                     |  */
/*                                                  |                     |  */
/*                                                  |_____.____.______W_C_|  */
/*   https://github.com/gmarais                     |_____________________|  */
/*                                                  //         ||        \\  */
/* *********************************************** // ******************* \\ */

class DbRevision
{
	static private function executeDowns($previous_revisions, $current_revisions)
	{
		foreach ($previous_revisions as $past_update)
		{
			foreach ($current_revisions as $update)
			{
				if ($past_update['ref'] == $update['ref'])
					break;
			}
			// if not found in revision array then execute the down
			if ($past_update['ref'] != $update['ref']) {
				$downs = json_decode($past_update['down']);
				if ($downs && is_array($downs)) {
					foreach ($downs as $down)
					{
						Db::getInstance()->execute($down);
						$error = Db::getInstance()->popLastError();
						if ($error)
							echo "<b>$error</b><br />";
					}
					$query = new DbQuery();
					$query->deleteFrom('revision');
					$query->where('id_revision', '=', $past_update['id_revision']);
					Db::getInstance()->execute($query->getSyntax(), $query->getData());
				}
			}
		}
	}

	static private function executeUps($current_revisions)
	{
		foreach ($current_revisions as $update)
		{
			$query = new DbQuery();
			$query->select('r.id_revision');
			$query->from('revision', 'r');
			$query->where('r.ref', '=', $update['ref']);
			$check = Db::getInstance()->getRow($query->getSyntax(), $query->getData());
			if (!$check) {
				foreach ($update['up'] as $sql)
				{
					Db::getInstance()->execute($sql);
					$error = Db::getInstance()->popLastError();
					if ($error)
						echo "<b>$error</b><br />";
				}
				$query = new DbQuery();
				$query->insertInto('revision', '`ref`, `down`');
				$query->values($update['ref']);
				$query->values(Tools::escape(json_encode($update['down'])));
				Db::getInstance()->execute($query->getSyntax(), $query->getData());
			}
		}
	}

	static public function processRevision()
	{
		$query = new DbQuery();
		$query->select('ref');
		$query->select('down');
		$query->select('id_revision');
		$query->from('revision');
		$previous_revisions = Db::getInstance()->getRows($query->getSyntax(), $query->getData());
		if (!$previous_revisions)
			$previous_revisions = array();
		$current_revisions = json_decode(preg_replace('/\{\$db_prefix\}/', _DB_PREFIX_, file_get_contents(_CONFIG_DIR_.'/json/dbrevision.json')), true);
		if (!$current_revisions || !is_array($current_revisions))
			return;
		if (count($previous_revisions) == count($current_revisions))
			return;
		self::executeDowns($previous_revisions, $current_revisions);
		self::executeUps($current_revisions);
	}
}
