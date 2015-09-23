Rublon PHP SDK
==============


Table of Contents
-----------------

1. [Introduction](#intro)
	* [Use cases](#intro-use-cases)
	* [Principles of operation](#intro-how-it-works)
	* [First steps](#intro-first-steps)
	* [Examples' assumptions](#intro-examples)
	* [Modifying the library](#intro-mods)
2. [Library initialization](#initialize)
3. [Signing in](#auth)
	* [Example PHP code](#auth-example)
4. [Authentication finalization](#callback)
	* [Input params](#callback-input)
	* [Authentication verification](#callback-verification)
	* [Example PHP code](#callback-example)
5. [Secure transaction confirmation](#confirm)
	* [Initiating transaction confirmation](#confirm-init)
	* [Finalizing the transaction](#confirm-callback)
6. [Email2FA - simplified identity verification](#email2fa)
	* [Principles of operation](#email2fa-how-it-works)
	* [Example usage](#email2fa-config)
7. [Passwordless Login](#pslogin) 
8. [GUI](#gui)
	* [Example PHP code](#gui-example)
	* [Trusted Devices Widget](#gui-device-widget)
9. [Business level features](#business)
	* [Getting list of active features](#api-get-features)
	* [Confirmations](#confirm-feature)
	* [Force mobile app](#force-mobile-app)
	* [Ignore Trusted Device](#ignore-trusted-device)
	* [Buffered confirmation](#buffered-confirmation)
	* [Sharing access](#sharing-access)
	* [Remote logout](#remote-logout)
10. [Changelog](#changelog)




<a id="intro"></a>

Introduction
------------

The *Rublon PHP SDK* library is a client-side implementation of
the [Rublon](https://rublon.com) authentication service written in PHP,
including methods for embedding the service's GUI in a HTML-based environment.
It forms a convenient PHP coding language facade for the service's REST interface.

<a id="intro-use-cases"></a>

### Use cases

Rublon provides an additional secury layer:

1.	**during logging in to your system**, adding a second (or additional)
	authentication factor,
2.	**while conducting a security-sensitive transactions**,
	providing a user the means for identity confirmation before changing passwords
	or conducting a money transfer.

To be able to perform an additional authentication using Rublon,
the user must first be authenticated in a different way,
e.g. with a username and password.
It is a necessary step, because upon Rublon's initialization the service
must receive certain information about the user:

- a unique Id, stored in the system (
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- 
- inafter called
**the integrated system**) implementing the Rublon service,
- the user's email address.

To experience the full measure of two-factor authentication, the end-user
should install the Rublon mobile app, available on all leading smartphone
systems. However, having those with older phone devices in mind or those
who do not want to install any additional apps on their phones, we prepared
a Email2FA process which does not require using an additional device of any kind.

<a id="intro-how-it-works"></a>

### Principles of operation

#### User protection

User protection is active when a user's email address in the integrated system
can be matched to a user in the Rublon service.
For this purpose, the user's email is sent to Rublon servers.

1. If the email is matched to an existing Rublon account, the user's identity
can be confirmed using Rublon.
2. Otherwise, if the user does not possess a Rublon account (the email
could not be matched), Rublon will use the Email2FA process, trying to verify
the user's identity by sending a confirmation email message to his email address.

#### Identity confirmation

If the library finds an active user protection, a URL address pointing to Rublon
servers will be generated. The user's web browser must be then redirected
to that URL in order to carry out the identity confirmation.

If the web browser is the user's Trusted Device, the authentication will be
performed automatically and invisibly. Otherwise, the user will be asked
to scan a QR code using the Rublon mobile app or to click the verification
link sent to his email address, upon which the authentication will be performed.

#### Return to the integrated system

After a successful authentication, the web browser will be redirected to
a callback URL address which points to the integrated system servers.
The integrated system should intercept that URL, retrieve its params and finalize
the authentication using this library.

<a id="intro-first-steps"></a>

### First steps

To start using the Rublon PHP SDK library you should:

*	install the Rublon mobile app on your smartphone,
	create a new account and confirm your email address,
*	visit the Rublon [Developer Area](https://developers.rublon.com)
	at [developers.rublon.com](https://developers.rublon.com)
	and log in by clicking the "Developer Dashboard" button,
	and scanning the QR code that will appear using the Rublon mobile app,
*	go to the "Add website" form (Dashboard -&gt; Add website)
	and fill in the required fields,
*	copy the provided **system token** and **secret key**,
	which will be used to identify the integrated system and verify
	the authenticity and integrity of the messages exchanged with Rublon API.

<a id="intro-examples"></a>

### Examples' assumptions

In the following examples we assume the existence of the superglobal session
array `$_SESSION`, which has access to an object storing the currently logged
in user data.

<a id="intro-mods"></a>

### Modifying the library

The `Rublon2Factor` class implements a few public methods, which, when needed,
can be overriden with inheritance.

We strongly discourage you from modifying any part of the library, as it usually
leads to difficulties during future library updates. If you need to change the flow
or internal structure of the `Rublon2Factor`, `Rublon2FactorGUI` or `Rublon2FactorCallback`
classes, don't hesitate to subclass them according to your needs.


<a id="initialize"></a>

Library initialization
----------------------

To initialize the library you need to instantiate a `Rublon2Factor` class object.
Its constructor takes three arguments.

<table>
	<caption><code>Rublon2Factor</code> class constructor arguments</caption>
	<thead><tr>
		<th>Name</th>
		<th>Type</th>
		<th>Description</th>
	</tr></thead>
	<tbody>
		<tr><td><code>$systemToken</code></td><td>string</td><td>Your system's public Id</td></tr>
		<tr><td><code>$secretKey</code></td><td>string</td><td>Secret key</td></tr>
		<tr><td><code>$apiServer</code></td><td>string</td><td>(optional) API Server URI</td></tr>
	</tbody>
</table>

An example of the library's initialization in PHP: 

		require_once "libs/Rublon/Rublon2Factor.php";
		
		$rublon = new Rublon2Factor(
			$systemToken = "A69FC450848B4B94A040416DC4421523",
			$secretKey = "bLS6NDP7pGjg346S4IHqTHgQQjjSLw3CyApvz5iRjYzgIPN4e9EOi1cQJLrTlvLoHY8zeqg4ILrItYidKJ6JjEUZaA6pR1tZMwSZ"
		);

<a id="auth"></a>

Signing in
------------

Rublon protects users during their signing in processes. Even if a someone
lears the user's password with malicious intent, such a person would be unable
to log in to the user's account, because a physical access to the Rublon mobile app
(installed in the user's smartphone) or to his email account is needed.

<p class="notice">
Developer can force users to authenticate using the mobile app (to avoid
the Email2FA process) and/or ignore the Trusted Devices, which can
increase the security. These features available only to systems
integrated within the <em>Business plan</em>.
<a href="https://developers.rublon.com/98/Pricing">Check the pricing</a>.
</p>

Authenticating a user with the second factor should be initiated when the user
has successfully passed the first factor of authentication (e.g. the valid user
credentials have been provided) and the user's unique Id and email address are known.

The `Rublon2Factor::auth()` method will check the user's protection status (using
the email address) and return a URL address for the web browser to be redirected to
(if user protection is active) or `NULL` in case the user's protection is not active.

<table>
	<caption><code>Rublon2Factor::auth()</code> method arguments</caption>
	<thead><tr>
		<th>Name</th>
		<th>Type</th>
		<th>Description</th>
	</tr></thead>
	<tbody>
		<tr><td><code>$callbackUrl</code></td><td>string</td><td>The integrated system's callback URL</td></tr>
		<tr><td><code>$userId</code></td><td>string</td><td>The integrated system's user's unique Id which will allow to log in the user upon successful authentication</td></tr>
		<tr><td><code>$userEmail</code></td><td>string</td><td>The user's email address in the integrated system which will allow to check the user's protection status and match the user to a Rublon account</td></tr>
		<tr><td><code>$consumerParams</code></td><td>array</td><td>Additional transaction parameters (optional)</td></tr>
	</tbody>
</table>

<a id="auth-example"></a>

### Example PHP code

An example of logging in a user on an integrated system:

	/**
	 * An example method used to log the user in (integrated system's method)
	 */
	function login($login, $password) {
	
		if (loginPreListener()) {
			if ($user = authenticate($login, $password)) {
			
				// The user has been authenticated.
				$_SESSION["user"] = $user;
				loginPostListener();
				
			}
		}
		
	}
	
	
	/**
	 * Listener (hook) invoked after a successful first factor user authentication,
	 * implemented for Rublon integration purposes.
	 */
	function loginPostListener() {
		
		$rublon = new Rublon2Factor(
			$systemToken = "A69FC450848B4B94A040416DC4421523",
			$secretKey = "bLS6NDP7pGjg346S4IHqTHgQQjjSLw3CyApvz5iRjYzgIPN4e9EOi1cQJLrTlvLoHY8zeqg4ILrItYidKJ6JjEUZaA6pR1tZMwSZ"
		);
		
		try { // Initiate a Rublon authentication transaction
		
			$url = $rublon->auth(
				$callbackUrl = "http://example.com/rublon_callback.php",
				$_SESSION["user"]["id"], // User Id
				$_SESSION["user"]["email"] // User email
			);
			
			if (!empty($url)) { // User protection is active
			
				// Log the user out before checking the second factor:
				unset($_SESSION["user"]);
				
				// Redirect the user's web browser to Rublon servers
				// to verify the protection:
				header('Location: ' . $url);
				
			}
			
		} catch (RublonException $e) {
			// An error occurred
			unset($_SESSION["user"]);
			die("There was an error, please try again later.");
		}
		
		/* If we're here, the user's account is not protected by Rublon.
		The user can be authenticated. */
		return true;
	
	}

If the user's account is protected by Rublon, calling the `Rublon2Factor::auth()`
method will return a URL address pointing to Rublon servers, which the user's
browser should redirect to in order to verify a Trusted Device, display
a QR code to be scanned by the Rublon mobile app or verify identity by clicking
a confirmation link which has been sent to the user's email address.

<p class="notice">
Because the user's web browser will be redirected to Rublon servers in order
to confirm the user's identity, the user should be logged out
(if he/she was logged in before) to prevent creating a user session.
Otherwise Rublon will not protect the user effectively, because returning
to the integrated system before a proper Rublon authentication is performed
may grant the user access to an active logged in session in the system.
The user should be logged in only after a successful Rublon authentication.
</p>

<a id="callback"></a>

Authentication finalization
---------------------------

After a successful authentication Rublon will redirect the user's browser
to the callback URL. The callback flow continues the authentication process,
i.e. the finalization of the authentication (logging in or identity confirmation).

<a id="callback-input"></a>
### Input params

The callback URL will receive its input arguments in the URL address itself (*query string*).

<table>
	<caption>Callback URL arguments</caption>
	<thead><tr>
		<th>Name</th>
		<th>Type</th>
		<th>Description</th>
	</tr></thead>
	<tbody>
		<tr><td><code>state</code></td><td>string</td><td>Authentication result: <code>ok</code>, <code>error</code> or <code>cancel</code></td></tr>
		<tr><td><code>token</code></td><td>string</td><td>Access token (100 alphanumeric characters, upper- and lowercase), which allows authentication's verification using a background Rublon API connection</td></tr>
	</tbody>
</table>

<div class="block">
Notice: If the callback URL has been set to e.g. <code>http://example.com/twofactor/auth/</code>,
the params will be appended to the URL address:
<pre><code>http://example.com/twofactor/auth/?state=ok&token=Kmad4hAS...d</code></pre>
If your callback URL should be formed differently (e.g. when using mod_rewrite),
you can set the callback URL's template using the meta-tags:
<code>%token%</code> and <code>%state%</code>, like so:<br />
<pre><code>http://example.com/twofactor/auth/%state%/%token%</code></pre>
</div>

<a id="callback-verification"></a>
### Authentication verification

After the callback is invoked, for proper finalization of the authentication
process you need to instantiate a `Rublon2FactorCallback` class object.

<table>
	<caption><code>Rublon2FactorCallback</code> class constructor method arguments</caption>
	<thead><tr>
		<th>Name</th>
		<th>Type</th>
		<th>Description</th>
	</tr></thead>
	<tbody>
		<tr><td><code>$rublon</code></td><td>Rublon2Factor</td><td>An instance of the <code>Rublon2Factor</code> class.</td></tr>
	</tbody>
</table>

Next, the `Rublon2FactorCallback::call()` method should be called. It takes two arguments..

<table>
	<caption><code>Rublon2FactorCallback::call</code> method arguments</caption>
	<thead><tr>
		<th>Name</th>
		<th>Type</th>
		<th>Description</th>
	</tr></thead>
	<tbody>
		<tr><td><code>$successHandler</code></td><td>callable</td><td>Name of a function/method, or an anonymous function/closure, which will be invoked on successful verification of the process, finalizing the authentication (logging the user in or confirming the user's identity for some operation).</td></tr>
		<tr><td><code>$cancelHandler</code></td><td>callable</td><td>Name of a function/method, or an anonymous function/closure, which will be invoked on receiving the <code>cancel</code> state to cancel the authentication transaction.</td></tr>
	</tbody>
</table>

<table>
	<caption>Arguments of the <code>$successHandler</code> function, passed to the <code>Rublon2FactorCallback::call</code> method</caption>
	<thead><tr>
		<th>Name</th>
		<th>Type</th>
		<th>Description</th>
	</tr></thead>
	<tbody>
		<tr><td><code>$userId</code></td><td>string</td><td>The user's unique Id in the integrated system, given as an argument to the `Rublon2Factor::auth` method, whose authentication is being confirmed by Rublon</td></tr>
		<tr><td><code>$callback</code></td><td>Rublon2FactorCallback</td><td>An instance of the <code>Rublon2FactorCallback</code> class</td></tr>
	</tbody>
</table>

<table>
	<caption>Arguments of the <code>$cancelHandler</code> function, passed to the <code>Rublon2FactorCallback::call</code> method</caption>
	<thead><tr>
		<th>Name</th>
		<th>Type</th>
		<th>Description</th>
	</tr></thead>
	<tbody>
		<tr><td><code>$callback</code></td><td>Rublon2FactorCallback</td><td>An instance of the <code>Rublon2FactorCallback</code> class</td></tr>
	</tbody>
</table>

<a id="callback-example"></a>
### Example PHP code

An example of the `Rublon2FactorCallback` class usage in the callback:

	$rublo = new Rublon2Factor(
		$systemToken = "A69FC450848B4B94A040416DC4421523",
		$secretKey = "bLS6NDP7pGjg346S4IHqTHgQQjjSLw3CyApvz5iRjYzgIPN4e9EOi1cQJLrTlvLoHY8zeqg4ILrItYidKJ6JjEUZaA6pR1tZMwSZ"
	);
		
	try {
	
		$callback = new Rublon2FactorCallback($rublon);
		$callback->call(
			$successHandler = function($userId, Rublon2FactorCallback $callback) {
				// The user is finally logged in
				$_SESSION["user"] = $userId;
			},
			$cancelHandler = function(Rublon2FactorCallback $callback) {
				// Cancel the authentication process
				header("Location: /login");
				exit;
			}
		);
		
		// The authentication process was successful
		header("Location: /dashboard");
		exit;
		
	} catch (RublonException $e) {
		// Please handle this error in the better way:
		die($e->getMessage());
	}


<a id="confirm"></a>

Secure transaction confirmation
-------------------------------

<p class="notice">
This feature is available only to systems integrated within the <em>Business plan</em>.
<a href="https://developers.rublon.com/98/Pricing">Check the pricing</a>.
</p>

If you are concerned about the security of some of your more sensitive transactions,
e.g. financial transfers, changing your password or email address, Rublon provides
the means for confirming such processes in a secure way. This feature is similar
in operation to the Rublon-confirmed authentication, however instead of scanning
a QR code the user is required to answer a question displayed in the Rublon mobile
app via a push notification.

After initiating a Rublon-confirmed transaction, a message will be displayed in the
mobile app, asking the user to confirm the transaction. The contents of such message
is prepared by the integrated system itself and can contain information which will
help the user identify which transaction the message pertains to, e.g. the amount
of a money transfer or info about the changed password. The user will have to confirm
the transaction by pressing "YES" or deny it by pressing "NO".

Confirmation of the transaction should be initiated only after a user is logged in and the user's
unique Id and email address are known.

The `Rublon2Factor::confirm()` method will check the user's protection status (using the email address hash)
and return a URL address which the web browser should redirect to (if the user is protected by Rublon),
or `NULL`, if the user protection is not active.

<table>
	<caption><code>Rublon2Factor::confirm()</code> method arguments</caption>
	<thead><tr>
		<th>Name</th>
		<th>Type</th>
		<th>Description</th>
	</tr></thead>
	<tbody>
		<tr><td><code>$callbackUrl</code></td><td>string</td><td>The integrated system's callback URL</td></tr>
		<tr><td><code>$userId</code></td><td>string</td><td>The integrated system's user's unique Id which will allow to log in the user upon successful authentication</td></tr>
		<tr><td><code>$userEmail</code></td><td>string</td><td>The user's email address in the integrated system which will allow to check the user's protection status and matching the user to a Rublon account</td></tr>
		<tr><td><code>$confirmMessage</code></td><td>string</td><td>Contents of the message displayed in the mobile app</td></tr>
		<tr><td><code>$consumerParams</code></td><td>array</td><td>Additional transaction parameters (optional).</td></tr>
	</tbody>
</table>


<a id="confirm-init"></a>
### Initiating transaction confirmation

An example of confirming a sensitive transaction using Rublon:
	
	
	/**
	 * Transaction confirmation.
	 * 
	 * @param int $transId Transaction Id
	 * @param int $amount Transaction amount
	 */
	function confirmTransaction($transId, $amount) {
		
		$user = $_SESSION['user'];
		$rublon = new Rublon2Factor(
			$systemToken = "A69FC450848B4B94A040416DC4421523",
			$secretKey = "bLS6NDP7pGjg346S4IHqTHgQQjjSLw3CyApvz5iRjYzgIPN4e9EOi1cQJLrTlvLoHY8zeqg4ILrItYidKJ6JjEUZaA6pR1tZMwSZ"
		);
		
		try { // Initiate Rublon authentication
		
			$url = $rublon->confirm(
				$callbackUrl = "http://example.com/rublon_transaction_confirm.php",
				$user["id"], // User Id
				$user["email"], // User email
				$msg = sprintf("Do you confirm transaction no. %d with the amount of %d?", $transId, $amount),
				$params = array("transId" => $transId) // Additional transaction parameters
			);
			
			if (!empty($url)) { // Rublon protection is active
				
				// Redirect the web browser to Rublon servers
				// to confirm the transaction:
				header('Location: ' . $url);
				
			}
			
		} catch (RublonException $e) {
			// An error occurred
			die("There was an error, please try again later.");
		}
		
		/* If we're here, the user's account is not protected by Rublon,
		so the user should confirm the transaction some other way. */
		return true;
	
	}

If the user's account is protected by Rublon, calling the `Rublon2Factor::confirm()` method will return a URL address which the user's web browser should redirect to so as to scan a QR code and confirm the transaction.

<a id="confirm-callback"></a>
### Finalizing the transaction

Finalizing the whole process involves invoking a callback URL, similarly to the authentication process. In the callback the user's answer to the transaction confirmation question, given by the user in the Rublon mobile app, can be obtained and processed. 

An example of confirming a transaction in the callback:

	$rublon = new Rublon2Factor(
		$systemToken = "A69FC450848B4B94A040416DC4421523",
		$secretKey = "bLS6NDP7pGjg346S4IHqTHgQQjjSLw3CyApvz5iRjYzgIPN4e9EOi1cQJLrTlvLoHY8zeqg4ILrItYidKJ6JjEUZaA6pR1tZMwSZ"
	);
			
	try {
	
		$callback = new Rublon2FactorCallback($rublon);
		$callback->call(
			$successHandler = function($userId, Rublon2FactorCallback $callback) {
				$params = $callback->getCredentials()->getResponse();
				if (isset($params['transId'])) {
					if (RublonAPICredentials::CONFIRM_RESULT_YES == $callback->getCredentials()->getConfirmResult()) {
						// Transaction confirmed, do something with it:
						transaction_confirmed($params['transId']);
					} else {
						// Transaction denied, cancel and clean:
						transaction_denied($params['transId']);
					}
				}
			},
			$cancelHandler = function(Rublon2FactorCallback $callback) {
				// Cancel the process
				redirect("/dashboard");
			}
		);
		
		// The process was successful
		redirect("/dashboard");
		
	} catch (RublonException $e) {
		// Please handle this error in the better way:
		die($e->getMessage());
	}

<a id="email2fa"></a>

Email2FA - simplified identity verification
-------------------------------------------

For users of an integrated system who do not possess a Rublon account (they do
not want to sign up or don't own a smartphone), Rublon provides a simplified form
of two-factor identity verification. This feature employs an email message
with an identity confirmation link sent to the email address of the user being
authenticated, assuming that no one but that user has access to his/her email
inbox. 

<p class="notice">
This feature is enabled by default. However
developer can force users to authenticate using the mobile app, to avoid
the Email2FA process, which can increase the security.
The forcing mobile app feature is available only to systems integrated
within the <em>Business plan</em>.
<a href="https://developers.rublon.com/98/Pricing">Check the pricing</a>.
</p>

<a id="email2fa-how-it-works"></a>

### Principles of operation

1.	Rublon looks for a Trusted Device, which will authenticate the user automatically.
2.	If a Trusted Device cannot be found, Rublon will check if a user with an email address
	provided by the integrated system is protected by Rublon. If such a user is found, the
	process involves using the mobile app.
3.	If no user is found (the user does not have a Rublon account), the Email2FA process
	is started.
4.	The user will receive an email with a identity confirmation link.
5.	After clicking the link, the user will be asked if the current browser should become a Trusted
	Device (signing in) or [a question about confirming a transaction will be displayed](#confirm).
6.	After answering the question, the user will be redirected to the integrated system's [callback URL](#callback)
	and logged in (or the transaction initiated by the user will be confirmed).


<a id="email2fa-config"></a>

### Example usage

The use of Email2FA is by default active and looks the same as the [Signing in process](#auth-example).

<a id="pslogin"></a>
Passwordless Login
-------------------------------------------
Passwordless Login is a way to integrate Rublon, which allows users to log into their accounts without login and password, only by scanning QR code with the Rublon App installed on a user smartphone. 

To read more about this method please navigate <a href="../example-login/README.html">here</a>.


<a id="gui"></a>

GUI
---

The integrated system's users should be informed about the system's capability of protecting
their accounts with Rublon. For web purposes, the `Rublon2FactorGUI` class has been prepared,
responsible for generating a HTML code block called the "*Rublon Box*" that should be embedded
in the integrated system. The block will contain information about the means to protect one's
account.

Rublon Box also contains a frame with a list of the user's Trusted Devices, displayed only when
the current browser is also a Trusted Device. The frame allows for removal of any Trusted
Device.

<a id="gui-example"></a>
### Example PHP code

In order to generate the Rublon Box, you must instantiate a `Rublon2FactorGUI` class object
and invoke its `userBox()` method or cast the object to a string.

	$rublon = new Rublon2Factor(
		$systemToken = "A69FC450848B4B94A040416DC4421523",
		$secretKey = "bLS6NDP7pGjg346S4IHqTHgQQjjSLw3CyApvz5iRjYzgIPN4e9EOi1cQJLrTlvLoHY8zeqg4ILrItYidKJ6JjEUZaA6pR1tZMwSZ"
	);
		
	echo new Rublon2FactorGUI(
		$rublon,
		$_SESSION["user"]["id"],
		$_SESSION["user"]["email"]
	);

<a id="gui-device-widget"></a>
### Trusted Devices Widget

If the current browser is the user's Trusted Device, a Widget with the user's Trusted Devices
will be displayed along with the Rublon Box. The Widget is an iframe containing a list
of the user's Trusted Devices with the means to remove them.

The CSS of the iframe can be customized. In order to achieve this,
the `Rublon2FactorGUI::getDeviceWidgetCSSAttribs()` method should be overriden. The result of
the method should be an associative array where the keys represent specific CSS attribute names.   

<table>
	<caption>Available CSS attributes</caption>
	<thead><tr>
		<th>Constant name</th>
		<th>CSS attribute</th>
		<th>Description</th>
	</tr></thead>
	<tbody>
		<tr><td><code>Rublon2FactorGUI::DEVICE_WIDGET_CSS_FONT_COLOR</code></td><td><code>color</code></td><td>Font color.</td></tr>
		<tr><td><code>Rublon2FactorGUI::DEVICE_WIDGET_CSS_FONT_SIZE</code></td><td><code>font-size</code></td><td>Font size.</td></tr>
		<tr><td><code>Rublon2FactorGUI::DEVICE_WIDGET_CSS_FONT_FAMILY</code></td><td><code>font-family</code></td><td>A set of font names.</td></tr>
		<tr><td><code>Rublon2FactorGUI::DEVICE_WIDGET_CSS_BACKGROUND_COLOR</code></td><td><code>background-color</code></td><td>Background color of the widget.</td></tr>
	</tbody>
</table>

An example of the overriden `Rublon2FactorGUI::getDeviceWidgetCSSAttribs()` method:

	protected function getDeviceWidgetCSSAttribs() {
		return array(
			Rublon2FactorGUI::DEVICE_WIDGET_CSS_FONT_SIZE => '13px',
			Rublon2FactorGUI::DEVICE_WIDGET_CSS_FONT_FAMILY => 'Verdana',
		);
	}


<a id="business"></a>

Business level features
------------------------

<a id="api-get-features"></a>

### Getting list of active features

Developer can obtain the list of active features for current integration using the following code.

	try {
	
		$client = new RublonAPIGetAvailableFeatures($rublon); // Pass the Rublon2Factor instance.
		$client->perform();
		
		// Here is the list of features:
		$features = $client->getFeatures();
		
		// You can check a signle feature:
		$isRemoteLogoutAvailable = !empty($features[RublonAPIGetAvailableFeatures::FEATURE_REMOTE_LOGOUT]);
		
	} catch (RublonException $e) {
		die($e->getMessage());
	}


<a id="confirm-feature"></a>

### Confirmations

The [Secure transaction confirmation](#confirm) described in this document
is an extra feature.


<a id="force-mobile-app"></a>

### Force mobile app

Developer can force users to authenticate by Rublon mobile app and deny
access to users that are using the Email2FA. This will increase the
security level, but also requires users to own a smartphone and install
the Rublon application.

To achieve this you have to pass the additional parameter in
the $consumerParams array during the initiation of the authentication process:

	$url = $rublon->auth(
		$callbackUrl = "http://example.com/rublon_callback.php",
		$_SESSION["user"]["id"], // User Id
		$_SESSION["user"]["email"], // User email
		$params = array(
			**RublonAuthParams::FIELD_FORCE_MOBILE_APP => true,**
		)
	);


You can just imagine a lot of use cases for this feature.
For example, the Wordpress administrator which have installed the WP plugin (provided by us)
can force choosen users' roles to authenticate using Rublon mobile app.



<a id="ignore-trusted-device"></a>

### Ignore Trusted Device

You can increase the security level in some cases, for example
when user didn't login for last 30 days, by ignoring his
Trusted Device during the Rublon authentication. Even if user's
Trusted Device exists, he will be forced to scan the QR code.

To achieve this you have to pass the additional parameter in
the $consumerParams array during the initiation of the authentication process:

	$url = $rublon->auth(
		$callbackUrl = "http://example.com/rublon_callback.php",
		$_SESSION["user"]["id"], // User Id
		$_SESSION["user"]["email"], // User email
		$params = array(
			**RublonAuthParams::FIELD_IGNORE_TRUSTED_DEVICE => true,**
		)
	);


<a id="buffered-confirmation"></a>

Buffered confirmation
-----------------------

If you want to make life easier for your users and don't need
extra high security level when users perform the multiple
confirmations in a small amount of time, you can use the buffered
confirmations feature.

User will be required to confirm the first confirmation using
the Rublon mobile app or clicking the confirmation link sent
to his email address (when using Email2FA). Developer determines
a time buffer withing the next user's confirmation will be
accepted immediately, without the need to use the mobile app
or checking the email. The only requirement is that the user's
Trusted Device has to be present before performing the confirmation.
Without a Trusted Device every transaction have to be confirmed manually.

The following code shows how to determine the time buffer (in seconds)
during the confirmation transaction initiation:

	$url = $rublon->confirm(
		$callbackUrl = "http://example.com/rublon_transaction_confirm.php",
		$user["id"], // User Id
		$user["email"], // User email
		$msg = sprintf("Do you confirm transaction no. %d with the amount of %d?", $transId, $amount),
		$params = array(
			"transId" => $transId, // Additional transaction parameters
			RublonAuthParams::FIELD_CONFIRM_TIME_BUFFER => 300, // 5 minutes
		)
	);



<a id="sharing-access"></a>

### Sharing Access

When a user's account is protected by Rublon, there is no possibility to login
by other people which are knowing the user name and password. But sometimes
there is a need to do that, for example two administrators are using the same
account to update the news feed on their website.

For a such cases Rublon provides the Share Access feature.
The Rublon user which is associated with the website's account (the host user)
can share his access with other Rublon user (the client user).
To achieve this, the host user have to enter the client user's email address
in the Share Access Widget, which should be visible when displaying the [Rublon GUI](#gui)
(if the feature has been activated for the integration).

The client user will be able to login to the website's account, but by default
he won't be able to confirm operation. To enable confirmations for the client user,
the host user have to click the "Enable confirming vulnerable operations" option
on the Share Access Widget.


<a id="remote-logout"></a>

### Remote Logout

When a Rublon user removes his Trusted Devices, Rublon is able to notify all
websites associated with this device that user has deleted the device.
Websites which implemented the remote logout listeners can logout the user
immediately and destroy his session for a security reasons.

#### Server-side listener

Developer can create a server-side logout listener which is based on the callback URL.
Rublon server wants to notify your website about the deleted device will perform a REST
request to the callback URL. You have to implement an additional method in
the `Rublon2FactorCallback` subclass to handle this action.

	class MyCallback extends Rublon2FactorCallback {
	
		/**
		 * Delete sessions with given userId and deviceId.
		 * 
		 * @see Rublon2FactorCallback::handleLogout()
		 */
		protected function handleLogout($userId, $deviceId) {
			global $db;
			
			// Delete user's session from database:
			$sql = "DELETE FROM sessions WHERE user_id = %d AND (rublon_device_id = %d OR rublon_device_id IS NULL)";
			$db->query($sql, $userId, $deviceId);
			
		}
	
	}
	
As you can see, the user's session have to be associated with the device ID.
This value can be obtained in the callback's $successHandler, as well as the
Rublon user's profile ID which can be also handy:

	$callback = new Rublon2FactorCallback($rublon);
	$callback->call(
		$successHandler = function($userId, Rublon2FactorCallback $callback) {
			// The user is finally logged in
			$_SESSION["user"] = $userId;
			
			// Obtain the device ID:
			$deviceId = $callback->getCredentials()->getDeviceId();
			
			// Obtain the Rublon user's profile ID:
			$profileId = $callback->getCredentials()->getProfileId();
			
		},
		$cancelHandler = function(Rublon2FactorCallback $callback) {
			// Cancel the authentication process
			header("Location: /login");
			exit;
		}
	);


#### Browser-side listener

Developer can add a browser-side logout listener which will listen the Rublon server
by using the JavaScript long-pool request.

<p class="notice">
Please do not use the browser-side listener as a default - do always use the server-side
remote logout listeners. The browser-side listener is not the best way to logout user,
because he may close the browser earlier or disable JavaScript and then won't be logged-out.
However this way provides a good user-experience, because the user is logged out immediately.
</p>

To enable the browser-side logout listener, embed the Rublon consumer script on every page in your website:

	echo new RublonConsumerScript($rublon, $userId, $userEmail, $logoutListener = true);
	
Also you have to provide the JavaScript function which will actually logout the user:

	<script type="text/javascript">
	function RublonLogoutCallback() {
		location.href = "/logout.php";
	}
	</script>
	

#### Checking the user's Trusted Device status

Developer can also use cron to check the user's Trusted Device status for active user's session
and delete it when the Trusted Device has been removed.

<p class="notice">
Please notice that not every user will create a Trusted Device when login to your website.
</p>

To check the user's device status perform the following:

	$client = new RublonAPICheckUserDevice($rublon, $profileId, $deviceId);
	$client->perform();
	$isActive = $client->isDeviceActive();

The $profileId value you can obtain in the callback's $successHandler function.




<a id="changelog"></a>

Changelog
---------
### 2015-07-02 (v. 3.7.1)
*	Added Passwordless Login support
*	Added short description and a link to the full example of the Passwordless Login method.

### 2015-05-06 (v. 3.7.0)
*	Email2FA became enabled by default.
*	Added Business level features description.

### 2015-03-21 (v. 3.5.1)
*	Removed the RCS support.

### 2014-08-19 (v. 3.1)
*	Transaction confirmation described.
*	Email2FA described.
*	Trusted Devices Widget described.

### 2014-05-22 (v. 3.0)
*	API v3.0 introduced: JSON messages' body simplified, and their signature and metadata moved
	to HTTP headers.
*	Added support for the Rublon Cache Server, a service for checking a user's email address
	association with a Rublon account (therefore checking the user's protection status).
*	Modified the `Rublon2Factor::auth()` method's header.
*	`Rublon2Factor::confirm()` method added, allowing developers on the *Financial* plan
	to confirm user transactions by displaying in the Rublon mobile app an additional YES/NO
	dialog with a customizable message. 

### 2014-01-15
The information about the association of a user's account in the integrated system with a Rublon
account has been moved to Rublon servers. This has simplified the library greatly; the templates
are no longer needed to be implemented, it suffices to just invoke the library methods accordingly.  
All basic classes and interfaces have been changed.
	
### 2013-11-22
First version of the README document.