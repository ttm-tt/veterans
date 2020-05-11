<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;
use Cake\Validation\Validator;

class ArticleVariantsTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
	
		$this->setTable('shop_article_variants');
		
		$this->belongsTo('Shop.Articles', ['foreign_key' => 'article_id']);
				
		$this->addBehavior('Translate', [
			'fields' => ['description'],
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
			->notBlank('variant_type')
		;
		
		return $validator;
	}
}
?>