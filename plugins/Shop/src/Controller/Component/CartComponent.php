<?php
/*
 * Cart = array(
		'People' => array(
			[0] => person
			...
		),
		'Items' => array(
			[0] => item
			...
		)
	)
 */
namespace Shop\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;

class CartComponent extends Component {
	public $components = array('Wizard.Wizard');

	private $cart = null;

	// Called after Controller::beforeFilter.
	// Thus, session variable Tournament.id should be set
	public function startup(Event $event) {
		$this->cart = $this->Wizard->read('cart');
		if ($this->cart === null) {
			$this->cart = array(
				'People' => array(),
				'Items' => array(),
				'Address' => array(),
				'PaymentMethod' => null,
				'order_id' => null
			);

			$this->Wizard->save('cart', $this->cart);
		}
	}


	// Called after render
	public function afterFilter(Event $event) {
		$this->Wizard->save('cart', $this->cart);
	}
	
	
	// Clear shopping cart
	public function clear() {
		$this->Wizard->delete('cart');
		
		// Reinitialize cart
		$this->cart = array(
			'People' => array(),
			'Items' => array(),
			'Address' => array(),
			'PaymentMethod' => null,
			'order_id' => null
		);
	}


	public function getPeople() {
		return $this->cart['People'];
	}


	public function addPerson($person) {
		$this->getController()->loadModel('Shop.Articles');
		$this->getController()->loadModel('Shop.ArticleVariants');

		$article = $this->getController()->Articles->find('all', array(
			'conditions' => array(
				'tournament_id' => $this->getController()->getRequest()->getSession()->read('Tournaments.id'),
				'name' => $person['type']
			)
		))->first();

		if (empty($article))
			return;
		
		if (!empty($person['variant_id'])) {			
			$variant = $this->getController()->ArticleVariants->find('all', array(
				'conditions' => array('ArticleVariants.id' => $person['variant_id'])
			))->first();
			
			if (!empty($variant)) {
				$article['article_variants'] = array(
					$variant
				);
			}
		}

		$this->cart['People'][] = $person;

		$this->addArticle($article->toArray());
	}


	public function removePerson($idx) {
		if ($idx >= count($this->cart['People']))
			return;
		
		$this->getController()->loadModel('Shop.Articles');

		$article = $this->getController()->Articles->find('all', array(
			'conditions' => array(
				'tournament_id' => $this->getController()->getRequest()->getSession()->read('Tournaments.id'),
				'name' => $this->cart['People'][$idx]['type']
			)
		))->first();

		// Update people details
		unset($this->cart['People'][$idx]);
		$this->cart['People'] = array_values($this->cart['People']);
		
		$key = $article['id'];
		
		$this->removeArticle($key);
	}
	
	
	public function getItems() {
		return $this->cart['Items'];
	}


	public function addArticle($newArticle) {
		// key is a combination of article id and options
		$article_id = $newArticle['id'];
		$variant_id = null;

		$key = '' . $newArticle['id'];
		$description = $newArticle['description'];

		if (!empty($newArticle['article_variants'])) {
			$key .= 'v' . $newArticle['article_variants'][0]['id'];
			$variant_id = $newArticle['article_variants'][0]['id'];
			$description .= ', ' . $newArticle['article_variants'][0]['description'];
		}

		if (!isset($this->cart['Items'][$key])) {
			$price = $newArticle['price'];
			
			if (!empty($variant_id)) {
				$this->getController()->loadModel('Shop.ArticleVariants');
				$variant = $this->getController()->ArticleVariants->find('all', array(
					'conditions' => array('ArticleVariants.id' => $variant_id)
				))->first();
				
				if (!empty($variant)) {
					$price += $variant['price'];
				}
			}
			
			$this->cart['Items'][$key] = 
				array(
					'article_id' => $article_id,
					'article_variant_id' => $variant_id,
					'key' => $key,
					'quantity' => 0,
					'description' => $description,
					'price' => $price,
					'total' => 0,
					'cancellation_fee' => 0
				) + $newArticle
			;
		}

		$this->setQuantity($key, $this->cart['Items'][$key]['quantity'] + 1);
	}


	public function removeArticle($key) {
		if (!isset($this->cart['Items'][$key]))
			return;
		
		$quantity = $this->cart['Items'][$key]['quantity'];
		
		if ($quantity == 1) {
			unset($this->cart['Items'][$key]);
		} else {
			$this->setQuantity($key, $quantity - 1);
		}

		$this->Wizard->save('cart', $this->cart);
	}


	public function setQuantity($key, $quantity) {
		if (isset($this->cart['Items'][$key])) {
			$this->cart['Items'][$key]['quantity'] = $quantity;

			$this->_calculatePrice($key);

			$this->Wizard->save('cart', $this->cart);
		}
	}


	private function _calculatePrice($key) {
		$quantity = $this->cart['Items'][$key]['quantity'];

		$this->cart['Items'][$key]['total'] = $this->cart['Items'][$key]['price'] * $quantity;
	}


	public function setAddress($address) {
		$this->cart['Address'] = $address;

		$this->Wizard->save('cart', $this->cart);
	}


	public function getAddress() {
		return $this->cart['Address'];
	}


	public function getTotal() {
		$total = 0;

		foreach ($this->cart['Items'] as $item) {
			$total += $item['total'];
		}

		return $total;
	}
	
	public function setPaymentMethod($method) {
		$this->cart['PaymentMethod'] = $method;
		
		$this->Wizard->save('cart', $this->cart);
	}
	
	public function getPaymentMethod() {
		return $this->cart['PaymentMethod'];
	}
	
	public function getOrderId() {
		return $this->cart['order_id'];
	}
	
	public function setOrderId($id = null) {
		$this->cart['order_id'] = $id;
	}
}
