<!-- PayPal JS SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_SANDBOX?PAYPAL_SANDBOX_CLIENT_ID:PAYPAL_PROD_CLIENT_ID; ?>&currency=<?php echo $currency; ?>"></script>
</head>
<body>
<div class="container">
	<h1>PayPal Standard Checkout Integration</h1>
	<div class="panel">
        <div class="overlay hidden"><div class="overlay-content"><img src="css/loading.gif" alt="Processing..."/></div></div>

		<div class="panel-heading">
			<h3 class="panel-title">Charge <?php echo '$'.$itemPrice; ?> with PayPal</h3>
			
			<!-- Product Info -->
			<p><b>Item Name:</b> <?php echo $itemName; ?></p>
			<p><b>Price:</b> <?php echo '$'.$itemPrice.' '.$currency; ?></p>
		</div>
		<div class="panel-body">
			<!-- Display status message -->
			<div id="paymentResponse" class="hidden"></div>
			
			<!-- Set up a container element for the button -->
			<div id="paypal-button-container"></div>
		</div>
	</div>
</div>

<script>
paypal.Buttons({
    // Sets up the transaction when a payment button is clicked
    createOrder: (data, actions) => {
        return actions.order.create({
            "purchase_units": [{
                "custom_id": "<?php echo $itemNumber; ?>",
                "description": "<?php echo $itemName; ?>",
                "amount": {
                    "currency_code": "<?php echo $currency; ?>",
                    "value": <?php echo $itemPrice; ?>,
                    "breakdown": {
                        "item_total": {  /* Required when including the items array */
                            "currency_code": "<?php echo $currency; ?>",
                            "value": <?php echo $itemPrice; ?>
                        }
                    }
                },
                "items": [
					{
						"name": "<?php echo $itemName; ?>", /* Shows within upper-right dropdown during payment approval */
						"description": "<?php echo $itemName; ?>", /* Item details will also be in the completed paypal.com transaction view */
						"unit_amount": {
							"currency_code": "<?php echo $currency; ?>",
							"value": <?php echo $itemPrice; ?>
						},
						"quantity": "1",
						"category": "DIGITAL_GOODS"
					},
                ]
            }]
        });
    },
    // Finalize the transaction after payer approval
    onApprove: (data, actions) => {
        return actions.order.capture().then(function(orderData) { //console.log(orderData);
            // Successful capture! For dev/demo purposes:
            //console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));
            //const transaction = orderData.purchase_units[0].payments.captures[0]; //console.log(transaction);
            //alert(`Transaction ${transaction.status}: ${transaction.id}\n\nSee console for all available details`);
            // When ready to go live, remove the alert and show a success message within this page. For example:
            // const element = document.getElementById('paypal-button-container');
            // element.innerHTML = '<h3>Thank you for your payment!</h3>';
            // Or go to another URL:  actions.redirect('thank_you.html');

            setProcessing(true);

            var postData = {paypal_order_check: 1, order_id: orderData.id};
            fetch('paypal_checkout_validate.php', {
                method: 'POST',
                headers: {'Accept': 'application/json'},
                body: encodeFormData(postData)
            })
            .then((response) => response.json())
            .then((result) => {
                if(result.status == 1){
                    window.location.href = "payment-status.php?checkout_ref_id="+result.ref_id;
                }else{
					const messageContainer = document.querySelector("#paymentResponse");
					messageContainer.classList.remove("hidden");
					messageContainer.textContent = result.msg;
					
					setTimeout(function () {
						messageContainer.classList.add("hidden");
						messageText.textContent = "";
					}, 5000);
                }
                setProcessing(false);
            })
            .catch(error => console.log(error));
        });
    }
}).render('#paypal-button-container');

const encodeFormData = (data) => {
  var form_data = new FormData();

  for ( var key in data ) {
    form_data.append(key, data[key]);
  }
  return form_data;   
}

// Show a loader on payment form processing
const setProcessing = (isProcessing) => {
	if (isProcessing) {
		document.querySelector(".overlay").classList.remove("hidden");
	} else {
		document.querySelector(".overlay").classList.add("hidden");
	}
}
</script>
