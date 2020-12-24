<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Datasource\EntityInterface;

class OrderArticlesTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
	
		$this->setTable('shop_order_articles');
		
		$this->belongsTo('Shop.Orders', ['foreignKey' => 'order_id']);
		$this->belongsTo('Shop.Articles', ['foreignKey' => 'article_id']);
		$this->belongsTo('Shop.ArticleVariants', ['foreignKey' => 'article_variant_id']);
		
		$this->belongsTo('People', ['foreignKey' => 'person_id']);
		
		$this->hasMany('Shop.OrderArticleHistories', ['foreignKey' => 'order_article_id']);
		
		// We need to catch Person.beforeDelete, so we can stop if order articles are still referenced
		TableRegistry::get('People')
				->getEventManager()
				->on('Person.deleteRule', function(\Cake\Event\EventInterface $event) {
						if ($this->find()
								->where(['person_id' => $event->getSubject()->id])
								->count()) {
							$event->stopPropagation();
							$event->getSubject()->setError('person_id', __('Person has orders and therefore cannot be deleted'));
							
							// No result, it is in the entity
							return null;
						}
					}
				);
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
	// In contrast to other history this is calculated backwards:
	// which means we are never storing the original record but only changes.
	// To calculate the state of an article in the past we start with the 
	// current set and apply the history backwards.
	// We can do this because records are never deleted, except the order
	// itself is deleted.
	public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::afterSave($event, $entity, $options);
		
		$oldData = $this->oldData;

		$newData = $this->get($entity->id)->toArray();

		$histories = array();

		$uid = (new Session())->read('Auth.User.id');

		if (empty($oldData)) {
			$h = array();
			$h['order_article_id'] = $newData['id'];
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
				$h['order_article_id'] = $newData['id'];
				$h['user_id'] = $uid;
				$h['field_name'] = $k;
				$h['old_value'] = $oldData[$k];
				$h['new_value'] = $newData[$k];
				$h['created'] = $newData['modified'];

				$histories[] = $h;
			}
		}

		if (!empty($histories)) {
			$orderArticleHistories = TableRegistry::get('Shop.OrderArticleHistories');
			$orderArticleHistories->saveMany($orderArticleHistories->newEntities($histories));
		}

		$this->oldData = false;
	}
	
}
?>
