<?php
namespace Shop\Controller;

use App\Model\Table\GroupsTable;


class AllotmentsController extends ShopAppController {
	public function index() {
		$tid = $this->request->getSession()->read('Tournaments.id');

		$this->loadModel('Shop.OrderArticles');
		
		$query = $this->OrderArticles->find();
		
		$this->paginate = array(
			'contain' => ['Articles', 'Users'],
			'conditions' => ['Articles.tournament_id' => $tid],
			'order' => ['Articles.sort_order' => 'DESC'],
			'fields' => [
				'Articles.description',
				'Users.username',
				'Allotments.allotment',
				'Allotments.modified',
				'Allotments.id',
				'count' => $query
						->contain(['Orders'])
						->select(['count' => 'SUM(quantity)'])
						->where([
							'cancelled IS NULL',
							'Allotments.article_id = OrderArticles.article_id',
							'Allotments.user_id = Orders.user_id'
						])
			],
			'sortWhitelist' => [
				'Articles.description',
				'Users.username',
				'Allotments.allotment',
				'Allotments.modified',
				'count'
			]
		);
		
		$allotments = $this->paginate();
		
		$this->set('allotments', $allotments);
	}		
	
	public function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid allotment'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$this->set('allotment', $this->Allotments->get($id, array(
			'contain' => ['Articles', 'Users']
		)));
	}
	
	
	public function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$allotment = $this->Allotments->newEmptyEntity();
		
		if ($this->request->is(['put', 'post'])) {
			$allotment = $this->Allotments->patchEntity($allotment, $this->request->getData());
			
			if ($this->Allotments->save($allotment)) {
				$this->MultipleFlash->setFlash(__('The allotment has been saved'), 'success');
				return $this->redirect(['action' => 'index']);
			}
			
			$this->MultipleFlash->setFlash(__('The allotment could not be saved. Please try again'), 'error');
		}
		
		$this->set('allotment', $allotment);
		
		
		$this->loadModel('Shop.Articles');
		$this->loadModel('Users');
		
		$this->set('articles', $this->Articles->find('list', array(
			'fields' => ['id', 'description'],
			'conditions' => [
				'tournament_id' => $tid,
				'available > 0'
			],
			'order' => ['description' => 'ASC']
		))->toArray());
		
		$this->set('users', $this->Users->find('list', array(
			'fields' => ['id', 'username'],
			'conditions' => ['group_id' => GroupsTable::getTourOperatorId()],
			'order' => ['username' => 'ASC']
		))->toArray());
		
	}
	
	
	public function edit($id = null) {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$id ) {
			$this->MultipleFlash->setFlash(__('Invalid allotment'), 'error');
			return $this->redirect(array('action' => 'index'));
		} 
		
		$allotment = $this->Allotments->get($id, array(
			'contain' => ['Articles', 'Users']
		));
		
		if ($this->request->is(['put', 'post'])) {
			$allotment = $this->Allotments->patchEntity($allotment, $this->request->getData());
			
			if ($this->Allotments->save($allotment)) {
				$this->MultipleFlash->setFlash(__('The allotment has been saved'), 'success');
				return $this->redirect(['action' => 'index']);
			}
			
			$this->MultipleFlash->setFlash(__('The allotment could not be saved. Please try again'), 'error');			
		}
		
		$this->set('allotment', $allotment);
	}
	
	
	public function delete($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for allotment'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		$allotment = $this->Allotments->get($id);
		
		if ($this->Allotments->delete($allotment)) {
			$this->MultipleFlash->setFlash(__('Allotment deleted'), 'success');
			return $this->redirect(array('action'=>'index'));
		}
		$this->MultipleFlash->setFlash(__('Allotment was not deleted'), 'error');
		return $this->redirect(array('action' => 'index'));		
	}
}

