/**
 * JavaScript helper for the transaction confirmation logic.
 */

document.addEventListener('DOMContentLoaded', function() {
	
	var form = document.getElementById('confirmForm');
	form.RublonConfirmationCallback = function(message) {
		RublonSDK.closeConfirmation();
		// Don't use document.body.innerHTML since it's reloading the iframe with disposable token!
		var msg = document.createElement('p');
		msg.setAttribute('class', 'transactionResult');
		msg.appendChild(document.createTextNode(message));
		document.body.appendChild(msg);
		form.parentNode.removeChild(form);
	};
	
});
