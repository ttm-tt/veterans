<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;
use Cake\Validation\Validator;

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
	
	public function validationDefault(Validator $validator) : Validator {
		
		// Validate combination last_name and first_name is unique
		// TODO: Better put that into a application rule
		$validator
			->notBlank('name')
			->notBlank('description')
		;
		
		return $validator;
	}
}
?>
