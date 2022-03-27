<?php
return [
// Shop settings
	'Shop' => [
		/*
		 * Currency converter
		 */
		'CurrencyConverter' => [
			'engine' => 'openexchangerates',
			'key' => '',
			'currency' => 'USD',
		],
		/*
		 * Payment service providers
		 */
		'PaymentProviders' => [
			/*
			 * Dummy payment
			 */
			'Dummy' => [
				'engine' => 'DummyPayment',
				'endpoint' => false
			],
			/*
			 * Ipayment demo settings
			 */

			'Ipayment' => [
				'accountData' => array(
					'accountId' => 99999,
					'trxuserId' => 99999,
					'trxpassword' => 0,
					'adminactionpassword' => '5cfgRT34xsdedtFLdfHxj7tfwx24fe'
				),
				'engine' => 'Ipayment',
				'endpoint' => 'https://ipayment.de/merchant/99999/processor.php'
			],
			/*
			 * Authorize.net
			 */
			'AuthorizeNet' => [
				'accountData' => array(
					'loginId' => '6pg6w2BQV3Tx',
					'transactionKey' => '922c25GRdS5gBfag'
				),
				'engine' => 'AuthorizePayment',
				'endpoint' => 'https://test.authorize.net/gateway/transact.dll'
			],
			/*
			 * Redsys
			 */
			'Redsys' => [
				'accountData' => array(
					'code' => '999008881',
					'key' => 'qwertyasdf0123456789',
					'terminal' => '4',
				), 
				'engine' => 'RedsysPayment',
				'endpoint' => 'https://sis-d.redsys.es/sis/realizarPago',
				'allowRefund' => true
			],
			/*
			 * Paypal
			 */
			'PaypalExpress' => [
				'accountData' => array(
					'username' => 'theis_api1.ttm.co.at',
					'password' => 'XM4V4YJZGWZ9U5US',
					'signature' => 'A2uH1mbeoNx40NjMdWTqbfDHo-w4AgMlu-H6wi9cQ51l4Qhbw-UmKGq8'
				),
				'api_version' => '124',
				'endpoint' => 'https://api-3t.sandbox.paypal.com/nvp', // Sandbox 
				'engine' => 'PaypalExpressPayment',
				'allowRefund' => true
			],
			/*
			 * Dibs D2
			 */
			'Dibs' => [
				'accountData' => array(
					'merchant' => 90218572,
					'password' => 'Kaszxe51',
					'k1' => '[tUdr@CUby,vXbld4RFE~2YM!(Tn.5NG',
					'k2' => 'Ozi#_lp,WizYALqaX:?8o+ilgen3,rqB'
				),
				'endpoint' => 'https://payment.architrade.com/paymentweb/start.action',
				'refundEndpoint' => 'https://payment.architrade.com/cgi-adm/refund.cgi',
				'engine' => 'DibsPayment',
				'allowRefund' => true
			],
			/*
			 * CardPointe HPP
			 */
			'CardPointe' => [
				'endpoint' => 'https://wvc2018.securepayments.cardpointe.com/pay',
				'engine' => 'CardPointePayment',
				'allowRefund' => false
			],
			
			/*
			 * B-Payment
			 */
			'BPayment' => [
				'endpoint' => 'https://test.borgun.is/securepay/default.aspx',
				'engine' => 'BPayment',
				'allowRefund' => false,
				'accountData' => [
					'merchantid' => 9275444,
					'paymentgatewayid' => 16,
					'secretkey' => 99887766
				]
			],
			
			/*
			 * Societe Generale eCommerce
			 */
			'SogEcommerce' => [
				'endpoint' => 'https://sogecommerce.societegenerale.eu/vads-payment/',
				'engine' => 'SogEcommerce',
				'ctx_mode' => 'TEST', // or PRODUCTION
				'currency' => 978,    // EUR
				'allowRefund' => false,
				'accountData' => [
					'site_id' => 90040519,
					'key' => 'xZUttNhW8zI1IROS',
				]
			],
			
			/*
			 * Stripe
			 */
			'Stripe' => [
				'engine'=> 'StripePayment',
				'accountData' => [
					// TEST keys
					'webhook_key' => 'whsec_DbUWwCg4uji3SJ72oC07VNRLFQ4Xo1GR',
					'public_key' => 'pk_test_jgaiILCKPXznXFbOKLCwtqqt00oxLP1zgV',
					'secret_key' => 'sk_test_4yhY91fNn5b2SYKNAcOof42B001yLH0r3A'
				]
			],
			
			/*
			 * BNL POSitivity
			 */
			'Positivity' => [
				// We need a separate setting for testUrl and test mode
				'test' => true, // In production change to false
				'endpoint' => 'https://pftest.bnlpositivity.it/service/',
				'engine' => 'PositivityPayment',
				'accountData' => [
					// TEST data
					'storeId' => '08000001',
					'kSig' => 'xHosiSb08fs8BQmt9Yhq3Ub99E8='
				]
			],
			
			/*
			 * SmartPay
			 */
			'SmartPay' => [
				'engine' => 'SmartPayPayment',
				'endpoint' => 'https://mti.bankmuscat.com:6443/transaction.do?command=initiateTransaction',
				'accountData' => [
					'merchant_id' => 0,
					'access_code' => '',
					'encryption_key' => ''					
				]
			]
		],
		
		// Selected engine
		'payment' => '',
		
		'allowRefund' => true,
		
		// Use test URL
		'testUrl' => false
	],
];
