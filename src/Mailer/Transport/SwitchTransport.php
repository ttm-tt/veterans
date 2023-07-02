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
			$ct = time();
			file_put_contents(TMP . '/sendinblue/' . date('Ymd-His', $ct), 
					print_r('Checking ' . count($addressees) . "addresses", true));
			$tmp = array_filter($addressees, function($address) use($transport) {
				foreach(($this->_config['transports'][$transport]['filterAddress'] ?? array()) as $fa) {
					$ct = time();
					$b = strpos(strtolower($address), strtolower($fa));
					file_put_contents(TMP . '/sendinblue/' . date('Ymd-His', $ct), 
						print_r('Checking ' . $address . ' against ' . $fa .  ' is ' . $b, true));
					
					return  $b !== true;
				}
			});
			
			if (count($tmp)) {
				$ret = $this->_transports[$transport]->send($message);
				file_put_contents(TMP . '/sendinblue/' . date('Ymd-His', $ct), 
						print_r(['addresses' => $tmp, 'ret' => $ret], true));
				return $ret;
			}
		}
		
		return $this->_defaultTransport->send($message);
	}

}
