<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

class ArticlesTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
	
		$this->setTable('shop_articles');
		
		$this->belongsTo('Tournaments');
		$this->hasMany('Shop.ArticleVariants', [
			'foreignKey' => 'article_id',
			'order' => ['ArticleVariants.sort_order' => 'ASC']
		]);
		$this->hasMany('Shop.OrderArticles', [
			'foreignKey' => 'article_id'
		]);
		$this->hasMany('Shop.Allotments', [
			'foreignKey'=> 'article_id'
		]);
		
		$this->addBehavior('Translate', [
			'fields' => ['description', 'article_description'],
			'allowEmptyTranslations' => false,
			'defaultLocale' => 'en'
		]);
	}
}
?>
