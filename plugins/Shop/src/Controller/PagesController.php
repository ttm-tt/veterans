<?php

namespace Shop\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;

class PagesController extends ShopAppController {
	public function beforeFilter(EventInterface $event) {
		$this->Auth->allOW();
		
		parent::beforeFilter($event);
	}
	
	public function shop_agb() {
		if ($this->request->getSession()->started()) {
			$language = $this->request->getSession()->read('Config.language');
		}
		
		if (empty($language)) {
			$language = Configure::read('Config.language');
		}
		
		if ($language && file_exists(APP . 'Plugin' . DS . 'Shop' .DS . 'View' . 'Pages' . DS . 'shop_agb_' . $language . 'ctp'))
			$this->render('shop_agb_' . $language);
		else
			$this->render('shop_agb');
	}
	
	public function players_privacy() {
		if ($this->request->getSession()->started()) {
			$language = $this->request->getSession()->read('Config.language');
		}
		
		if (empty($language)) {
			$language = Configure::read('Config.language');
		}
		
		if ($language && file_exists(APP . 'Plugin' . DS . 'Shop' .DS . 'View' . 'Pages' . DS . 'players_agb_' . $language . 'ctp'))
			$this->render('players_privacy_' . $language);		
		else
			$this->render('players_privacy');
	}
}
