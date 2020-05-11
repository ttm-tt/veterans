<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

class OrderArticleHistoriesTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
	
		$this->setTable('shop_order_article_histories');
	
		$this->belongsTo('Shop.OrderArticles', ['foreignKey' => 'order_article_id']);
		$this->belongsTo('Users');
	}
}
?>
