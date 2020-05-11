<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Datasource\EntityInterface;


class OrdersTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('shop_orders');
		
		$this->hasOne('InvoiceAddresses', [
			'className' => 'Shop.OrderAddresses',
			'foreignKey' => 'order_id',
			'conditions' => ['InvoiceAddresses.type' => 'P']
		]);
		
		$this->hasOne('ShipmentAddresses', [
			'className' => 'Shop.OrderAddresses',
			'foreignKey' => 'order_id',
			'conditions' => ['ShipAddresses.type' => 'S']
		]);
		
		$this->hasMany('Shop.OrderArticles', ['foreignKey' => 'order_id']);
		
		$this->hasMany('Shop.OrderPayments', ['foreignKey' => 'order_id']);
		$this->hasMany('Shop.OrderComments', ['foreignKey' => 'order_id']);
		$this->hasMany('Shop.OrderHistories', ['foreignKey' => 'order_id']);


		$this->belongsTo('Users');
		$this->belongsTo('Shop.OrderStatus', ['foreignKey' => 'order_status_id']);
	}
	
	private $oldData;

	// Remember old values
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::beforeSave($event, $entity, $options);
		
		if ($entity->isNew())
			$this->oldData = null;
		else 
			$this->oldData = $this->get($entity->id)->toArray();
	}

	// Save history
	public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::afterSave($event, $entity, $options);
		
		$oldData = $this->oldData;

		$newData = $this->get($entity->id)->toArray();

		$histories = array();

		$uid = (new Session())->read('Auth.User.id');

		if (empty($oldData)) {
			$h = array();
			$h['order_id'] = $newData['id'];
			$h['user_id'] = $uid;
			$h['field_name'] = 'created';
			$h['old_value'] = null;
			$h['new_value'] = serialize($newData);
			$h['created'] = $newData['modified'];

			$histories[] = $h;
		} else {
			foreach ($newData as $k => $v) {
				if ($oldData[$k] == $newData[$k])
					continue;

				if ($k == 'created')
					continue;
				if ($k == 'modified')
					continue;

				$h = array();
				$h['order_id'] = $newData['id'];
				$h['user_id'] = $uid;
				$h['field_name'] = $k;
				$h['old_value'] = $oldData[$k];
				$h['new_value'] = $newData[$k];
				$h['created'] = $newData['modified'];

				$histories[] = $h;
			}
		}

		if (!empty($histories)) {
			$orderHistories = TableRegistry::get('Shop.OrderHistories');
			$orderHistories->saveMany($orderHistories->newEntities($histories));
		}

		$this->oldData = false;
	}
}
?>
