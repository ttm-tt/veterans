<?php

namespace App\Test\Mock;

use Acl\AclInterface;
use Cake\ORM\Entity;

class TestAcl implements AclInterface {
	public function allow($aro, $aco, $action = "*"): bool {
		return true;
	}

	public function check($aro, $aco, $action = "*"): bool {
		if ($aro instanceof Entity)
			$enabled = $aro->enabled;
		else if (is_array($aro) && isset($aro['Users']))
			$enabled = $aro['Users']['enabled'] ?? true;
		else
			return false;
		
		return $enabled;
	}

	public function deny($aro, $aco, $action = "*"): bool {
		return true;
	}

	public function inherit($aro, $aco, $action = "*"): bool {
		return true;
	}

	public function initialize(\Cake\Controller\Component $component): void {
		
	}

}
