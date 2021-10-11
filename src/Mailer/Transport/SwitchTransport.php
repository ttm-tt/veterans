<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Mailer\Transport;

use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message;
use Cake\Mailer\TransportFactory;


class SwitchTransport extends AbstractTransport {
	private $_transports = array();
	private $_defaultTransport = null;
	
	public function __construct(array $config = []) {
		parent::__construct($config);
		
		foreach (array_keys($config['transports'] ?? array()) as $transport) {
			$this->_transports[$transport] = TransportFactory::get($transport);
		}
		
		$this->_defaultTransport = TransportFactory::get('default');
	}
	
	public function send(Message $message): array {
		$addressees = array_merge($message->getFrom(), $message->getTo(), $message->getCc(), $message->getBcc());
		
		foreach (array_keys($this->_config['transports']) as $transport) {
			$tmp = array_filter($addressees, function($address) use($transport) {
				foreach(($this->_config['transports'][$transport]['filterAddress'] ?? array()) as $a) {
					return strpos(strtolower($address), strtolower($a)) !== true;
							
				}
			});
			
			if (count($tmp)) {
				return $this->_transports[$transport]->send($message);
			}
		}
		
		return $this->_defaultTransport->send($message);
	}

}
